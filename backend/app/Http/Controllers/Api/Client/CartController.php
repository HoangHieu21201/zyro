<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Cart\AddToCartRequest;
use App\Http\Requests\Client\Cart\UpdateCartRequest;
use App\Http\Requests\Client\Cart\MergeCartRequest;
use App\Http\Requests\Client\Cart\AddLookbookCartRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class CartController extends Controller
{
    private function resolveUserOrSession(Request $request)
    {
        $user = auth('sanctum')->user();
        $sessionId = $request->header('X-Cart-Session-Id');
        return [$user, $sessionId];
    }

    public function index(Request $request): JsonResponse
    {
        [$user, $sessionId] = $this->resolveUserOrSession($request);

        if (!$user && !$sessionId) {
            return response()->json(['success' => true, 'data' => [], 'cart_count' => 0]);
        }

        if ($user) {
            $cart = Cart::firstOrCreate(['user_id' => $user->id], ['expired_at' => Carbon::now()->addDays(30)]);
        } else {
            $cart = Cart::firstOrCreate(['session_id' => $sessionId], ['expired_at' => Carbon::now()->addDays(30)]);
        }

        $cartItems = $cart->items()
            ->with(['variant.attributeValues.attribute', 'variant.product', 'lookbook'])
            ->get();

        $formattedItems = $cartItems->map(function ($item) {
            $variant = $item->variant;
            $product = $variant ? $variant->product : null;

            $isAvailable = $variant && $product && $product->status === 'published' && !$product->trashed();
            
            $currentStock = $variant ? ($variant->stock_quantity - ($variant->reserved_stock ?? 0)) : 0;
            $stockWarning = $isAvailable && ($item->quantity > $currentStock) ? true : false;

            return [
                'item_id' => $item->id,
                'product_id' => $item->product_id,
                'variant_id' => $item->variant_id,
                'quantity' => $item->quantity,
                'lookbook_id' => $item->lookbook_id,
                'lookbook_name' => $item->lookbook ? $item->lookbook->name : 'Combo Phong Cách',
                'lookbook_image' => $item->lookbook ? $this->getImageUrl($item->lookbook->main_image) : null,
                'lookbook_selections' => $item->lookbook_selections,
                'current_stock' => $currentStock,
                'current_price' => $variant ? ($variant->promotional_price ?? $variant->price) : 0,
                'product_name' => $product ? $product->name : 'Sản phẩm không còn tồn tại',
                'product_slug' => $product ? $product->slug : '',
                'image' => $variant && $variant->image_url ? $this->getImageUrl($variant->image_url) : ($product ? $this->getImageUrl($product->thumbnail_image) : null),
                'attributes' => $variant ? $variant->attributeValues->pluck('value')->join(' - ') : '',
                'is_available' => $isAvailable,
                'stock_warning' => $stockWarning
            ];
        });

        $cartCount = 0;
        $comboTracker = [];

        foreach ($formattedItems as $item) {
            if (!empty($item['lookbook_id'])) {
                if (!in_array($item['lookbook_id'], $comboTracker)) {
                    $comboTracker[] = $item['lookbook_id'];
                    $cartCount++;
                }
            } else {
                $cartCount++;
            }
        }

        return response()->json([
            'success' => true, 
            'data' => $formattedItems,
            'cart_count' => $cartCount
        ]);
    }

    public function add(AddToCartRequest $request): JsonResponse
    {
        [$user, $sessionId] = $this->resolveUserOrSession($request);

        if (!$user && !$sessionId) {
            return response()->json(['success' => false, 'message' => 'Hệ thống không nhận diện được phiên làm việc.'], 400);
        }
        
        $lockKey = 'cart_action_lock_' . ($user ? 'u_' . $user->id : 's_' . $sessionId);
        $lock = Cache::lock($lockKey, 3);

        if (!$lock->get()) {
            return response()->json(['success' => false, 'message' => 'Hệ thống đang xử lý giỏ hàng, vui lòng thao tác chậm lại.'], 429);
        }

        try {
            $variantId = $request->input('variant_id');
            $quantity = $request->input('quantity');

            $variant = ProductVariant::with('product')->find($variantId);

            if (!$variant || !$variant->product || $variant->product->status !== 'published') {
                return response()->json(['success' => false, 'message' => 'Sản phẩm này đã ngừng kinh doanh.'], 400);
            }

            $currentStock = $variant->stock_quantity - ($variant->reserved_stock ?? 0);

            if ($user) {
                $cart = Cart::firstOrCreate(['user_id' => $user->id], ['expired_at' => Carbon::now()->addDays(30)]);
            } else {
                $cart = Cart::firstOrCreate(['session_id' => $sessionId], ['expired_at' => Carbon::now()->addDays(30)]);
            }
            
            $existingItem = CartItem::where('cart_id', $cart->id)
                                    ->where('variant_id', $variantId)
                                    ->whereNull('lookbook_id') 
                                    ->first();

            $totalRequested = $existingItem ? $existingItem->quantity + $quantity : $quantity;

            if ($totalRequested > 50) {
                return response()->json(['success' => false, 'message' => 'Bạn chỉ được mua tối đa 50 sản phẩm loại này.'], 400);
            }

            if ($totalRequested > $currentStock) {
                return response()->json(['success' => false, 'message' => "Sản phẩm chỉ còn {$currentStock} chiếc trong kho."], 400);
            }

            if ($existingItem) {
                $existingItem->quantity = $totalRequested;
                $existingItem->save();
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $variant->product_id, 
                    'variant_id' => $variantId,
                    'quantity' => $quantity,
                    'lookbook_id' => null 
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Đã thêm vào giỏ hàng.']);
            
        } finally {
            $lock->release();
        }
    }

    public function updateQuantity(UpdateCartRequest $request, $itemId): JsonResponse
    {
        [$user, $sessionId] = $this->resolveUserOrSession($request);

        if (!$user && !$sessionId) {
            return response()->json(['success' => false, 'message' => 'Phiên làm việc đã hết hạn.'], 400);
        }

        $lockKey = 'cart_action_lock_' . ($user ? 'u_' . $user->id : 's_' . $sessionId);
        $lock = Cache::lock($lockKey, 3);

        if (!$lock->get()) {
            return response()->json(['success' => false, 'message' => 'Vui lòng thao tác chậm lại.'], 429);
        }

        try {
            $quantity = (int) $request->input('quantity');
            
            if ($quantity <= 0 || $quantity > 50) {
                return response()->json(['success' => false, 'message' => 'Lỗi bảo mật: Số lượng cập nhật không hợp lệ (Giới hạn: 1 - 50 sản phẩm).'], 400);
            }

            $cart = $user ? Cart::where('user_id', $user->id)->first() : Cart::where('session_id', $sessionId)->first();
            if (!$cart) return response()->json(['success' => false, 'message' => 'Giỏ hàng trống.'], 404);

            $cartItem = CartItem::where('cart_id', $cart->id)->find($itemId);
            if (!$cartItem) return response()->json(['success' => false, 'message' => 'Sản phẩm không có trong giỏ.'], 404);

            // =========================================================================================
            // ĐÃ FIX QUY LUẬT TỶ LỆ COMBO BẰNG TOÁN HỌC (Multiplier = NewQty / OldQty)
            // Khách có xé lẻ request ở Frontend thì Backend vẫn tự cân bằng lại TẤT CẢ các món trong Combo
            // =========================================================================================
            if ($cartItem->lookbook_id) {
                $comboItems = CartItem::where('cart_id', $cart->id)->where('lookbook_id', $cartItem->lookbook_id)->get();
                
                // Tính hệ số thay đổi dựa trên sản phẩm cụ thể vừa bị gửi Request
                $oldQty = $cartItem->quantity;
                $ratio = $quantity / $oldQty; 

                // Quét kho toàn bộ Combo trước khi áp dụng để đảm bảo an toàn
                foreach ($comboItems as $cItem) {
                    $targetQty = round($cItem->quantity * $ratio);
                    $v = ProductVariant::find($cItem->variant_id);
                    $stock = $v ? ($v->stock_quantity - ($v->reserved_stock ?? 0)) : 0;
                    
                    if (!$v || $targetQty > $stock) {
                        return response()->json(['success' => false, 'message' => "Một sản phẩm trong bộ sưu tập chỉ còn {$stock} chiếc, không đủ để tăng số lượng Combo."], 400);
                    }
                }
                
                // An toàn -> Áp dụng nhân hệ số cho toàn bộ món đồ trong Combo (Giữ nguyên cấu trúc Lookbook)
                foreach ($comboItems as $cItem) {
                    $cItem->quantity = round($cItem->quantity * $ratio);
                    $cItem->save();
                }
            } else {
                // Sản phẩm lẻ thì cập nhật bình thường
                $variant = ProductVariant::find($cartItem->variant_id);
                $currentStock = $variant ? ($variant->stock_quantity - ($variant->reserved_stock ?? 0)) : 0;

                if (!$variant || $quantity > $currentStock) {
                    return response()->json(['success' => false, 'message' => "Chỉ còn {$currentStock} sản phẩm trong kho.", 'current_stock' => $currentStock], 400);
                }

                $cartItem->quantity = $quantity;
                $cartItem->save();
            }

            return response()->json(['success' => true]);
        } finally {
            $lock->release();
        }
    }

    public function remove(Request $request, $itemId): JsonResponse
    {
        [$user, $sessionId] = $this->resolveUserOrSession($request);
        $cart = $user ? Cart::where('user_id', $user->id)->first() : Cart::where('session_id', $sessionId)->first();
        
        if ($cart) {
            $cartItem = CartItem::where('cart_id', $cart->id)->find($itemId);
            
            if ($cartItem) {
                // LUÔN LUÔN DUY TRÌ: Đã xóa là xóa sạch nguyên Combo (Atomic Delete)
                if ($cartItem->lookbook_id) {
                    CartItem::where('cart_id', $cart->id)->where('lookbook_id', $cartItem->lookbook_id)->delete();
                } else {
                    $cartItem->delete();
                }
            }
        }
        return response()->json(['success' => true, 'message' => 'Đã xóa sản phẩm khỏi giỏ.']);
    }

    public function addLookbook(AddLookbookCartRequest $request): JsonResponse
    {
        [$user, $sessionId] = $this->resolveUserOrSession($request);

        if (!$user && !$sessionId) {
            return response()->json(['success' => false, 'message' => 'Thiếu thông tin phiên làm việc.'], 400);
        }
        
        $lockKey = 'cart_action_lock_' . ($user ? 'u_' . $user->id : 's_' . $sessionId);
        $lock = Cache::lock($lockKey, 3);

        if (!$lock->get()) {
            return response()->json(['success' => false, 'message' => 'Hệ thống đang xử lý giỏ hàng, vui lòng thao tác chậm lại.'], 429);
        }

        try {
            $lookbookId = $request->input('lookbook_id');
            $requestedCombos = $request->input('quantity', 1);
            $selections = $request->input('lookbook_selections', []);

            if (empty($selections)) {
                return response()->json(['success' => false, 'message' => 'Vui lòng chọn sản phẩm trong combo.'], 400);
            }

            if ($user) {
                $cart = Cart::firstOrCreate(['user_id' => $user->id], ['expired_at' => Carbon::now()->addDays(30)]);
            } else {
                $cart = Cart::firstOrCreate(['session_id' => $sessionId], ['expired_at' => Carbon::now()->addDays(30)]);
            }

            DB::beginTransaction();
            try {
                $maxCombosToAdd = $requestedCombos;

                foreach ($selections as $item) {
                    $variant = ProductVariant::with('product')->find($item['variant_id']);
                    
                    if (!$variant || !$variant->product || $variant->product->status !== 'published') {
                        $maxCombosToAdd = 0; 
                        break;
                    }

                    $currentStock = $variant->stock_quantity - ($variant->reserved_stock ?? 0);
                    $existingItem = CartItem::where('cart_id', $cart->id)
                                            ->where('variant_id', $item['variant_id'])
                                            ->where('lookbook_id', $lookbookId)
                                            ->first();

                    $existingQty = $existingItem ? $existingItem->quantity : 0;
                    
                    $multiplier = (int)($item['quantity'] ?? 1);
                    if ($multiplier <= 0) $multiplier = 1;

                    $availableForNew = $currentStock - $existingQty;
                    $possibleCombos = floor($availableForNew / $multiplier);

                    if ($possibleCombos < $maxCombosToAdd) {
                        $maxCombosToAdd = $possibleCombos;
                    }
                }

                if ($maxCombosToAdd <= 0) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Một số sản phẩm trong bộ sưu tập đã cạn kho hoặc bạn đã đạt giới hạn mua.'], 400);
                }

                $finalAddedCombos = min($maxCombosToAdd, 50);
                $addedCount = 0;

                foreach ($selections as $item) {
                    $variantId = $item['variant_id'];
                    $multiplier = (int)($item['quantity'] ?? 1);
                    if ($multiplier <= 0) $multiplier = 1;
                    
                    $itemQty = $multiplier * $finalAddedCombos;
                    $attributes = $item['attributes'] ?? null;

                    $variant = ProductVariant::find($variantId);
                    
                    $existingItem = CartItem::where('cart_id', $cart->id)
                                            ->where('variant_id', $variantId)
                                            ->where('lookbook_id', $lookbookId)
                                            ->first();

                    $totalQty = $existingItem ? $existingItem->quantity + $itemQty : $itemQty;
                    if ($totalQty > 50) $totalQty = 50;

                    if ($existingItem) {
                        $existingItem->quantity = $totalQty;
                        $existingSelections = is_array($existingItem->lookbook_selections) ? $existingItem->lookbook_selections : [];
                        $existingItem->lookbook_selections = array_merge($existingSelections, ['attributes' => $attributes]);
                        $existingItem->save();
                    } else {
                        CartItem::create([
                            'cart_id' => $cart->id,
                            'product_id' => $variant->product_id,
                            'variant_id' => $variantId,
                            'quantity' => $totalQty,
                            'lookbook_id' => $lookbookId,
                            'lookbook_selections' => ['attributes' => $attributes]
                        ]);
                    }
                    $addedCount++;
                }

                DB::commit();
                
                $msg = "Đã thêm bộ sưu tập vào giỏ hàng.";
                if ($finalAddedCombos < $requestedCombos) {
                    $msg = "Chỉ thêm được $finalAddedCombos combo do giới hạn tồn kho.";
                }
                
                return response()->json(['success' => true, 'message' => $msg]);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Lỗi thêm Lookbook: ' . $e->getMessage()], 500);
            }
        } finally {
            $lock->release(); 
        }
    }

    public function merge(MergeCartRequest $request): JsonResponse
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Bạn cần đăng nhập để đồng bộ giỏ hàng.'], 401);
        }

        $lockKey = 'cart_action_lock_u_' . $user->id;
        $lock = Cache::lock($lockKey, 5);

        if (!$lock->get()) {
            return response()->json(['success' => false, 'message' => 'Hệ thống đang đồng bộ, vui lòng thao tác chậm lại.'], 429);
        }

        try {
            $localItems = $request->input('local_items', []);

            if (empty($localItems)) {
                return response()->json(['success' => true]);
            }

            $cart = Cart::firstOrCreate(
                ['user_id' => $user->id],
                ['expired_at' => Carbon::now()->addDays(30)]
            );

            DB::beginTransaction();
            try {
                foreach ($localItems as $item) {
                    $variantId = $item['variant_id'];
                    $quantity = (int) $item['quantity'];
                    
                    $lookbookId = $item['lookbook_id'] ?? null;
                    $selections = $item['lookbook_selections'] ?? null;

                    $variant = ProductVariant::with('product')->find($variantId);
                    
                    if (!$variant || !$variant->product || $variant->product->status !== 'published') {
                        continue;
                    }

                    $currentStock = $variant->stock_quantity - ($variant->reserved_stock ?? 0);

                    $query = CartItem::where('cart_id', $cart->id)->where('variant_id', $variantId);
                                     
                    if ($lookbookId) {
                        $query->where('lookbook_id', $lookbookId);
                    } else {
                        $query->whereNull('lookbook_id');
                    }
                    
                    $existingItem = $query->first();

                    $totalRequested = $existingItem ? $existingItem->quantity + $quantity : $quantity;
                    if ($totalRequested > 50) $totalRequested = 50;
                    if ($totalRequested > $currentStock) $totalRequested = $currentStock;
                    if ($totalRequested <= 0) continue;

                    if ($existingItem) {
                        $existingItem->quantity = $totalRequested;
                        if ($lookbookId && $selections) {
                            $existingItem->lookbook_selections = $selections;
                        }
                        $existingItem->save();
                    } else {
                        CartItem::create([
                            'cart_id' => $cart->id,
                            'product_id' => $variant->product_id,
                            'variant_id' => $variantId,
                            'quantity' => $totalRequested,
                            'lookbook_id' => $lookbookId,
                            'lookbook_selections' => $selections
                        ]);
                    }
                }
                DB::commit();
                return response()->json(['success' => true, 'message' => 'Đồng bộ giỏ hàng thành công']);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Lỗi đồng bộ giỏ hàng'], 500);
            }
        } finally {
            $lock->release();
        }
    }

    private function getImageUrl($path)
    {
        if (!$path) return null;
        if (str_starts_with($path, 'http')) return $path;
        return asset('storage/' . ltrim($path, '/'));
    }
}