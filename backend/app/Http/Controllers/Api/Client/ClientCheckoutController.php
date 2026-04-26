<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\ProductVariant;
use App\Models\UserAddress;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderPlacedMail;
use App\Mail\AdminNewOrderMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ClientCheckoutController extends Controller
{
    // ========================================================
    // 1. KHỞI TẠO DỮ LIỆU CHECKOUT (ĐỊA CHỈ, VOUCHER, USER)
    // ========================================================
    public function initData(Request $request)
    {
        $addresses = [];
        $userData = null;

        $user = auth('sanctum')->user();
        if ($user) {
            $addresses = UserAddress::where('user_id', $user->id)->get();
            $userData = [
                'id'    => $user->id,
                'name'  => $user->full_name ?? $user->name ?? '',
                'email' => $user->email ?? '',
                'phone' => $user->phone ?? ''
            ];
        }

        // Lấy danh sách Voucher khả dụng
        $vouchers = DB::table('vouchers')
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('start_time')->orWhere('start_time', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_time')->orWhere('end_time', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('usage_limit')->orWhereColumn('usage_count', '<', 'usage_limit');
            })
            ->get();

        return response()->json([
            'success'   => true,
            'addresses' => $addresses,
            'coupons'   => $vouchers, 
            'user'      => $userData
        ]);
    }

    // ========================================================
    // 2. XỬ LÝ ĐẶT HÀNG (CHỐNG DOUBLE CLICK BẰNG CACHE LOCK)
    // ========================================================
    public function processCheckout(Request $request)
    {
        $cart = $this->resolveCart($request);

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Giỏ hàng trống hoặc phiên đã hết hạn.'], 400);
        }

        $user = auth('sanctum')->user();

        // CHỐNG DOUBLE CLICK (Khóa 10 giây cho mỗi user/session)
        $sessionId = $request->header('X-Cart-Session-Id');
        $lockKey = 'checkout_lock_' . ($user ? $user->id : $sessionId);
        $lock = Cache::lock($lockKey, 10);

        if (!$lock->get()) {
            return response()->json([
                'success' => false,
                'message' => 'Hệ thống đang xử lý đơn hàng của bạn, vui lòng không bấm liên tục.'
            ], 429);
        }

        try {
            return DB::transaction(function () use ($request, $cart, $user) {
                
                $customerName = $request->customer_name;
                $customerPhone = $request->customer_phone;
                $customerEmail = $request->customer_email;
                $customerAddress = $request->customer_address;

                if ($request->user_address_id && $user) {
                    $address = UserAddress::where('user_id', $user->id)->find($request->user_address_id);
                    if ($address) {
                        $customerName = $address->customer_name;
                        $customerPhone = $address->customer_phone;
                        $customerAddress = $address->shipping_address . ', ' . $address->ward . ', ' . $address->district . ', ' . $address->city;
                    }
                }

                $variantIdsToLock = $cart->items->pluck('variant_id')->filter()->toArray();
                
                $variants = ProductVariant::with(['product', 'attributeValues'])
                    ->whereIn('id', array_unique($variantIdsToLock))
                    ->orderBy('id')->lockForUpdate()->get()->keyBy('id');

                $subTotal = 0;
                $orderItemsData = [];

                foreach ($cart->items as $item) {
                    if ($item->variant_id) {
                        $variant = $variants->get($item->variant_id);
                        
                        // Kiểm tra tồn kho
                        if (!$variant || $variant->stock_quantity < $item->quantity) {
                            $pName = $variant && $variant->product ? $variant->product->name : 'Sản phẩm';
                            throw new \Exception("{$pName} không đủ số lượng trong kho.");
                        }

                        // Trừ tồn kho
                        $variant->stock_quantity -= $item->quantity;
                        $variant->save();

                        // Tính giá mua (purchased_price)
                        $itemPrice = $variant->promotional_price ?? $variant->price;
                        $itemTotal = $itemPrice * $item->quantity;
                        $subTotal += $itemTotal;

                        $costPrice = $variant->cost_price ?? ($variant->product->cost_price ?? 0);

                        $attrs = ['Mặc định'];
                        if ($variant->attributeValues && $variant->attributeValues->count() > 0) {
                            $attrs = $variant->attributeValues->pluck('value')->toArray();
                        }

                        // ĐÃ FIX: CHÈN THÊM DỮ LIỆU LOOKBOOK TỪ CART SANG ORDER
                        $orderItemsData[] = [
                            'product_id'         => $item->product_id,
                            'variant_id'         => $variant->id, 
                            'product_name'       => $variant->product ? $variant->product->name : 'Sản phẩm ZYRO',
                            'variant_sku'        => $variant->sku,
                            'variant_attributes' => $attrs, 
                            'variant_image'      => $variant->image_url ?? ($variant->product ? $variant->product->thumbnail_image : null),
                            'purchased_price'    => $itemPrice,
                            'cost_price'         => $costPrice, 
                            'quantity'           => $item->quantity,
                            'total_price'        => $itemTotal,
                            // LUÂN CHUYỂN THÔNG TIN COMBO Ở ĐÂY!!!
                            'lookbook_id'        => $item->lookbook_id,
                            'lookbook_selections'=> $item->lookbook_selections
                        ];
                    }
                }

                $discountAmount = 0;
                $voucherId = null;
                
                if ($request->coupon_code) {
                    $voucher = DB::table('vouchers')->where('code', $request->coupon_code)->first();
                    if (!$voucher || $voucher->status !== 'active') {
                        throw new \Exception("Mã giảm giá không hợp lệ hoặc đã ngừng kích hoạt.");
                    }
                    if ($subTotal < $voucher->min_spend) {
                        throw new \Exception("Chưa đạt giá trị đơn hàng tối thiểu để dùng mã này.");
                    }

                    if ($voucher->discount_type === 'percent' || $voucher->discount_type === 'percentage') {
                        $calcDiscount = $subTotal * ($voucher->discount_value / 100);
                        if ($voucher->max_discount_amount && $calcDiscount > $voucher->max_discount_amount) {
                            $calcDiscount = $voucher->max_discount_amount;
                        }
                        $discountAmount = $calcDiscount;
                    } else {
                        $discountAmount = $voucher->discount_value;
                    }
                    
                    $voucherId = $voucher->id; 
                    DB::table('vouchers')->where('id', $voucherId)->increment('usage_count');
                }

                $shippingFee = (int) $request->shipping_fee;
                $totalAmount = max($subTotal - $discountAmount + $shippingFee, 0);

                $orderCode = 'ZYRO' . strtoupper(Str::random(8));

                $order = Order::create([
                    'order_code'       => $orderCode,
                    'user_id'          => $user->id ?? null,
                    'shipping_info'    => [
                        'name'    => $customerName,
                        'phone'   => $customerPhone,
                        'email'   => $customerEmail,
                        'address' => $customerAddress,
                        'origin_city' => 'Hà Nội' 
                    ],
                    'order_note'       => $request->order_note,
                    'sub_total'        => $subTotal,
                    'shipping_fee'     => $shippingFee,
                    'discount_amount'  => $discountAmount,
                    'total_amount'     => $totalAmount,
                    'voucher_id'       => $voucherId, 
                    'payment_method'   => $request->payment_method, 
                    'payment_status'   => 'unpaid',
                    'status'           => 'pending',
                ]);

                // Lưu Order Items
                foreach ($orderItemsData as $itemData) {
                    $itemData['order_id'] = $order->id;
                    OrderItem::create($itemData);
                }

                OrderStatusHistory::create([
                    'order_id'        => $order->id,
                    'old_status'      => null,
                    'new_status'      => 'pending',
                    'note'            => 'Khách hàng khởi tạo đơn hàng',
                    'changed_by'      => $user->id ?? null,
                    'changed_by_type' => $user ? get_class($user) : null,
                ]);

                if ($request->payment_method === 'cod') {
                    $cart->items()->delete();
                    $cart->delete();

                    $this->sendOrderConfirmationEmail($order);

                    return response()->json([
                        'success' => true,
                        'data' => $order,
                        'message' => 'Đặt hàng thành công!'
                    ]);
                }

                if ($request->payment_method === 'momo') {
                    $momoUrl = $this->generateMomoUrl($order);
                    return response()->json([
                        'success' => true,
                        'payment_url' => $momoUrl,
                        'message' => 'Đang chuyển hướng sang Ví MoMo...'
                    ]);
                }
            });
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        } finally {
            $lock->release();
        }
    }

    private function generateMomoUrl($order)
    {
        $endpoint = env('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create');
        $partnerCode = env('MOMO_PARTNER_CODE', 'MOMOBKUN20180529');
        $accessKey   = env('MOMO_ACCESS_KEY', 'klm05TvNBzhg7h7j');
        $secretKey   = env('MOMO_SECRET_KEY', 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa');

        $orderInfo = "Thanh toan don hang ZYRO " . $order->order_code;
        $amount = (string) round($order->total_amount);
        $orderId = $order->order_code . "_" . time(); 

        $redirectUrl = env('FRONTEND_URL', 'http://localhost:5173') . '/checkout/momo-return';
        $ipnUrl = url('/api/v1/client/checkout/momo-return');

        $extraData = "";
        $requestId = time() . "";
        $requestType = "payWithATM";

        $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&ipnUrl=" . $ipnUrl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $redirectUrl . "&requestId=" . $requestId . "&requestType=" . $requestType;
        $signature = hash_hmac("sha256", $rawHash, $secretKey);

        $data = array(
            'partnerCode' => $partnerCode,
            'partnerName' => "ZYRO Official",
            "storeId"     => "ZYRO_Store",
            'requestId'   => $requestId,
            'amount'      => (int)$amount,
            'orderId'     => $orderId,
            'orderInfo'   => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl'      => $ipnUrl,
            'lang'        => 'vi',
            'extraData'   => $extraData,
            'requestType' => $requestType,
            'signature'   => $signature
        );

        $response = Http::post($endpoint, $data);
        $result = $response->json();

        if (isset($result['payUrl'])) {
            return $result['payUrl'];
        }

        throw new \Exception("MoMo API Error: " . ($result['message'] ?? 'Lỗi tạo link thanh toán'));
    }

    public function momoReturn(Request $request)
    {
        $parts = explode('_', $request->orderId);
        $orderCode = $parts[0] ?? '';

        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');

        if ($request->resultCode == 0) {
            Order::where('order_code', $orderCode)->update(['payment_status' => 'paid']);
            $order = Order::with('items')->where('order_code', $orderCode)->first();

            if ($order) {
                if ($order->user_id) {
                    $cart = Cart::where('user_id', $order->user_id)->first();
                    if ($cart) {
                        $cart->items()->delete();
                        $cart->delete();
                    }
                }

                $this->sendOrderConfirmationEmail($order);
            }

            return redirect($frontendUrl . '/checkout/success?order=' . $orderCode);
        }

        $this->cancelOrderAndRestoreStock($orderCode);
        return redirect($frontendUrl . '/checkout/failed?order=' . $orderCode);
    }

    private function sendOrderConfirmationEmail($order)
    {
        try {
            $order->load('items');

            $shippingInfo = is_string($order->shipping_info) ? json_decode($order->shipping_info, true) : $order->shipping_info;
            $customerEmail = $shippingInfo['email'] ?? null;

            if (!empty($customerEmail)) {
                Mail::to($customerEmail)->send(new OrderPlacedMail($order));
            }

            $adminEmailsToNotify = Admin::where('role_id', 1)
                ->where('status', 'active')
                ->pluck('email')
                ->toArray();

            if (!empty($adminEmailsToNotify)) {
                Mail::to($adminEmailsToNotify)->send(new AdminNewOrderMail($order));
            }
        } catch (\Exception $e) {
            Log::error('Lỗi gửi mail đơn hàng ' . $order->order_code . ': ' . $e->getMessage());
        }
    }

    private function cancelOrderAndRestoreStock($orderCode)
    {
        $order = Order::with('items')->where('order_code', $orderCode)->first();

        if ($order && $order->status === 'pending' && $order->payment_status === 'unpaid') {
            $order->update(['status' => 'cancelled', 'payment_status' => 'failed']);

            foreach ($order->items as $item) {
                if ($item->variant_id) { 
                    ProductVariant::where('id', $item->variant_id)->increment('stock_quantity', $item->quantity);
                }
            }
        }
    }

    private function resolveCart(Request $request)
    {
        $user = auth('sanctum')->user();
        if ($user) return Cart::with(['items.variant.product'])->where('user_id', $user->id)->first();

        $sessionId = $request->header('X-Cart-Session-Id');
        if ($sessionId) return Cart::with(['items.variant.product'])->where('session_id', $sessionId)->first();

        return null;
    }
}