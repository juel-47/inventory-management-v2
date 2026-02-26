<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
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
        $productsForOrderForm = Product::query()
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'outlet_price', 'minimum_order_qty'])
            ->map(function ($product) use ($isOutletRole) {
                $price = $isOutletRole
                    ? (float) ($product->outlet_price ?: $product->price)
                    : (float) $product->price;

                return [
                    'id' => (int) $product->id,
                    'name' => (string) $product->name,
                    'price' => $price,
                    'minimum_order_qty' => max(1, (int) ($product->minimum_order_qty ?? 1)),
                ];
            })
            ->values();

        $reorderSeedRows = [];
        $reorderOrderId = (int) $request->query('reorder', 0);
        if ($panel === 'order-form' && $reorderOrderId > 0) {
            $reorderOrder = Order::query()
                ->where('user_id', $user->id)
                ->whereKey($reorderOrderId)
                ->with('items')
                ->first();

            if ($reorderOrder) {
                $reorderSeedRows = $reorderOrder->items
                    ->groupBy('product_id')
                    ->map(function ($items, $productId) {
                        return [
                            'product_id' => (int) $productId,
                            'qty' => (int) $items->sum('quantity'),
                        ];
                    })
                    ->values()
                    ->all();
            }
        }

        return view('frontend.pages.account.index', compact(
            'user',
            'panel',
            'orders',
            'recentOrders',
            'productsForOrderForm',
            'reorderSeedRows'
        ));
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
}
