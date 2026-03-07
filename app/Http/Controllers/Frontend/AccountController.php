<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SavedPurchaseForm;
use App\Models\SavedPurchaseFormItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    /**
     * Show account control panel with order history on the left
     * and selected order details on the right.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $panel = (string) $request->query('panel', 'dashboard');
        $orders = Order::query()
            ->withCount('items')
            ->withSum('items as total_units', 'quantity')
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $recentOrders = Order::query()
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->take(8)
            ->get(['id', 'order_no', 'created_at']);

        $isOutletRole = $user->hasRole('Outlet User') || $user->hasRole('User');
        $mapOrderFormProduct = function ($product) use ($isOutletRole) {
                $price = $isOutletRole
                    ? (float) ($product->outlet_price ?: $product->price)
                    : (float) $product->price;

                $variants = $product->variants
                    ->map(function ($variant) use ($product, $isOutletRole) {
                        $variantPrice = $isOutletRole
                            ? (float) ($variant->outlet_price ?: $variant->price ?: $product->outlet_price ?: $product->price)
                            : (float) ($variant->price ?: $product->price);

                        $colorRelation = $variant->getRelation('color');
                        $sizeRelation = $variant->getRelation('size');
                        $name = trim((string) ($variant->name ?? ''));
                        $color = trim((string) (is_object($colorRelation) ? ($colorRelation->name ?? '') : ($variant->color ?? '')));
                        $size = trim((string) (is_object($sizeRelation) ? ($sizeRelation->name ?? '') : ($variant->size ?? '')));

                        $labelParts = [];
                        if ($name !== '') {
                            $labelParts[] = preg_replace('/\s+/', ' ', $name);
                        }
                        if ($color !== '' && stripos($name, $color) === false) {
                            $labelParts[] = $color;
                        }
                        if ($size !== '' && stripos($name, $size) === false) {
                            $labelParts[] = $size;
                        }
                        $label = trim(implode(' / ', array_values(array_unique(array_filter($labelParts)))));
                        if ($label === '') {
                            $label = 'Variant #' . $variant->id;
                        }

                        return [
                            'id' => (int) $variant->id,
                            'name' => $label,
                            'label' => $label,
                            'price' => $variantPrice,
                        ];
                    })
                    ->values()
                    ->all();

                return [
                    'id' => (int) $product->id,
                    'name' => (string) $product->name,
                    'price' => $price,
                    'minimum_order_qty' => max(1, (int) ($product->minimum_order_qty ?? 1)),
                    'variants' => $variants,
                ];
            };

        $productsForOrderForm = Product::query()
            ->where('status', 1)
            ->with([
                'variants:id,product_id,name,color,size,color_id,size_id,price,outlet_price',
                'variants.color:id,name',
                'variants.size:id,name',
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'outlet_price', 'minimum_order_qty'])
            ->map($mapOrderFormProduct)
            ->values();

        $reorderSeedRows = [];
        $selectedSavedRequestId = (int) $request->query('saved', 0);
        $savedPurchaseForms = SavedPurchaseForm::query()
            ->where('user_id', $user->id)
            ->withCount('items')
            ->orderByDesc('id')
            ->take(20)
            ->get(['id', 'request_no', 'status', 'total_qty', 'total_amount', 'created_at']);

        if (in_array($panel, ['order-form', 'saved-forms'], true) && $selectedSavedRequestId > 0) {
            $savedRequest = SavedPurchaseForm::query()
                ->where('user_id', $user->id)
                ->whereKey($selectedSavedRequestId)
                ->with(['items:id,saved_purchase_form_id,product_id,variant_id,qty'])
                ->first();

            if ($savedRequest) {
                $reorderSeedRows = $savedRequest->items
                    ->groupBy(function ($item) {
                        return ((int) $item->product_id) . '|' . ((int) ($item->variant_id ?? 0));
                    })
                    ->map(function ($items) {
                        $first = $items->first();
                        return [
                            'product_id' => (int) $first->product_id,
                            'variant_id' => $first->variant_id ? (int) $first->variant_id : null,
                            'qty' => (int) $items->sum('qty'),
                        ];
                    })
                    ->values()
                    ->all();
            }
        }

        $reorderOrderId = (int) $request->query('reorder', 0);
        if ($panel === 'order-form' && $selectedSavedRequestId <= 0 && $reorderOrderId > 0) {
            $reorderOrder = Order::query()
                ->where('user_id', $user->id)
                ->whereKey($reorderOrderId)
                ->with('items')
                ->first();

            if ($reorderOrder) {
                $reorderSeedRows = $reorderOrder->items
                    ->groupBy(function ($item) {
                        return ((int) $item->product_id) . '|' . ((int) ($item->variant_id ?? 0));
                    })
                    ->map(function ($items) {
                        $first = $items->first();
                        return [
                            'product_id' => (int) $first->product_id,
                            'variant_id' => $first->variant_id ? (int) $first->variant_id : null,
                            'qty' => (int) $items->sum('quantity'),
                        ];
                    })
                    ->values()
                    ->all();
            }
        }

        $seedProductIds = collect($reorderSeedRows)
            ->pluck('product_id')
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $missingProductIds = $seedProductIds->diff(
            $productsForOrderForm->pluck('id')->map(fn ($id) => (int) $id)->values()
        );

        if ($missingProductIds->isNotEmpty()) {
            $missingProducts = Product::query()
                ->whereIn('id', $missingProductIds->all())
                ->with(['variants:id,product_id,name,color,size,price,outlet_price'])
                ->get(['id', 'name', 'price', 'outlet_price', 'minimum_order_qty'])
                ->map($mapOrderFormProduct)
                ->values();

            $productsForOrderForm = $productsForOrderForm
                ->concat($missingProducts)
                ->unique('id')
                ->sortBy('name')
                ->values();
        }

        return view('frontend.pages.account.index', compact(
            'user',
            'panel',
            'orders',
            'recentOrders',
            'productsForOrderForm',
            'reorderSeedRows',
            'savedPurchaseForms',
            'selectedSavedRequestId'
        ));
    }

    public function addOrderFormToCart(Request $request)
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
        ]);

        $items = collect($validated['items'])
            ->map(function ($item) {
                return [
                    'product_id' => (int) $item['product_id'],
                    'variant_id' => isset($item['variant_id']) && $item['variant_id'] !== '' ? (int) $item['variant_id'] : null,
                    'qty' => max(1, (int) $item['qty']),
                ];
            })
            ->values();

        if ($items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No valid items to add.',
            ], 422);
        }

        $productIds = $items->pluck('product_id')->unique()->values();
        $variantIds = $items->pluck('variant_id')->filter()->unique()->values();

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->get(['id', 'vendor_id'])
            ->keyBy('id');

        $productsWithVariants = ProductVariant::query()
            ->whereIn('product_id', $productIds)
            ->pluck('product_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->flip();

        $variants = ProductVariant::query()
            ->whereIn('id', $variantIds)
            ->get(['id', 'product_id'])
            ->keyBy('id');

        foreach ($items as $item) {
            $product = $products->get($item['product_id']);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid product selected in order form.',
                ], 422);
            }

            $variantId = $item['variant_id'];
            if ($variantId === null && $productsWithVariants->has((int) $item['product_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Variant required for selected product.',
                ], 422);
            }

            if ($variantId !== null) {
                $variant = $variants->get($variantId);
                if (!$variant || (int) $variant->product_id !== (int) $item['product_id']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid variant for selected product.',
                    ], 422);
                }
            }
        }

        $addedRows = 0;
        DB::transaction(function () use ($items, $products, &$addedRows) {
            foreach ($items as $item) {
                $productId = $item['product_id'];
                $variantId = $item['variant_id'];
                $qty = $item['qty'];
                $product = $products->get($productId);

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
                    $cartItem->quantity = ((int) $cartItem->quantity) + $qty;
                    $cartItem->save();
                } else {
                    Cart::create([
                        'user_id' => Auth::id(),
                        'product_id' => $productId,
                        'variant_id' => $variantId,
                        'cart_type' => 'frontend',
                        'vendor_id' => $product->vendor_id ?? null,
                        'quantity' => $qty,
                    ]);
                }

                $addedRows++;
            }
        });

        $count = (int) Cart::where('user_id', Auth::id())
            ->where('cart_type', 'frontend')
            ->sum('quantity');

        return response()->json([
            'success' => true,
            'message' => $addedRows . ' row(s) added to cart.',
            'count' => $count,
        ]);
    }

    public function saveOrderForm(Request $request)
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $rows = collect($validated['items'])
            ->map(function ($item) {
                return [
                    'product_id' => (int) $item['product_id'],
                    'variant_id' => isset($item['variant_id']) && $item['variant_id'] !== '' ? (int) $item['variant_id'] : null,
                    'qty' => max(1, (int) $item['qty']),
                ];
            })
            ->groupBy(function ($row) {
                return $row['product_id'] . '|' . ($row['variant_id'] ?? 0);
            })
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'product_id' => $first['product_id'],
                    'variant_id' => $first['variant_id'],
                    'qty' => (int) $group->sum('qty'),
                ];
            })
            ->values();

        if ($rows->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No valid rows to save.',
            ], 422);
        }

        $productIds = $rows->pluck('product_id')->unique()->values();
        $variantIds = $rows->pluck('variant_id')->filter()->unique()->values();

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->get(['id', 'price', 'outlet_price'])
            ->keyBy('id');

        $productsWithVariants = ProductVariant::query()
            ->whereIn('product_id', $productIds)
            ->pluck('product_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->flip();

        $variants = ProductVariant::query()
            ->whereIn('id', $variantIds)
            ->get(['id', 'product_id', 'price', 'outlet_price'])
            ->keyBy('id');

        foreach ($rows as $row) {
            $product = $products->get($row['product_id']);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid product selected in order form.',
                ], 422);
            }

            if ($row['variant_id'] === null && $productsWithVariants->has((int) $row['product_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Variant required for selected product.',
                ], 422);
            }

            if ($row['variant_id'] !== null) {
                $variant = $variants->get($row['variant_id']);
                if (!$variant || (int) $variant->product_id !== (int) $row['product_id']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid variant for selected product.',
                    ], 422);
                }
            }
        }

        $user = Auth::user();
        $totalQty = 0;
        $totalAmount = 0.0;

        $requestModel = DB::transaction(function () use ($rows, $products, $variants, $user, $validated, &$totalQty, &$totalAmount) {
            $requestModel = SavedPurchaseForm::create([
                'request_no' => $this->generateProductRequestNo($user),
                'user_id' => (int) $user->id,
                'status' => 'saved',
                'total_qty' => 0,
                'total_amount' => 0,
                'note' => $validated['note'] ?? null,
            ]);

            foreach ($rows as $row) {
                $product = $products->get($row['product_id']);
                $variant = $row['variant_id'] !== null ? $variants->get($row['variant_id']) : null;

                $unitPrice = $this->resolveOrderFormUnitPrice($product, $variant, $user);
                $subtotal = round(((int) $row['qty']) * $unitPrice, 2);

                SavedPurchaseFormItem::create([
                    'saved_purchase_form_id' => $requestModel->id,
                    'product_id' => (int) $row['product_id'],
                    'variant_id' => $row['variant_id'],
                    'qty' => (int) $row['qty'],
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                $totalQty += (int) $row['qty'];
                $totalAmount += $subtotal;
            }

            $requestModel->update([
                'total_qty' => (int) $totalQty,
                'total_amount' => round($totalAmount, 2),
            ]);

            return $requestModel;
        });

        return response()->json([
            'success' => true,
            'message' => 'Purchase form saved successfully.',
            'request_id' => (int) $requestModel->id,
            'request_no' => (string) $requestModel->request_no,
        ]);
    }

    public function checkoutSavedForm(Request $request, int $savedRequest)
    {
        $userId = (int) Auth::id();
        $saved = SavedPurchaseForm::query()
            ->where('user_id', $userId)
            ->whereKey($savedRequest)
            ->with(['items:id,saved_purchase_form_id,product_id,variant_id,qty'])
            ->first();

        if (!$saved) {
            return redirect()
                ->route('account.index', ['panel' => 'saved-forms'])
                ->with('error_profile', 'Saved form not found.');
        }

        $rows = $saved->items
            ->map(function ($item) {
                return [
                    'product_id' => (int) $item->product_id,
                    'variant_id' => $item->variant_id ? (int) $item->variant_id : null,
                    'qty' => max(1, (int) $item->qty),
                ];
            })
            ->groupBy(function ($row) {
                return $row['product_id'] . '|' . ($row['variant_id'] ?? 0);
            })
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'product_id' => (int) $first['product_id'],
                    'variant_id' => $first['variant_id'],
                    'qty' => (int) $group->sum('qty'),
                ];
            })
            ->values();

        if ($rows->isEmpty()) {
            return redirect()
                ->route('account.index', ['panel' => 'saved-forms'])
                ->with('error_profile', 'Saved form has no items.');
        }

        $productIds = $rows->pluck('product_id')->unique()->values();
        $variantIds = $rows->pluck('variant_id')->filter()->unique()->values();

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->get(['id', 'vendor_id'])
            ->keyBy('id');

        $variants = ProductVariant::query()
            ->whereIn('id', $variantIds)
            ->get(['id', 'product_id'])
            ->keyBy('id');

        foreach ($rows as $row) {
            $product = $products->get($row['product_id']);
            if (!$product) {
                return redirect()
                    ->route('account.index', ['panel' => 'saved-forms'])
                    ->with('error_profile', 'A product from this form is no longer available.');
            }

            if ($row['variant_id'] !== null) {
                $variant = $variants->get($row['variant_id']);
                if (!$variant || (int) $variant->product_id !== (int) $row['product_id']) {
                    return redirect()
                        ->route('account.index', ['panel' => 'saved-forms'])
                        ->with('error_profile', 'A variant from this form is no longer valid.');
                }
            }
        }

        DB::transaction(function () use ($userId, $rows, $products) {
            Cart::query()
                ->where('user_id', $userId)
                ->where('cart_type', 'frontend')
                ->delete();

            foreach ($rows as $row) {
                $product = $products->get($row['product_id']);

                Cart::create([
                    'user_id' => $userId,
                    'product_id' => (int) $row['product_id'],
                    'variant_id' => $row['variant_id'],
                    'cart_type' => 'frontend',
                    'vendor_id' => $product->vendor_id ?? null,
                    'quantity' => (int) $row['qty'],
                ]);
            }
        });

        return redirect()->route('checkout.index', [
            'saved_form' => (int) $saved->id,
        ]);
    }

    public function deleteSavedForm(Request $request, int $savedRequest)
    {
        $deleted = SavedPurchaseForm::query()
            ->where('user_id', (int) Auth::id())
            ->whereKey($savedRequest)
            ->delete();

        if (!$deleted) {
            return redirect()
                ->route('account.index', ['panel' => 'saved-forms'])
                ->with('error_profile', 'Saved form not found.');
        }

        return redirect()
            ->route('account.index', ['panel' => 'saved-forms'])
            ->with('success_profile', 'Saved form deleted successfully.');
    }

    /**
     * Update frontend account profile information.
     */
    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validateWithBag('profileUpdate', [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'image' => ['nullable', 'mimetypes:image/jpeg,image/png,image/gif,image/webp', 'max:2048'],
            'phone' => ['nullable', 'string', 'max:30'],
            'outlet_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($request->hasFile('image')) {
            if ($user->image && File::exists(public_path($user->image))) {
                File::delete(public_path($user->image));
            }

            $image = $request->file('image');
            $imageName = rand() . '_' . $image->getClientOriginalName();
            $image->storeAs('uploads', $imageName, 'public');
            $user->image = '/storage/uploads/' . $imageName;
        }

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'outlet_name' => $validated['outlet_name'] ?? null,
            'address' => $validated['address'] ?? null,
        ]);
        $user->save();

        return redirect()
            ->route('account.index', ['panel' => 'profile'])
            ->with('success_profile', 'Account information updated successfully.');
    }

    /**
     * Update frontend account password.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validateWithBag('passwordUpdate', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('account.index', ['panel' => 'profile'])
            ->with('success_password', 'Password updated successfully.');
    }

    private function resolveOrderFormUnitPrice(Product $product, ?ProductVariant $variant, $user): float
    {
        $isOutletRole = $user && ($user->hasRole('Outlet User') || $user->hasRole('User'));
        if ($isOutletRole) {
            return (float) ($variant ? ($variant->outlet_price ?: $variant->price ?: $product->outlet_price ?: $product->price) : ($product->outlet_price ?: $product->price));
        }

        return (float) ($variant ? ($variant->price ?: $product->price) : $product->price);
    }

    private function generateProductRequestNo($user): string
    {
        $isOutletRole = $user && ($user->hasRole('Outlet User') || $user->hasRole('User'));
        $prefix = $isOutletRole ? 'DS-REQ-' : 'REQ-';

        do {
            $requestNo = $prefix . strtoupper(Str::random(10));
        } while (SavedPurchaseForm::query()->where('request_no', $requestNo)->exists());

        return $requestNo;
    }
}
