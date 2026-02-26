<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Wishlist;
use App\Services\CheckoutTaxResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CartController extends Controller
{
    /**
     * Show the frontend cart page
     */
    public function index()
    {
        return view('frontend.pages.cart');
    }

    /**
     * Show the checkout page
     */
    public function checkout()
    {
        $user = Auth::user();
        $items = $this->getUserCartItems();
        $summary = $this->calculateCheckoutSummary($items);

        $nameParts = preg_split('/\s+/', trim((string) ($user->name ?? '')));
        $firstName = $nameParts[0] ?? '';
        $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '';

        return view('frontend.pages.checkout', [
            'cartItems' => $items,
            'subtotal' => $summary['subtotal'],
            'vatRate' => $summary['vat_rate'],
            'vatAmount' => $summary['tax_amount'],
            'taxLabel' => $summary['tax_label'],
            'discountAmount' => $summary['discount_amount'],
            'total' => $summary['total'],
            'firstName' => $firstName,
            'lastName' => $lastName,
            'user' => $user,
        ]);
    }

    /**
     * Get all frontend cart items for the authenticated user
     */
    public function items()
    {
        $items = $this->getUserCartItems();

        return response()->json([
            'items' => $items,
            'count' => $items->count(),
        ]);
    }

    /**
     * Add or update a product in the frontend cart
     */
    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity'   => 'nullable|integer|min:1',
        ]);

        $productId = $validated['product_id'];
        $variantId = isset($validated['variant_id']) ? (int) $validated['variant_id'] : null;
        $product = Product::findOrFail($productId);
        if ($variantId) {
            $variant = ProductVariant::findOrFail($variantId);
            if ((int) $variant->product_id !== (int) $productId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid variant for this product.',
                ], 422);
            }
        }

        $minimumOrderQty = max(1, (int) ($product->minimum_order_qty ?? 1));
        $quantity  = max(1, (int) ($validated['quantity'] ?? 1));

        if ($quantity < $minimumOrderQty) {
            $quantity = $minimumOrderQty;
        } elseif ($quantity > $minimumOrderQty) {
            $quantity = (int) (ceil($quantity / $minimumOrderQty) * $minimumOrderQty);
        }

        $cartItem = Cart::where('user_id', Auth::id())
                        ->where('product_id', $productId)
                        ->where('cart_type', 'frontend')
                        ->when(
                            $variantId !== null,
                            fn ($q) => $q->where('variant_id', $variantId),
                            fn ($q) => $q->whereNull('variant_id')
                        )
                        ->first();

        if ($cartItem) {
            $cartItem->quantity = $cartItem->quantity + $quantity;
            $cartItem->save();
            $action = 'updated';
        } else {
            Cart::create([
                'user_id'    => Auth::id(),
                'product_id' => $productId,
                'variant_id' => $variantId,
                'cart_type'  => 'frontend',
                'vendor_id'  => $product->vendor_id ?? null,
                'quantity'   => $quantity,
            ]);
            $action = 'added';
        }

        $count = Cart::where('user_id', Auth::id())
                     ->where('cart_type', 'frontend')
                     ->sum('quantity');

        $removedFromWishlist = Wishlist::where('user_id', Auth::id())
            ->where('product_id', $productId)
            ->delete() > 0;

        $wishlistCount = Wishlist::where('user_id', Auth::id())->count();

        return response()->json([
            'success'               => true,
            'action'                => $action,
            'count'                 => (int) $count,
            'variant_id'            => $variantId,
            'removed_from_wishlist' => $removedFromWishlist,
            'wishlist_count'        => (int) $wishlistCount,
            'applied_quantity'      => (int) $quantity,
            'minimum_order_qty'     => (int) $minimumOrderQty,
        ]);
    }

    /**
     * Remove a product from the frontend cart
     */
    public function remove(Request $request)
    {
        $validated = $request->validate([
            'cart_id'    => 'nullable|integer|exists:carts,id',
            'product_id' => 'nullable|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
        ]);

        $query = Cart::where('user_id', Auth::id())
            ->where('cart_type', 'frontend');

        if (!empty($validated['cart_id'])) {
            $query->where('id', (int) $validated['cart_id']);
        } elseif (!empty($validated['product_id'])) {
            $variantId = isset($validated['variant_id']) ? (int) $validated['variant_id'] : null;
            $query->where('product_id', (int) $validated['product_id'])
                ->when(
                    $variantId !== null,
                    fn ($q) => $q->where('variant_id', $variantId),
                    fn ($q) => $q->whereNull('variant_id')
                );
        } else {
            return response()->json([
                'success' => false,
                'message' => 'cart_id or product_id is required.',
            ], 422);
        }

        $query->delete();

        $count = Cart::where('user_id', Auth::id())
                     ->where('cart_type', 'frontend')
                     ->sum('quantity');

        return response()->json([
            'success' => true,
            'count'   => (int) $count,
        ]);
    }

    /**
     * Update quantity of a cart item
     */
    public function updateQuantity(Request $request)
    {
        $validated = $request->validate([
            'cart_id'    => 'nullable|integer|exists:carts,id',
            'product_id' => 'nullable|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $query = Cart::where('user_id', Auth::id())
            ->where('cart_type', 'frontend');

        if (!empty($validated['cart_id'])) {
            $query->where('id', (int) $validated['cart_id']);
        } elseif (!empty($validated['product_id'])) {
            $variantId = isset($validated['variant_id']) ? (int) $validated['variant_id'] : null;
            $query->where('product_id', (int) $validated['product_id'])
                ->when(
                    $variantId !== null,
                    fn ($q) => $q->where('variant_id', $variantId),
                    fn ($q) => $q->whereNull('variant_id')
                );
        } else {
            return response()->json([
                'success' => false,
                'message' => 'cart_id or product_id is required.',
            ], 422);
        }

        $cartItem = $query->first();

        if ($cartItem) {
            $cartItem->quantity = $validated['quantity'];
            $cartItem->save();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Clear all frontend cart items for the user
     */
    public function clear()
    {
        Cart::where('user_id', Auth::id())
            ->where('cart_type', 'frontend')
            ->delete();

        return response()->json(['success' => true, 'count' => 0]);
    }

    /**
     * Place order from frontend checkout
     */
    public function placeOrder(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'nullable|string|max:255|required_unless:ship_different,1',
            'email' => 'nullable|email|max:255|required_unless:ship_different,1',
            'phone' => 'nullable|string|max:50|required_unless:ship_different,1',
            'address' => 'nullable|string|max:500|required_unless:ship_different,1',
            'outlet_name' => 'nullable|string|max:255',
            'ship_different' => 'nullable|boolean',
            'shipping_first_name' => 'nullable|string|max:255|required_if:ship_different,1',
            'shipping_last_name' => 'nullable|string|max:255',
            'shipping_email' => 'nullable|email|max:255|required_if:ship_different,1',
            'shipping_phone' => 'nullable|string|max:50|required_if:ship_different,1',
            'shipping_street_address' => 'nullable|string|max:500|required_if:ship_different,1',
            'shipping_city' => 'nullable|string|max:255|required_if:ship_different,1',
            'shipping_state' => 'nullable|string|max:255|required_if:ship_different,1',
            'shipping_zip_code' => 'nullable|string|max:50|required_if:ship_different,1',
            'shipping_country' => 'nullable|string|max:255|required_if:ship_different,1',
            'shipping_outlet_name' => 'nullable|string|max:255',
        ]);

        $cartRows = Cart::where('user_id', Auth::id())
            ->where('cart_type', 'frontend')
            ->with(['product.category', 'variant'])
            ->get()
            ->filter(fn ($item) => $item->product)
            ->values();

        if ($cartRows->isEmpty()) {
            return redirect()->route('checkout.index')->with('error', 'Your cart is empty.');
        }

        $shipDifferent = (bool) ($validated['ship_different'] ?? false);
        $checkoutItems = $this->getUserCartItems();
        $summary = $this->calculateCheckoutSummary($checkoutItems);

        $shippingName = trim((($validated['shipping_first_name'] ?? '') . ' ' . ($validated['shipping_last_name'] ?? '')));
        $shippingName = $shippingName !== '' ? $shippingName : null;

        // DB billing_* columns are non-nullable. If ship_different is enabled, reuse shipping info as billing source.
        $billingName = $shipDifferent ? ($shippingName ?? ($validated['shipping_first_name'] ?? null)) : ($validated['first_name'] ?? null);
        $billingEmail = $shipDifferent ? ($validated['shipping_email'] ?? null) : ($validated['email'] ?? null);
        $billingPhone = $shipDifferent ? ($validated['shipping_phone'] ?? null) : ($validated['phone'] ?? null);
        $billingAddress = $shipDifferent ? ($validated['shipping_street_address'] ?? null) : ($validated['address'] ?? null);
        $billingOutletName = $shipDifferent
            ? ($validated['shipping_outlet_name'] ?? null)
            : ($validated['outlet_name'] ?? null);

        DB::beginTransaction();
        try {
            $orderNo = $this->generateUniqueOrderNoForUser(Auth::user());

            $order = Order::create([
                'order_no' => $orderNo,
                'user_id' => Auth::id(),
                'status' => 'pending',
                'shipping_method' => 'frontend_checkout',
                'ship_different' => $shipDifferent,
                'billing_name' => $billingName,
                'billing_email' => $billingEmail,
                'billing_phone' => $billingPhone,
                'billing_address' => $billingAddress,
                'billing_outlet_name' => $billingOutletName,
                'shipping_name' => $shipDifferent ? $shippingName : null,
                'shipping_email' => $shipDifferent ? ($validated['shipping_email'] ?? null) : null,
                'shipping_phone' => $shipDifferent ? ($validated['shipping_phone'] ?? null) : null,
                'shipping_address' => $shipDifferent ? ($validated['shipping_street_address'] ?? null) : null,
                'shipping_city' => $shipDifferent ? ($validated['shipping_city'] ?? null) : null,
                'shipping_state' => $shipDifferent ? ($validated['shipping_state'] ?? null) : null,
                'shipping_zip_code' => $shipDifferent ? ($validated['shipping_zip_code'] ?? null) : null,
                'shipping_country' => $shipDifferent ? ($validated['shipping_country'] ?? null) : null,
                'shipping_outlet_name' => $shipDifferent ? ($validated['shipping_outlet_name'] ?? null) : null,
                'subtotal_amount' => $summary['subtotal'],
                'tax_amount' => $summary['tax_amount'],
                'discount_amount' => $summary['discount_amount'],
                'total_amount' => $summary['total'],
                'tax_label' => $summary['tax_label'],
                'vat_rate' => $summary['vat_rate'],
                'placed_at' => now(),
            ]);

            foreach ($cartRows as $item) {
                $product = $item->product;
                $variant = $item->variant;
                $unitPrice = (float) $this->resolveCartItemUnitPrice($product, $variant);
                $variantLabel = $this->resolveVariantLabel($variant);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'variant_id' => $variant?->id,
                    'vendor_id' => $item->vendor_id ?: $product->vendor_id,
                    'product_name' => $product->name,
                    'category_name' => $product->category->name ?? 'General',
                    'variant_label' => $variantLabel,
                    'product_image' => $product->thumb_image,
                    'unit_price' => $unitPrice,
                    'quantity' => (int) $item->quantity,
                    'line_total' => round($unitPrice * (int) $item->quantity, 2),
                ]);
            }

            Cart::where('user_id', Auth::id())
                ->where('cart_type', 'frontend')
                ->delete();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()
                ->route('checkout.index')
                ->with('error', 'Order placement failed: ' . $e->getMessage())
                ->withInput();
        }

        return redirect()
            ->route('orders.show', $order->id)
            ->with('success', 'Order placed successfully. Reference: ' . $order->order_no);
    }

    private function resolveVariantLabel(?ProductVariant $variant): ?string
    {
        if (!$variant) {
            return null;
        }

        $label = trim((string) ($variant->name ?? ''));
        if ($label === '') {
            $parts = array_filter([
                trim((string) ($variant->color ?? '')),
                trim((string) ($variant->size ?? '')),
            ]);
            $label = implode(' - ', $parts);
        }

        return $label !== '' ? $label : null;
    }

    private function resolveCartItemUnitPrice(Product $product, ?ProductVariant $variant): float
    {
        $user = Auth::user();
        $isOutletRole = $user->hasRole('Outlet User') || $user->hasRole('User');

        $price = $isOutletRole
            ? ($variant ? ($variant->outlet_price ?: $product->outlet_price ?: $product->price) : ($product->outlet_price ?? $product->price))
            : ($variant ? ($variant->price ?: $product->price) : $product->price);

        return (float) $price;
    }

    private function calculateCheckoutSummary($items): array
    {
        $subtotal = $items->sum(function ($item) {
            return ((float) $item['price']) * ((int) $item['quantity']);
        });

        $productsById = Product::query()
            ->whereIn('id', $items->pluck('product_id')->all())
            ->get()
            ->keyBy('id');

        $taxResolver = app(CheckoutTaxResolver::class);
        $taxAmount = 0.0;
        $appliedTaxSignatures = [];
        $hasDefaultFlatTax = false;
        $defaultFlatTaxValue = 0.0;

        foreach ($items as $item) {
            $lineSubtotal = ((float) $item['price']) * ((int) $item['quantity']);
            $product = $productsById->get((int) $item['product_id']);
            $lineTax = $taxResolver->resolveForLine($product, $lineSubtotal);

            if ($lineTax['source'] === 'default' && $lineTax['type'] === 'flat') {
                $hasDefaultFlatTax = true;
                $defaultFlatTaxValue = max($defaultFlatTaxValue, (float) $lineTax['value']);
            } else {
                $taxAmount += (float) $lineTax['amount'];
            }

            if ($lineTax['source'] !== 'none') {
                $appliedTaxSignatures[] = ($lineTax['source'] . ':' . ($lineTax['type'] ?? 'none') . ':' . (string) $lineTax['value']);
            }
        }

        if ($hasDefaultFlatTax && $items->isNotEmpty()) {
            $taxAmount += $defaultFlatTaxValue;
        }

        $taxAmount = round($taxAmount, 2);
        $taxLabel = 'VAT / Tax';
        $vatRate = null;

        $defaultTax = $taxResolver->getDefaultTax();
        $uniqueSignatures = array_values(array_unique($appliedTaxSignatures));
        if (count($uniqueSignatures) > 1) {
            $taxLabel = 'VAT / Tax (Mixed)';
        } elseif (count($uniqueSignatures) === 1) {
            [$source, $type, $value] = explode(':', $uniqueSignatures[0]);
            if ($type === 'percent') {
                $vatRate = (float) $value;
                $taxLabel = 'VAT (' . rtrim(rtrim(number_format($vatRate, 2, '.', ''), '0'), '.') . '%)';
            } elseif ($type === 'flat') {
                $taxLabel = $source === 'product' ? 'Product VAT (Flat)' : 'VAT (Flat)';
            }
        } elseif ($defaultTax && $defaultTax->type === 'percent') {
            $vatRate = (float) $defaultTax->value;
            $taxLabel = 'VAT (' . rtrim(rtrim(number_format($vatRate, 2, '.', ''), '0'), '.') . '%)';
        } elseif ($defaultTax && $defaultTax->type === 'flat') {
            $taxLabel = 'VAT (Flat)';
        }

        $discountAmount = 0.0;
        $total = $subtotal + $taxAmount - $discountAmount;

        return [
            'subtotal' => round($subtotal, 2),
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total' => round($total, 2),
            'tax_label' => $taxLabel,
            'vat_rate' => $vatRate,
        ];
    }

    private function getUserCartItems()
    {
        return Cart::where('user_id', Auth::id())
                    ->where('cart_type', 'frontend')
                    ->with(['product.category', 'variant'])
                    ->get()
                    ->map(function ($item) {
                        if (!$item->product) {
                            return null;
                        }

                        $product = $item->product;
                        $imagePath = $product->thumb_image;
                        $imageUrl = (strpos($imagePath, 'http') === 0)
                            ? $imagePath
                            : (file_exists(public_path($imagePath))
                                ? asset($imagePath)
                                : asset('storage/' . $imagePath));

                        $variant = $item->variant;
                        $price = $this->resolveCartItemUnitPrice($product, $variant);
                        $variantLabel = $this->resolveVariantLabel($variant);

                        return [
                            'id' => $item->id,
                            'product_id' => $product->id,
                            'variant_id' => $variant?->id,
                            'name' => $product->name,
                            'price' => (float) $price,
                            'image' => $imageUrl,
                            'category' => $product->category->name ?? 'General',
                            'variant_label' => $variantLabel,
                            'quantity' => (int) ($item->quantity ?? 1),
                        ];
                    })
                    ->filter()
                    ->values();
    }

    private function generateUniqueOrderNoForUser($user): string
    {
        $isOutletUser = $user && ($user->hasRole('Outlet User') || $user->hasRole('Outlet'));
        $prefix = $isOutletUser ? 'DS' : 'ORD';

        do {
            $orderNo = $prefix . '-' . strtoupper(Str::random(10));
        } while (Order::query()->where('order_no', $orderNo)->exists());

        return $orderNo;
    }
}
