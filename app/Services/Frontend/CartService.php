<?php

namespace App\Services\Frontend;

use App\Models\Cart;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Wishlist;
use App\Services\CheckoutDiscountResolver;
use Illuminate\Support\Facades\Auth;

class CartService
{
    public function getUserCartItems()
    {
        $discountResolver = app(CheckoutDiscountResolver::class);

        return Cart::where('user_id', Auth::id())
                    ->where('cart_type', 'frontend')
                    ->with(['product.category', 'product.inventoryStocks', 'variant.inventoryStocks'])
                    ->get()
                    ->map(function ($item) use ($discountResolver) {
                        if (!$item->product || (int) ($item->product->status ?? 0) !== 1) {
                            return null;
                        }

                        $product = $item->product;
                        $imagePath = (string) ($product->thumb_image ?? '');
                        $imageUrl = (strpos($imagePath, 'http') === 0)
                            ? $imagePath
                            : ($imagePath !== '' && file_exists(public_path($imagePath))
                                ? asset($imagePath)
                                : asset('storage/' . $imagePath));

                        $variant = $item->variant;
                        $price = (float) $this->resolveCartItemUnitPrice($product, $variant);
                        $variantLabel = $this->resolveVariantLabel($variant);
                        $availableStock = $this->resolveAvailableStock($product, $variant);
                        $quantity = (int) ($item->quantity ?? 1);
                        $lineSubtotal = round($price * $quantity, 2);

                        $lineDiscount = $discountResolver->resolveForLine($product, $lineSubtotal, $quantity);
                        $lineDiscountAmount = round((float) ($lineDiscount['amount'] ?? 0), 2);
                        $discountPerUnit = $quantity > 0 ? ($lineDiscountAmount / $quantity) : 0.0;
                        $displayPrice = round(max(0, $price - $discountPerUnit), 2);
                        $lineTotalAfterDiscount = round(max(0, $lineSubtotal - $lineDiscountAmount), 2);

                        return [
                            'id' => $item->id,
                            'product_id' => $product->id,
                            'variant_id' => $variant?->id,
                            'name' => $product->name,
                            'price' => (float) $price,
                            'original_price' => (float) $price,
                            'display_price' => (float) $displayPrice,
                            'has_discount' => $lineDiscountAmount > 0,
                            'discount_source' => (string) ($lineDiscount['source'] ?? 'none'),
                            'discount_type' => (string) ($lineDiscount['type'] ?? ''),
                            'discount_value' => (float) ($lineDiscount['value'] ?? 0),
                            'discounts' => $lineDiscount['discounts'] ?? [],
                            'line_discount' => (float) $lineDiscountAmount,
                            'line_total' => (float) $lineSubtotal,
                            'line_total_after_discount' => (float) $lineTotalAfterDiscount,
                            'image' => $imageUrl,
                            'category' => $product->category->name ?? 'General',
                            'variant_label' => $variantLabel,
                            'quantity' => $quantity,
                            'minimum_order_qty' => max(1, (int) ($product->minimum_order_qty ?? 1)),
                            'available_stock' => (int) $availableStock,
                            'product_id' => $product->id,
                        ];
                    })
                    ->filter()
                    ->values();
    }

    public function add(array $validated): array
    {
        $productId = (int) $validated['product_id'];
        $variantId = isset($validated['variant_id']) ? (int) $validated['variant_id'] : null;
        $product = Product::with(['inventoryStocks', 'productType'])
            ->active()
            ->whereHas('category', fn ($q) => $q->active())
            ->findOrFail($productId);

        $productTypeName = trim((string) optional($product->productType)->name);
        if ($productTypeName === '') {
            $productTypeName = trim((string) ($product->product_type ?? ''));
        }
        if (strtolower($productTypeName) === 'upcoming' || str_contains(strtolower($productTypeName), 'upcoming')) {
            return ['success' => false, 'message' => 'This product is coming soon and not available for purchase yet.', 'code' => 422];
        }

        $variant = null;
        if ($variantId) {
            $variant = ProductVariant::with('inventoryStocks')->active()->findOrFail($variantId);
            if ((int) $variant->product_id !== $productId) {
                return ['success' => false, 'message' => 'Invalid variant for this product.', 'code' => 422];
            }
        }

        $minimumOrderQty = max(1, (int) ($product->minimum_order_qty ?? 1));
        $quantity = max(1, (int) ($validated['quantity'] ?? 1));
        if ($quantity < $minimumOrderQty) {
            $quantity = $minimumOrderQty;
        } elseif ($quantity > $minimumOrderQty) {
            $quantity = (int) (ceil($quantity / $minimumOrderQty) * $minimumOrderQty);
        }

        $cartItem = Cart::where('user_id', Auth::id())
                        ->where('product_id', $productId)
                        ->where('cart_type', 'frontend')
                        ->when($variantId !== null, fn ($q) => $q->where('variant_id', $variantId), fn ($q) => $q->whereNull('variant_id'))
                        ->first();

        $availableStock = $this->resolveAvailableStock($product, $variant);
        $currentCartQty = (int) ($cartItem->quantity ?? 0);
        $requestedCartQty = $currentCartQty + $quantity;

        if ($availableStock < 1) {
            return ['success' => false, 'message' => 'This item is out of stock.', 'available_stock' => 0, 'code' => 422];
        }
        if ($requestedCartQty > $availableStock) {
            return ['success' => false, 'message' => 'Requested quantity exceeds available stock. Available stock: ' . $availableStock . '.', 'available_stock' => $availableStock, 'requested_quantity' => $requestedCartQty, 'code' => 422];
        }

        if ($cartItem) {
            $cartItem->quantity = $requestedCartQty;
            $cartItem->save();
            $action = 'updated';
        } else {
            Cart::create([
                'user_id' => Auth::id(),
                'product_id' => $productId,
                'variant_id' => $variantId,
                'cart_type' => 'frontend',
                'vendor_id' => $product->vendor_id ?? null,
                'quantity' => $quantity,
            ]);
            $action = 'added';
        }

        $count = Cart::where('user_id', Auth::id())->where('cart_type', 'frontend')->sum('quantity');
        $removedFromWishlist = Wishlist::where('user_id', Auth::id())->where('product_id', $productId)->delete() > 0;
        $wishlistCount = Wishlist::where('user_id', Auth::id())->count();

        return [
            'success' => true,
            'action' => $action,
            'count' => (int) $count,
            'variant_id' => $variantId,
            'removed_from_wishlist' => $removedFromWishlist,
            'wishlist_count' => (int) $wishlistCount,
            'applied_quantity' => (int) $quantity,
            'minimum_order_qty' => (int) $minimumOrderQty,
        ];
    }

    public function remove(array $validated): array
    {
        $query = Cart::where('user_id', Auth::id())->where('cart_type', 'frontend');

        if (!empty($validated['cart_id'])) {
            $query->where('id', (int) $validated['cart_id']);
        } elseif (!empty($validated['product_id'])) {
            $variantId = isset($validated['variant_id']) ? (int) $validated['variant_id'] : null;
            $query->where('product_id', (int) $validated['product_id'])
                ->when($variantId !== null, fn ($q) => $q->where('variant_id', $variantId), fn ($q) => $q->whereNull('variant_id'));
        } else {
            return ['success' => false, 'message' => 'cart_id or product_id is required.', 'code' => 422];
        }

        $query->delete();
        $count = Cart::where('user_id', Auth::id())->where('cart_type', 'frontend')->sum('quantity');

        return ['success' => true, 'count' => (int) $count];
    }

    public function updateQuantity(array $validated): array
    {
        $query = Cart::where('user_id', Auth::id())->where('cart_type', 'frontend');

        if (!empty($validated['cart_id'])) {
            $query->where('id', (int) $validated['cart_id']);
        } elseif (!empty($validated['product_id'])) {
            $variantId = isset($validated['variant_id']) ? (int) $validated['variant_id'] : null;
            $query->where('product_id', (int) $validated['product_id'])
                ->when($variantId !== null, fn ($q) => $q->where('variant_id', $variantId), fn ($q) => $q->whereNull('variant_id'));
        } else {
            return ['success' => false, 'message' => 'cart_id or product_id is required.', 'code' => 422];
        }

        $cartItem = $query->first();
        if (!$cartItem) {
            return ['success' => true, 'applied_quantity' => 0];
        }

        $cartItem->loadMissing(['product.inventoryStocks', 'variant.inventoryStocks']);
        if (!$cartItem->product) {
            return ['success' => false, 'message' => 'Product not found for this cart item.', 'code' => 422];
        }
        if ((int) ($cartItem->product->status ?? 0) !== 1) {
            return ['success' => false, 'message' => 'This product is inactive.', 'code' => 422];
        }
        if ($cartItem->variant && (int) $cartItem->variant->product_id !== (int) $cartItem->product_id) {
            return ['success' => false, 'message' => 'Invalid variant for this cart item.', 'code' => 422];
        }

        $availableStock = $this->resolveAvailableStock($cartItem->product, $cartItem->variant);
        $minimumOrderQty = max(1, (int) ($cartItem->product->minimum_order_qty ?? 1));
        $requestedQty = max(1, (int) $validated['quantity']);

        if ($requestedQty < $minimumOrderQty) {
            $requestedQty = $minimumOrderQty;
        } elseif ($requestedQty > $minimumOrderQty) {
            $requestedQty = (int) (ceil($requestedQty / $minimumOrderQty) * $minimumOrderQty);
        }

        if ($availableStock < 1) {
            return ['success' => false, 'message' => 'This item is out of stock.', 'available_stock' => 0, 'code' => 422];
        }
        if ($requestedQty > $availableStock) {
            return ['success' => false, 'message' => 'Requested quantity exceeds available stock. Available stock: ' . $availableStock . '.', 'available_stock' => $availableStock, 'requested_quantity' => $requestedQty, 'code' => 422];
        }

        $cartItem->quantity = $requestedQty;
        $cartItem->save();

        return ['success' => true, 'applied_quantity' => (int) $cartItem->quantity];
    }

    public function clear(): array
    {
        Cart::where('user_id', Auth::id())->where('cart_type', 'frontend')->delete();
        return ['success' => true, 'count' => 0];
    }

    public function resolveAvailableStock(Product $product, ?ProductVariant $variant): int
    {
        $outletId = $this->resolveOrderOutletId();
        $stocks = $variant ? $variant->inventoryStocks : $product->inventoryStocks;
        $stockRow = $this->findInventoryStockRowInCollection($stocks, $outletId)
            ?? $this->fetchInventoryStockRow((int) $product->id, $variant?->id, $outletId);
        return max(0, (int) ($stockRow?->quantity ?? 0));
    }

    public function resolveCartItemUnitPrice(Product $product, ?ProductVariant $variant): float
    {
        $user = Auth::user();
        $isOutletRole = $user->hasRole('Outlet User') || $user->hasRole('User');
        $price = $isOutletRole
            ? ($variant ? ($variant->outlet_price ?: $product->outlet_price ?: $product->price) : ($product->outlet_price ?? $product->price))
            : ($variant ? ($variant->price ?: $product->price) : $product->price);
        return (float) $price;
    }

    public function resolveVariantLabel(?ProductVariant $variant): ?string
    {
        if (!$variant) return null;
        $label = trim((string) ($variant->name ?? ''));
        if ($label === '') {
            $parts = array_filter([trim((string) ($variant->color ?? '')), trim((string) ($variant->size ?? ''))]);
            $label = implode(' - ', $parts);
        }
        return $label !== '' ? $label : null;
    }

    public function resolveOrderOutletId(): int
    {
        $user = Auth::user();
        $userOutletId = $user?->outlet_id ?? null;
        return !empty($userOutletId) ? (int) $userOutletId : (int) config('inventory.default_outlet_id', 1);
    }

    private function findInventoryStockRowInCollection($stocks, int $outletId): ?InventoryStock
    {
        if (!$stocks) return null;
        foreach ($stocks as $stock) {
            if ((int) ($stock->outlet_id ?? 0) === $outletId) return $stock;
        }
        return null;
    }

    private function fetchInventoryStockRow(int $productId, ?int $variantId, int $outletId, bool $lock = false): ?InventoryStock
    {
        $query = InventoryStock::query()->where('product_id', $productId)->where('outlet_id', $outletId);
        if ($variantId !== null) {
            $query->where('variant_id', $variantId);
        } else {
            $query->whereNull('variant_id');
        }
        if ($lock) $query->lockForUpdate();
        return $query->first();
    }
}
