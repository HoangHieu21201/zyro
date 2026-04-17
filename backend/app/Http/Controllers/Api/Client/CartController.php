<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Cart\AddToCartRequest;
use App\Http\Requests\Client\Cart\UpdateCartRequest;
use App\Http\Requests\Client\Cart\MergeCartRequest;
use Illuminate\Http\JsonResponse;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CartController extends Controller
{
    /**
     * LẤY DANH SÁCH GIỎ HÀNG
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        
        $cart = Cart::firstOrCreate(
            ['user_id' => $user->id],
            ['expired_at' => Carbon::now()->addDays(30)] 
        );

        $cartItems = $cart->items()
            ->with(['variant.attributeValues.attribute', 'variant.product'])
            ->get();

        $formattedItems = $cartItems->map(function ($item) {
            $variant = $item->variant;
            $product = $variant ? $variant->product : null;

            $isAvailable = $variant && $product && $product->status === 'published' && !$product->trashed();
            
            // ĐÃ FIX: Sửa stock thành stock_quantity cho khớp Database
            $currentStock = $variant ? ($variant->stock_quantity - ($variant->reserved_stock ?? 0)) : 0;
            $stockWarning = $isAvailable && ($item->quantity > $currentStock) ? true : false;

            return [
                'item_id' => $item->id,
                'product_id' => $item->product_id,
                'variant_id' => $item->variant_id,
                'quantity' => $item->quantity,
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

        return response()->json([
            'success' => true, 
            'data' => $formattedItems
        ]);
    }

    /**
     * THÊM SẢN PHẨM VÀO GIỎ
     */
    public function add(AddToCartRequest $request): JsonResponse
    {
        $user = Auth::user();
        $variantId = $request->input('variant_id');
        $quantity = $request->input('quantity');

        $variant = ProductVariant::with('product')->find($variantId);

        if (!$variant || !$variant->product || $variant->product->status !== 'published') {
            return response()->json(['success' => false, 'message' => 'Sản phẩm này đã ngừng kinh doanh.'], 400);
        }

        // ĐÃ FIX: Sửa stock thành stock_quantity
        $currentStock = $variant->stock_quantity - ($variant->reserved_stock ?? 0);

        $cart = Cart::firstOrCreate(
            ['user_id' => $user->id],
            ['expired_at' => Carbon::now()->addDays(30)]
        );
        
        $existingItem = CartItem::where('cart_id', $cart->id)
                                ->where('variant_id', $variantId)
                                ->first();

        $totalRequested = $existingItem ? $existingItem->quantity + $quantity : $quantity;

        if ($totalRequested > 50) {
            return response()->json(['success' => false, 'message' => 'Bạn chỉ được phép mua tối đa 50 sản phẩm loại này.'], 400);
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
                'quantity' => $quantity
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Đã thêm vào giỏ hàng.']);
    }

    /**
     * CẬP NHẬT SỐ LƯỢNG
     */
    public function updateQuantity(UpdateCartRequest $request, $itemId): JsonResponse
    {
        $user = Auth::user();
        $quantity = $request->input('quantity');

        $cart = Cart::where('user_id', $user->id)->first();
        if (!$cart) return response()->json(['success' => false, 'message' => 'Giỏ hàng trống.'], 404);

        $cartItem = CartItem::where('cart_id', $cart->id)->find($itemId);
        if (!$cartItem) return response()->json(['success' => false, 'message' => 'Sản phẩm không có trong giỏ.'], 404);

        $variant = ProductVariant::find($cartItem->variant_id);
        
        // ĐÃ FIX: Sửa stock thành stock_quantity
        $currentStock = $variant ? ($variant->stock_quantity - ($variant->reserved_stock ?? 0)) : 0;

        if (!$variant || $quantity > $currentStock) {
            return response()->json(['success' => false, 'message' => "Chỉ còn {$currentStock} sản phẩm trong kho.", 'current_stock' => $currentStock], 400);
        }

        $cartItem->quantity = $quantity;
        $cartItem->save();

        return response()->json(['success' => true]);
    }

    /**
     * XÓA ITEM
     */
    public function remove($itemId): JsonResponse
    {
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->first();
        if ($cart) {
            CartItem::where('cart_id', $cart->id)->where('id', $itemId)->delete();
        }
        return response()->json(['success' => true, 'message' => 'Đã xóa sản phẩm khỏi giỏ.']);
    }

    /**
     * GỘP GIỎ HÀNG
     */
    public function merge(MergeCartRequest $request): JsonResponse
    {
        $user = Auth::user();
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

                $variant = ProductVariant::with('product')->find($variantId);
                
                if (!$variant || !$variant->product || $variant->product->status !== 'published') {
                    continue;
                }

                $currentStock = $variant->stock_quantity - ($variant->reserved_stock ?? 0);

                $existingItem = CartItem::where('cart_id', $cart->id)
                                        ->where('variant_id', $variantId)
                                        ->first();

                $totalRequested = $existingItem ? $existingItem->quantity + $quantity : $quantity;
                if ($totalRequested > 50) $totalRequested = 50;
                if ($totalRequested > $currentStock) $totalRequested = $currentStock;
                if ($totalRequested <= 0) continue;

                if ($existingItem) {
                    $existingItem->quantity = $totalRequested;
                    $existingItem->save();
                } else {
                    CartItem::create([
                        'cart_id' => $cart->id,
                        'product_id' => $variant->product_id,
                        'variant_id' => $variantId,
                        'quantity' => $totalRequested
                    ]);
                }
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Đồng bộ giỏ hàng thành công']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Lỗi đồng bộ giỏ hàng'], 500);
        }
    }

    private function getImageUrl($path)
    {
        if (!$path) return null;
        if (str_starts_with($path, 'http')) return $path;
        return 'http://127.0.0.1:8000/storage/' . ltrim($path, '/');
    }
}