<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Checkout\ProcessCheckoutRequest; 
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\ProductVariant;
use App\Models\UserAddress;
use App\Models\Admin;
use App\Models\FlashSale;
use App\Models\MembershipTier; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderPlacedMail;
use App\Mail\AdminNewOrderMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Notifications\AdminAlertNotification;

class ClientCheckoutController extends Controller
{
    public function initData(Request $request)
    {
        $addresses = [];
        $userData = null;

        $user = auth('sanctum')->user();
        if ($user) {
            $addresses = UserAddress::where('user_id', $user->id)->get();
            $totalSpent = Order::where('user_id', $user->id)->where('status', 'completed')->sum('total_amount');

            $userData = [
                'id'          => $user->id,
                'name'        => $user->full_name ?? $user->name ?? '',
                'email'       => $user->email ?? '',
                'phone'       => $user->phone ?? '',
                'total_spent' => (float) $totalSpent
            ];
        }

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

        $tiers = MembershipTier::orderBy('min_spent', 'asc')->get();

        return response()->json([
            'success'   => true,
            'addresses' => $addresses,
            'coupons'   => $vouchers, 
            'tiers'     => $tiers, 
            'user'      => $userData
        ]);
    }

    public function processCheckout(ProcessCheckoutRequest $request)
    {
        $cart = $this->resolveCart($request);

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Giỏ hàng trống hoặc phiên đã hết hạn.'], 400);
        }

        $user = auth('sanctum')->user();
        $sessionId = $request->header('X-Cart-Session-Id');
        $lockKey = 'checkout_lock_' . ($user ? $user->id : $sessionId);
        $lock = Cache::lock($lockKey, 10);

        if (!$lock->get()) {
            return response()->json(['success' => false, 'message' => 'Hệ thống đang xử lý đơn hàng, vui lòng không bấm liên tục.'], 429);
        }

        try {
            return DB::transaction(function () use ($request, $cart, $user, $sessionId) {
                
                $customerName = $request->customer_name;
                $customerPhone = $request->customer_phone;
                $customerEmail = $request->customer_email;
                $customerAddress = $request->customer_address;
                $city = $request->customer_address; 

                if ($request->user_address_id && $user) {
                    $address = UserAddress::where('user_id', $user->id)->find($request->user_address_id);
                    if ($address) {
                        $customerName = $address->customer_name;
                        $customerPhone = $address->customer_phone;
                        $customerAddress = $address->shipping_address . ', ' . $address->ward . ', ' . $address->district . ', ' . $address->city;
                        $city = $address->city;
                    }
                }

                $variantIdsToLock = $cart->items->pluck('variant_id')->filter()->toArray();
                $variants = ProductVariant::with(['product', 'attributeValues'])
                    ->whereIn('id', array_unique($variantIdsToLock))
                    ->orderBy('id')->lockForUpdate()->get()->keyBy('id');

                // ĐÃ FIX (GÓC KHUẤT TRÙM CUỐI): Truyền Email và SĐT xuống để xác minh Giới hạn Voucher Cá nhân của Guest
                $totals = $this->calculateOrderTotals($cart->items, $variants, $user, $request->coupon_code, $customerEmail, $customerPhone);

                $orderItemsData = [];

                foreach ($cart->items as $item) {
                    if ($item->variant_id) {
                        $variant = $variants->get($item->variant_id);
                        
                        $availableStock = $variant->stock_quantity - ($variant->reserved_stock ?? 0);
                        
                        if (!$variant || $availableStock < $item->quantity) {
                            $pName = $variant && $variant->product ? $variant->product->name : 'sản phẩm';
                            throw new \Exception("{$pName} không đủ số lượng khả dụng (chỉ còn {$availableStock} sản phẩm).");
                        }

                        $variant->reserved_stock = ($variant->reserved_stock ?? 0) + $item->quantity;
                        $variant->save();

                        $itemPrice = $totals['fs_variant_prices'][$variant->id] ?? ($variant->promotional_price ?? $variant->price);
                        $itemTotal = $itemPrice * $item->quantity;
                        $costPrice = $variant->cost_price ?? ($variant->product->cost_price ?? 0);

                        $attrs = ['Mặc định'];
                        if ($variant->attributeValues && $variant->attributeValues->count() > 0) {
                            $attrs = $variant->attributeValues->pluck('value')->toArray();
                        }

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
                            'lookbook_id'        => $item->lookbook_id,
                            'lookbook_selections'=> $item->lookbook_selections
                        ];
                    }
                }

                if ($totals['voucher_id']) {
                    DB::table('vouchers')->where('id', $totals['voucher_id'])->increment('usage_count');
                }

                $shippingFee = $this->calculateShippingFee($totals['subtotal_after_tier'], $city);
                
                $finalTotalAmount = max($totals['final_total'] + $shippingFee, 0);
                $totalDiscountAmount = $totals['tier_discount'] + $totals['voucher_discount'];

                $orderCode = 'ZYRO' . strtoupper(Str::random(8));

                $orderNote = $request->order_note;
                if ($request->require_vat && $request->vat_info) {
                    $vat = $request->vat_info;
                    $vatStr = " [YÊU CẦU XUẤT VAT] Công ty: {$vat['company_name']} - MST: {$vat['tax_code']} - Đ/C: {$vat['address']}";
                    $orderNote = $orderNote ? $orderNote . $vatStr : trim($vatStr);
                }

                $order = Order::create([
                    'order_code'          => $orderCode,
                    'user_id'             => $user->id ?? null,
                    'shipping_info'       => [
                        'name'        => $customerName,
                        'phone'       => $customerPhone,
                        'email'       => $customerEmail,
                        'address'     => $customerAddress,
                        'origin_city' => 'Hà Nội',
                        'session_id'  => !$user ? $sessionId : null 
                    ],
                    'order_note'          => $orderNote,
                    'sub_total'           => $totals['sub_total'],
                    'flash_sale_discount' => $totals['flash_sale_discount'],
                    'tier_discount'       => $totals['tier_discount'],       
                    'voucher_discount'    => $totals['voucher_discount'],    
                    'discount_amount'     => $totalDiscountAmount,
                    'shipping_fee'        => $shippingFee,
                    'total_amount'        => $finalTotalAmount,
                    'voucher_id'          => $totals['voucher_id'], 
                    'payment_method'      => $request->payment_method, 
                    'payment_status'      => 'unpaid',
                    'payment_details'     => null, 
                    'discount_details'    => [
                        'tier_name'         => $totals['tier_name'],
                        'tier_percent'      => $totals['tier_percent'],
                        'tier_max_discount' => $totals['tier_max_discount'],
                        'voucher_code'      => $request->coupon_code,
                        'voucher_type'      => $totals['voucher_type'],
                        'voucher_value'     => $totals['voucher_value'],
                    ],
                    'status'              => 'pending',
                ]);

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

                // Xóa sản phẩm khỏi giỏ hàng NGAY SAU KHI TẠO ĐƠN (Kể cả MoMo)
                $this->clearCartAfterPayment($order);

                if ($request->payment_method === 'cod') {
                    foreach ($orderItemsData as $itemData) {
                        if ($itemData['variant_id']) {
                            ProductVariant::where('id', $itemData['variant_id'])->update([
                                'stock_quantity' => DB::raw("stock_quantity - {$itemData['quantity']}"),
                                'reserved_stock' => DB::raw("GREATEST(0, COALESCE(reserved_stock, 0) - {$itemData['quantity']})")
                            ]);
                        }
                    }

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

                if ($request->payment_method === 'vnpay') {
                    throw new \Exception('Cổng thanh toán VNPay đang bảo trì hoặc chưa cấu hình. Vui lòng chọn phương thức khác.');
                }
            });
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        } finally {
            $lock->release();
        }
    }

    private function calculateShippingFee($totalAmount, $cityStr)
    {
        if ($totalAmount >= 1000000) {
            return 0;
        }

        $city = mb_strtolower($cityStr ?? '', 'UTF-8');
        if (str_contains($city, 'hà nội') || str_contains($city, 'hồ chí minh')) {
            return 30000;
        }

        return 40000;
    }

    private function calculateOrderTotals($cartItems, $variants, $user, $voucherCode = null, $customerEmail = null, $customerPhone = null)
    {
        $regularTotal = 0;
        $flashSaleTotal = 0;
        $flashSaleDiscountAmount = 0;

        $activeFlashSale = FlashSale::where('status', 'active')
            ->where('start_time', '<=', now())
            ->where('end_time', '>', now())
            ->with('items')
            ->first();

        $fsVariantPrices = [];
        if ($activeFlashSale) {
            foreach ($activeFlashSale->items as $fsItem) {
                $fsVariantPrices[$fsItem->variant_id] = $fsItem->flash_sale_price;
            }
        }

        foreach ($cartItems as $item) {
            $variant = $variants->get($item->variant_id);
            if (!$variant) continue;

            $qty = $item->quantity;
            $originalPrice = $variant->promotional_price ?? $variant->price;

            if (isset($fsVariantPrices[$variant->id])) {
                $fsPrice = $fsVariantPrices[$variant->id];
                $flashSaleTotal += ($fsPrice * $qty);
                if ($originalPrice > $fsPrice) {
                    $flashSaleDiscountAmount += (($originalPrice - $fsPrice) * $qty);
                }
            } else {
                $regularTotal += ($originalPrice * $qty);
            }
        }

        $tierDiscount = 0;
        $tierName = null; 
        $tierPercent = null;
        $tierMaxDiscount = null;

        if ($user) {
            $totalSpent = Order::where('user_id', $user->id)
                ->where('status', 'completed')
                ->sum('total_amount');
                
            $matchedTier = MembershipTier::where('min_spent', '<=', $totalSpent)
                ->orderBy('min_spent', 'desc')
                ->first();
                
            if ($matchedTier) {
                $vipPercent = $matchedTier->discount_percent ?? 0;
                $tierDiscount = $regularTotal * ($vipPercent / 100);
                
                if ($matchedTier->max_discount_amount > 0 && $tierDiscount > $matchedTier->max_discount_amount) {
                    $tierDiscount = $matchedTier->max_discount_amount;
                }

                $tierName = $matchedTier->name; 
                $tierPercent = $vipPercent;
                $tierMaxDiscount = $matchedTier->max_discount_amount;
            }
        }
        
        $discountedRegularTotal = $regularTotal - $tierDiscount;
        $subTotalBase = $regularTotal + $flashSaleTotal; 
        $subtotalAfterTier = $discountedRegularTotal + $flashSaleTotal;

        $voucherDiscount = 0;
        $voucherId = null;
        $voucherType = null;
        $voucherValue = null;

        if ($voucherCode) {
            $voucher = DB::table('vouchers')
                ->where('code', $voucherCode)
                ->where('status', 'active')
                ->lockForUpdate() 
                ->where(function ($q) {
                    $q->whereNull('start_time')->orWhere('start_time', '<=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('end_time')->orWhere('end_time', '>=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('usage_limit')->orWhereColumn('usage_count', '<', 'usage_limit');
                })
                ->first();

            if ($voucher && $subtotalAfterTier >= $voucher->min_spend) {
                
                // =========================================================================================
                // ĐÃ FIX (GÓC KHUẤT TRÙM CUỐI): CHẶN KHÁCH VÃNG LAI BÒN RÚT MÃ GIẢM GIÁ
                // Check Lịch sử đặt hàng của Guest qua Email / Số điện thoại 
                // =========================================================================================
                if ($voucher->usage_limit_per_user) {
                    $usageQuery = DB::table('orders')
                        ->where('voucher_id', $voucher->id)
                        ->whereNotIn('status', ['cancelled', 'returned']);

                    if ($user) {
                        $usageCount = $usageQuery->where('user_id', $user->id)->count();
                    } else if ($customerEmail || $customerPhone) {
                        $usageCount = $usageQuery->whereNull('user_id')
                            ->where(function($q) use ($customerEmail, $customerPhone) {
                                if ($customerEmail) $q->orWhere('shipping_info->email', $customerEmail);
                                if ($customerPhone) $q->orWhere('shipping_info->phone', $customerPhone);
                            })->count();
                    } else {
                        $usageCount = 0; 
                    }

                    if ($usageCount >= $voucher->usage_limit_per_user) {
                        throw new \Exception("Bạn (hoặc SĐT/Email này) đã hết lượt sử dụng mã giảm giá này ({$voucher->usage_limit_per_user} lần/người).");
                    }
                }

                $voucherType = $voucher->discount_type;
                $voucherValue = $voucher->discount_value;

                if ($voucher->discount_type === 'percent' || $voucher->discount_type === 'percentage') {
                    $calcDiscount = $subtotalAfterTier * ($voucher->discount_value / 100);
                    if ($voucher->max_discount_amount && $calcDiscount > $voucher->max_discount_amount) {
                        $calcDiscount = $voucher->max_discount_amount;
                    }
                    $voucherDiscount = $calcDiscount;
                } else {
                    $voucherDiscount = $voucher->discount_value;
                }
                $voucherId = $voucher->id;

                $voucherDiscount = min($voucherDiscount, $subtotalAfterTier);
            } else {
                throw new \Exception("Mã giảm giá không hợp lệ hoặc đơn hàng chưa đạt tối thiểu " . number_format($voucher->min_spend ?? 0) . "đ (sau khi áp dụng Hạng).");
            }
        }

        $finalTotal = max(0, $subtotalAfterTier - $voucherDiscount);

        return [
            'sub_total'           => $subTotalBase,
            'flash_sale_discount' => $flashSaleDiscountAmount, 
            'flash_sale_total'    => $flashSaleTotal,
            'regular_total'       => $regularTotal,
            'tier_name'           => $tierName, 
            'tier_percent'        => $tierPercent,
            'tier_max_discount'   => $tierMaxDiscount,
            'tier_discount'       => $tierDiscount,
            'voucher_type'        => $voucherType,
            'voucher_value'       => $voucherValue,
            'voucher_discount'    => $voucherDiscount,
            'subtotal_after_tier' => $subtotalAfterTier,
            'final_total'         => $finalTotal,
            'voucher_id'          => $voucherId,
            'fs_variant_prices'   => $fsVariantPrices
        ];
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

        $redirectUrl = url('/api/v1/client/checkout/momo-return');
        $ipnUrl = url('/api/v1/client/checkout/momo-ipn');

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

    private function verifyMomoSignature(Request $request): bool
    {
        $accessKey = env('MOMO_ACCESS_KEY', 'klm05TvNBzhg7h7j');
        $secretKey = env('MOMO_SECRET_KEY', 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa');

        $rawHash = "accessKey=" . $accessKey .
            "&amount=" . $request->amount .
            "&extraData=" . $request->extraData .
            "&message=" . $request->message .
            "&orderId=" . $request->orderId .
            "&orderInfo=" . $request->orderInfo .
            "&orderType=" . $request->orderType .
            "&partnerCode=" . $request->partnerCode .
            "&payType=" . $request->payType .
            "&requestId=" . $request->requestId .
            "&responseTime=" . $request->responseTime .
            "&resultCode=" . $request->resultCode .
            "&transId=" . $request->transId;

        $signature = hash_hmac("sha256", $rawHash, $secretKey);
        return hash_equals($signature, (string) $request->signature);
    }

    public function momoReturn(Request $request)
    {
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
        $parts = explode('_', $request->orderId);
        $orderCode = $parts[0] ?? '';

        if (!$this->verifyMomoSignature($request)) {
            return redirect($frontendUrl . '/checkout/failed?order=' . $orderCode);
        }

        if ($request->resultCode == 0) {
            return redirect($frontendUrl . '/checkout/success?order=' . $orderCode);
        }

        return redirect($frontendUrl . '/checkout/failed?order=' . $orderCode);
    }

    public function momoIpn(Request $request)
    {
        if (!$this->verifyMomoSignature($request)) {
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $parts = explode('_', $request->orderId);
        $orderCode = $parts[0] ?? '';

        $order = Order::with('items')->where('order_code', $orderCode)->first();
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($order->payment_status === 'paid') {
            return response()->json(['message' => 'Success'], 204);
        }

        if ($request->resultCode == 0) {
            
            $momoAmount = (float) $request->amount;
            $orderAmount = (float) round($order->total_amount);

            if (abs($momoAmount - $orderAmount) > 0.01) {
                Log::warning("CẢNH BÁO BẢO MẬT: MoMo Amount Mismatch! Đơn hàng: {$orderCode}, Tiền MoMo trả về: {$momoAmount}, Tiền đúng trong DB: {$orderAmount}");
                return response()->json(['message' => 'Amount mismatch'], 400);
            }

            DB::transaction(function () use ($order, $request) {
                
                $order->update([
                    'payment_status' => 'paid',
                    'transaction_id' => $request->transId,
                    'payment_details' => $request->all() 
                ]);

                foreach ($order->items as $item) {
                    if ($item->variant_id) {
                        ProductVariant::where('id', $item->variant_id)->update([
                            'stock_quantity' => DB::raw("stock_quantity - {$item->quantity}"),
                            'reserved_stock' => DB::raw("GREATEST(0, COALESCE(reserved_stock, 0) - {$item->quantity})")
                        ]);
                    }
                }
            });

            $this->sendOrderConfirmationEmail($order);

        } else {
            $this->cancelOrderAndRestoreStock($orderCode);
        }

        return response()->json(['message' => 'Success'], 204);
    }

    private function clearCartAfterPayment($order)
    {
        $cart = null;
        if ($order->user_id) {
            $cart = Cart::where('user_id', $order->user_id)->first();
        } else {
            $shippingInfo = is_string($order->shipping_info) ? json_decode($order->shipping_info, true) : $order->shipping_info;
            $sessionId = $shippingInfo['session_id'] ?? null;
            
            if ($sessionId) {
                $cart = Cart::where('session_id', $sessionId)->first();
            }
        }

        if ($cart) {
            $variantIds = $order->items->pluck('variant_id')->toArray();
            $cart->items()->whereIn('variant_id', $variantIds)->delete();
            
            if ($cart->items()->count() === 0) {
                $cart->delete();
            }
        }
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

            $adminsToNotify = Admin::where('role_id', 1)->where('status', 'active')->get();

            if ($adminsToNotify->isNotEmpty()) {
                Mail::to($adminsToNotify->pluck('email')->toArray())->send(new AdminNewOrderMail($order));
                
                foreach ($adminsToNotify as $admin) {
                    $admin->notify(new AdminAlertNotification([
                        'type'    => 'success',
                        'title'   => 'Đơn hàng mới: #' . $order->order_code,
                        'message' => 'Khách hàng vừa đặt đơn hàng mới trị giá ' . number_format($order->total_amount, 0, ',', '.') . 'đ.',
                        'url'     => '/admin/orders/' . $order->id,
                    ]));
                }
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
                    ProductVariant::where('id', $item->variant_id)->update([
                        'reserved_stock' => DB::raw("GREATEST(0, COALESCE(reserved_stock, 0) - {$item->quantity})")
                    ]);
                }
            }

            if ($order->voucher_id) {
                DB::table('vouchers')->where('id', $order->voucher_id)->decrement('usage_count');
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