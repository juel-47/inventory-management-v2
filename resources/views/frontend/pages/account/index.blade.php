@extends('layouts.frontend')
@section('title', 'My Account')

@section('content')
@php
    $currentPanel = $panel ?? 'dashboard';
    $currency = $settings->currency_icon ?? '$';
    $heroTitle = $currentPanel === 'orders' ? 'Orders' : 'MY ACCOUNT';
@endphp

<section class="relative border-b border-slate-200 bg-gradient-to-b from-slate-100 to-slate-50 overflow-hidden">
    <div class="absolute inset-0 opacity-40" style="background-image: radial-gradient(circle at 12% 25%, #d1d5db 0, transparent 35%), radial-gradient(circle at 88% 20%, #e5e7eb 0, transparent 35%), radial-gradient(circle at 80% 82%, #d1d5db 0, transparent 30%);"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 relative text-center">
        <h1 class="text-4xl font-black tracking-tight text-slate-900 uppercase">{{ $heroTitle }}</h1>
        <p class="text-sm text-slate-500 mt-2">Home @if($currentPanel !== 'dashboard') > MY ACCOUNT @endif</p>
    </div>
</section>

<section class="bg-slate-50 py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <aside class="lg:col-span-4 xl:col-span-3 space-y-4">
                <div class="bg-white border border-slate-200 rounded-sm p-4">
                    <div class="flex items-center gap-3">
                        @if(!empty($user->image))
                            <img src="{{ asset($user->image) }}" alt="{{ $user->name }}" class="w-14 h-14 rounded-sm object-cover border border-slate-200">
                        @else
                            <div class="w-14 h-14 rounded-sm bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M5.121 17.804A9 9 0 1118.879 17.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                        @endif
                        <div class="min-w-0">
                            <p class="text-xl font-black text-slate-900 truncate uppercase">{{ $user->name }}</p>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-[11px] uppercase tracking-widest font-bold text-slate-500 hover:text-rose-600">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-slate-200 rounded-sm overflow-hidden">
                    @php
                        $menuBase = 'flex items-center px-4 py-3 border-b border-slate-200 text-sm uppercase tracking-[0.12em] font-bold transition-colors';
                        $menuActive = 'bg-slate-100 text-slate-900';
                        $menuIdle = 'text-slate-500 hover:bg-slate-50';
                    @endphp
                    <a href="{{ route('account.index', ['panel' => 'dashboard']) }}" class="{{ $menuBase }} {{ $currentPanel === 'dashboard' ? $menuActive : $menuIdle }}">Control Panel</a>
                    <a href="{{ route('account.index', ['panel' => 'orders']) }}" class="{{ $menuBase }} {{ $currentPanel === 'orders' ? $menuActive : $menuIdle }}">Orders</a>
                    {{-- <a href="{{ route('account.index', ['panel' => 'downloads']) }}" class="{{ $menuBase }} {{ $currentPanel === 'downloads' ? $menuActive : $menuIdle }}">Downloads</a> --}}
                    <a href="{{ route('account.index', ['panel' => 'addresses']) }}" class="{{ $menuBase }} {{ $currentPanel === 'addresses' ? $menuActive : $menuIdle }}">Addresses</a>
                    <a href="{{ route('account.index', ['panel' => 'order-form']) }}" class="{{ $menuBase }} {{ $currentPanel === 'order-form' ? $menuActive : $menuIdle }}">Order Form</a>
                    {{-- <a href="{{ route('wishlist.index') }}" class="{{ $menuBase }} {{ $menuIdle }}">Shopping List</a> --}}
                    <a href="{{ route('account.index', ['panel' => 'profile']) }}" class="{{ $menuBase }} {{ $currentPanel === 'profile' ? $menuActive : $menuIdle }}">Account Information</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-3 text-sm uppercase tracking-[0.12em] font-bold text-slate-500 hover:bg-slate-50">Log Out</button>
                    </form>
                </div>

                @if($recentOrders->isNotEmpty())
                    <div class="bg-white border border-slate-200 rounded-sm p-3">
                        <p class="text-[11px] uppercase tracking-[0.14em] font-black text-slate-400 mb-2">Recent Orders</p>
                        <div class="space-y-2 max-h-64 overflow-auto pr-1">
                            @foreach($recentOrders as $rOrder)
                                <a href="{{ route('account.index', ['panel' => 'orders', 'page' => 1]) }}" class="block border border-slate-100 rounded-sm px-2 py-2 hover:bg-slate-50">
                                    <p class="text-xs font-black text-slate-800">{{ $rOrder->order_no }}</p>
                                    <p class="text-[11px] text-slate-500">{{ $rOrder->created_at?->format('d M Y') }}</p>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>

            <div class="lg:col-span-8 xl:col-span-9">
                @if($currentPanel === 'orders')
                    <div class="bg-white border border-slate-200 rounded-sm p-4 md:p-6">
                        <h2 class="text-4xl font-light text-slate-900 mb-4 uppercase tracking-wide">Orders</h2>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[780px]">
                                <thead>
                                    <tr class="border-b border-slate-200 text-left text-sm uppercase tracking-[0.12em] text-slate-600">
                                        <th class="py-3 pr-4 font-black">Order</th>
                                        <th class="py-3 px-4 font-black">Date</th>
                                        <th class="py-3 px-4 font-black">Status</th>
                                        <th class="py-3 px-4 font-black">Total</th>
                                        <th class="py-3 pl-4 font-black">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($orders as $order)
                                        <tr class="border-b border-slate-100">
                                            <td class="py-3 pr-4 text-sm font-semibold text-slate-800">#{{ $order->order_no }}</td>
                                            <td class="py-3 px-4 text-sm text-slate-700">{{ $order->created_at?->format('F j, Y') }}</td>
                                            <td class="py-3 px-4">
                                                @php $status = strtolower($order->status); @endphp
                                                <span class="text-xs font-black px-2 py-1 rounded
                                                    {{ $status === 'completed' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                                    {{ $status === 'approved' ? 'bg-sky-100 text-sky-700' : '' }}
                                                    {{ $status === 'cancelled' ? 'bg-rose-100 text-rose-700' : '' }}
                                                    {{ $status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-4 text-sm text-slate-800">
                                                <span class="font-semibold">{{ $currency }}{{ number_format($order->total_amount, 2) }}</span>
                                                <span class="text-slate-500"> for {{ (int) ($order->total_units ?? 0) }} units</span>
                                            </td>
                                            <td class="py-3 pl-4 text-[11px] uppercase tracking-[0.12em] font-black">
                                                <a href="{{ route('orders.show', $order->id) }}" class="text-slate-700 hover:text-indigo-600">View</a>
                                                <span class="text-slate-300 mx-1">|</span>
                                                <form method="POST" action="{{ route('orders.reorder', $order->id) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-slate-700 hover:text-indigo-600">Reorder</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-8 text-center text-sm text-slate-500">No orders found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($orders->hasPages())
                            @php
                                $start = max(1, $orders->currentPage() - 1);
                                $end = min($orders->lastPage(), $orders->currentPage() + 1);
                            @endphp
                            <div class="mt-6 flex items-center justify-end gap-2">
                                @if(!$orders->onFirstPage())
                                    <a href="{{ $orders->previousPageUrl() }}" class="px-3 py-2 border border-slate-300 rounded-sm text-xs font-bold uppercase tracking-[0.12em] text-slate-700 hover:bg-slate-100">Prev</a>
                                @endif

                                @foreach(range($start, $end) as $pageNo)
                                    @if($pageNo === $orders->currentPage())
                                        <span class="px-3 py-2 border border-slate-900 bg-slate-900 rounded-sm text-xs font-black uppercase tracking-[0.12em] text-white">{{ $pageNo }}</span>
                                    @else
                                        <a href="{{ $orders->url($pageNo) }}" class="px-3 py-2 border border-slate-300 rounded-sm text-xs font-bold uppercase tracking-[0.12em] text-slate-700 hover:bg-slate-100">{{ $pageNo }}</a>
                                    @endif
                                @endforeach

                                @if($orders->hasMorePages())
                                    <a href="{{ $orders->nextPageUrl() }}" class="px-5 py-2 border border-rose-700 bg-rose-700 rounded-sm text-xs font-black uppercase tracking-[0.14em] text-white hover:bg-rose-800">Next</a>
                                @endif
                            </div>
                        @endif
                    </div>
                @elseif($currentPanel === 'order-form')
                    <div class="bg-white border border-slate-200 rounded-sm p-4 md:p-6" x-data="orderFormPanel(@js($productsForOrderForm), @js($reorderSeedRows), @js($currency))">
                        <h2 class="text-4xl font-light text-slate-900 mb-3 uppercase tracking-wide">Order Form</h2>

                        <div class="border border-slate-300 rounded-sm overflow-hidden">
                            <div class="bg-slate-500 text-white text-sm px-3 py-2 font-bold">Order form</div>
                            <div class="p-3 bg-slate-100/70">
                                <div class="grid grid-cols-12 gap-3 text-xs uppercase tracking-[0.12em] font-black text-slate-600 mb-2">
                                    <div class="col-span-8">Search/Product</div>
                                    <div class="col-span-2 text-center">Number</div>
                                    <div class="col-span-2 text-right">Total</div>
                                </div>

                                <div class="space-y-2">
                                    <template x-for="(row, idx) in rows" :key="idx">
                                        <div class="grid grid-cols-12 gap-3 items-center">
                                            <div class="col-span-8">
                                                <select x-model.number="row.product_id" @change="syncRow(idx)" class="w-full border border-slate-300 rounded-sm bg-white px-3 py-2 text-sm text-slate-800">
                                                    <option value="">Search for a product</option>
                                                    <template x-for="p in products" :key="p.id">
                                                        <option :value="p.id" x-text="p.name"></option>
                                                    </template>
                                                </select>
                                            </div>
                                            <div class="col-span-2">
                                                <input type="number" min="0" x-model.number="row.qty" @input="syncRow(idx)" class="w-full border border-slate-300 rounded-sm bg-white px-2 py-2 text-sm text-center text-slate-800">
                                            </div>
                                            <div class="col-span-2 text-right text-sm font-semibold text-slate-700" x-text="formatMoney(row.line_total)"></div>
                                        </div>
                                    </template>
                                </div>

                                <button type="button" @click="addRow()" class="mt-3 px-3 py-1.5 bg-slate-500 text-white text-xs uppercase tracking-[0.12em] font-bold rounded-sm hover:bg-slate-600">+Add Product</button>

                                <div class="mt-4 pt-3 border-t border-slate-300 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="addAllToCart()" class="px-4 py-2 bg-rose-700 text-white text-sm font-black uppercase tracking-[0.12em] rounded-sm hover:bg-rose-800">Add To Cart</button>
                                        <button type="button" class="px-4 py-2 bg-slate-300 text-white text-sm font-black uppercase tracking-[0.12em] rounded-sm cursor-not-allowed">Save Purchase Form</button>
                                    </div>
                                    <p class="text-2xl font-light text-slate-900">Total: <span class="font-black" x-text="formatMoney(grandTotal)"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($currentPanel === 'profile')
                    <div class="space-y-5">
                        <div class="bg-white border border-slate-200 rounded-sm p-5">
                            <h2 class="text-3xl font-light text-slate-900 mb-1 uppercase tracking-wide">Account Information</h2>
                            <p class="text-sm text-slate-500 mb-5">Manage your profile details and keep contact information updated.</p>

                            <form method="POST" action="{{ route('account.profile.update') }}" enctype="multipart/form-data" class="space-y-4">
                                @csrf
                                <div>
                                    <label for="profile_image" class="block text-xs font-black uppercase tracking-[0.12em] text-slate-500 mb-1">Profile Image</label>
                                    <div class="flex items-center gap-3">
                                        @if(!empty($user->image))
                                            <img src="{{ asset($user->image) }}" alt="{{ $user->name }}" class="w-12 h-12 rounded-sm object-cover border border-slate-200">
                                        @endif
                                        <input id="profile_image" name="image" type="file" accept="image/jpeg,image/png,image/gif,image/webp" class="w-full border border-slate-300 rounded-sm px-3 py-2 text-sm text-slate-700 file:mr-3 file:px-3 file:py-1.5 file:border-0 file:bg-slate-200 file:text-slate-700 file:rounded-sm">
                                    </div>
                                    @error('image', 'profileUpdate')
                                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="profile_name" class="block text-xs font-black uppercase tracking-[0.12em] text-slate-500 mb-1">Name</label>
                                        <input id="profile_name" name="name" type="text" value="{{ old('name', $user->name) }}" class="w-full border border-slate-300 rounded-sm px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-200">
                                        @error('name', 'profileUpdate')
                                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="profile_email" class="block text-xs font-black uppercase tracking-[0.12em] text-slate-500 mb-1">Email</label>
                                        <input id="profile_email" name="email" type="email" value="{{ old('email', $user->email) }}" class="w-full border border-slate-300 rounded-sm px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-200">
                                        @error('email', 'profileUpdate')
                                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="profile_phone" class="block text-xs font-black uppercase tracking-[0.12em] text-slate-500 mb-1">Phone</label>
                                        <input id="profile_phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}" class="w-full border border-slate-300 rounded-sm px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-200">
                                        @error('phone', 'profileUpdate')
                                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="profile_outlet_name" class="block text-xs font-black uppercase tracking-[0.12em] text-slate-500 mb-1">Outlet / Shop</label>
                                        <input id="profile_outlet_name" name="outlet_name" type="text" value="{{ old('outlet_name', $user->outlet_name) }}" class="w-full border border-slate-300 rounded-sm px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-200">
                                        @error('outlet_name', 'profileUpdate')
                                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div>
                                    <label for="profile_address" class="block text-xs font-black uppercase tracking-[0.12em] text-slate-500 mb-1">Address</label>
                                    <textarea id="profile_address" name="address" rows="3" class="w-full border border-slate-300 rounded-sm px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-200">{{ old('address', $user->address) }}</textarea>
                                    @error('address', 'profileUpdate')
                                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="pt-2">
                                    <button type="submit" class="px-5 py-2 bg-slate-900 text-white text-xs font-black uppercase tracking-[0.12em] rounded-sm hover:bg-slate-800">Save Changes</button>
                                </div>
                            </form>
                        </div>

                        <div class="bg-white border border-slate-200 rounded-sm p-5">
                            <h3 class="text-2xl font-light text-slate-900 mb-1 uppercase tracking-wide">Change Password</h3>
                            <p class="text-sm text-slate-500 mb-5">Use a strong password with at least 8 characters.</p>

                            <form method="POST" action="{{ route('account.password.update') }}" class="space-y-4">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="md:col-span-2">
                                        <label for="current_password" class="block text-xs font-black uppercase tracking-[0.12em] text-slate-500 mb-1">Current Password</label>
                                        <input id="current_password" name="current_password" type="password" class="w-full border border-slate-300 rounded-sm px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-200">
                                        @error('current_password', 'passwordUpdate')
                                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="password" class="block text-xs font-black uppercase tracking-[0.12em] text-slate-500 mb-1">New Password</label>
                                        <input id="password" name="password" type="password" class="w-full border border-slate-300 rounded-sm px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-200">
                                        @error('password', 'passwordUpdate')
                                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="password_confirmation" class="block text-xs font-black uppercase tracking-[0.12em] text-slate-500 mb-1">Confirm Password</label>
                                        <input id="password_confirmation" name="password_confirmation" type="password" class="w-full border border-slate-300 rounded-sm px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-200">
                                    </div>
                                </div>

                                <div class="pt-2">
                                    <button type="submit" class="px-5 py-2 bg-rose-700 text-white text-xs font-black uppercase tracking-[0.12em] rounded-sm hover:bg-rose-800">Update Password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="bg-white border border-slate-200 rounded-sm p-5 mb-4">
                        <p class="text-lg text-slate-800">
                            Hello <span class="font-black">{{ $user->name }}</span>
                            <span class="text-sm text-slate-500">(if you are not {{ $user->name }}, please log out)</span>
                        </p>
                        <p class="text-sm text-slate-600 mt-3 leading-7">
                            In the "My Account" control panel, you can view your recent orders, manage shipping and billing address, and edit account information.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <a href="{{ route('account.index', ['panel' => 'orders']) }}" class="bg-white border border-slate-200 rounded-sm p-8 text-center hover:bg-slate-50">
                            <p class="text-xl font-light text-slate-900">Orders</p>
                        </a>
                        {{-- <a href="{{ route('account.index', ['panel' => 'downloads']) }}" class="bg-white border border-slate-200 rounded-sm p-8 text-center hover:bg-slate-50">
                            <p class="text-xl font-light text-slate-900">Downloads</p>
                        </a> --}}
                        <a href="{{ route('account.index', ['panel' => 'addresses']) }}" class="bg-white border border-slate-200 rounded-sm p-8 text-center hover:bg-slate-50">
                            <p class="text-xl font-light text-slate-900">Addresses</p>
                        </a>
                        <a href="{{ route('account.index', ['panel' => 'order-form']) }}" class="bg-white border border-slate-200 rounded-sm p-8 text-center hover:bg-slate-50">
                            <p class="text-xl font-light text-slate-900">Order form</p>
                        </a>
                        {{-- <a href="{{ route('wishlist.index') }}" class="bg-white border border-slate-200 rounded-sm p-8 text-center hover:bg-slate-50">
                            <p class="text-xl font-light text-slate-900">Shopping list</p>
                        </a> --}}
                        <a href="{{ route('account.index', ['panel' => 'profile']) }}" class="bg-white border border-slate-200 rounded-sm p-8 text-center hover:bg-slate-50">
                            <p class="text-xl font-light text-slate-900">Account information</p>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
@php
    $accountToasts = [];
    if (session('success_profile')) {
        $accountToasts[] = ['type' => 'success', 'message' => session('success_profile')];
    }
    if (session('success_password')) {
        $accountToasts[] = ['type' => 'success', 'message' => session('success_password')];
    }
    if ($errors->getBag('profileUpdate')->any()) {
        foreach ($errors->getBag('profileUpdate')->all() as $msg) {
            $accountToasts[] = ['type' => 'error', 'message' => $msg];
        }
    }
    if ($errors->getBag('passwordUpdate')->any()) {
        foreach ($errors->getBag('passwordUpdate')->all() as $msg) {
            $accountToasts[] = ['type' => 'error', 'message' => $msg];
        }
    }
@endphp
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const accountToasts = @json($accountToasts);
        if (!Array.isArray(accountToasts) || !accountToasts.length) return;

        const bodyEl = document.querySelector('[x-data*="globalApp"]');
        const globalApp = bodyEl?._x_dataStack?.[0];
        if (!globalApp?.notify) return;

        accountToasts.forEach((toast, idx) => {
            setTimeout(() => {
                globalApp.notify(toast.message, toast.type || 'success');
            }, 150 * (idx + 1));
        });
    });

    document.addEventListener('alpine:init', () => {
        Alpine.data('orderFormPanel', (products, initialRows = [], currency = '$') => ({
            products: products || [],
            rows: [],
            currency,

            init() {
                if (Array.isArray(initialRows) && initialRows.length > 0) {
                    this.rows = initialRows.map((r) => {
                        const pid = parseInt(r.product_id) || null;
                        const qty = Math.max(0, parseInt(r.qty) || 0);
                        return {
                            product_id: pid,
                            qty: qty,
                            unit_price: this.getProductPrice(pid),
                            line_total: 0,
                        };
                    });
                } else {
                    this.rows = Array.from({ length: 5 }, () => ({
                        product_id: null,
                        qty: 0,
                        unit_price: 0,
                        line_total: 0,
                    }));
                }
                this.rows.forEach((_, idx) => this.syncRow(idx));
            },

            get grandTotal() {
                return this.rows.reduce((sum, row) => sum + (parseFloat(row.line_total) || 0), 0);
            },

            getProductPrice(productId) {
                const product = this.products.find((p) => parseInt(p.id) === parseInt(productId));
                return product ? (parseFloat(product.price) || 0) : 0;
            },

            getMinimumQty(productId) {
                const product = this.products.find((p) => parseInt(p.id) === parseInt(productId));
                return product ? Math.max(1, parseInt(product.minimum_order_qty) || 1) : 1;
            },

            syncRow(index) {
                const row = this.rows[index];
                if (!row) return;

                if (!row.product_id) {
                    row.unit_price = 0;
                    row.line_total = 0;
                    return;
                }

                row.unit_price = this.getProductPrice(row.product_id);
                const minQty = this.getMinimumQty(row.product_id);
                const qty = Math.max(0, parseInt(row.qty) || 0);
                row.qty = qty > 0 && qty < minQty ? minQty : qty;
                row.line_total = (parseFloat(row.unit_price) || 0) * (parseInt(row.qty) || 0);
            },

            addRow() {
                this.rows.push({
                    product_id: null,
                    qty: 0,
                    unit_price: 0,
                    line_total: 0,
                });
            },

            formatMoney(value) {
                const amount = parseFloat(value || 0);
                return `${this.currency}${amount.toFixed(2)}`;
            },

            async addAllToCart() {
                const validRows = this.rows.filter((r) => r.product_id && (parseInt(r.qty) || 0) > 0);
                if (!validRows.length) {
                    this.notify('Please add at least one product with quantity.', 'warning');
                    return;
                }

                let successCount = 0;
                for (const row of validRows) {
                    try {
                        const res = await fetch('/frontend/cart/add', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content'),
                            },
                            body: JSON.stringify({
                                product_id: parseInt(row.product_id),
                                quantity: Math.max(1, parseInt(row.qty) || 1),
                            }),
                        });

                        if (res.ok) {
                            successCount++;
                        }
                    } catch (e) {}
                }

                if (successCount > 0) {
                    if (window.Alpine && Alpine.store('cart')) {
                        await Alpine.store('cart').loadFromDB();
                    }
                    this.notify(`${successCount} product(s) added to cart.`, 'success');
                } else {
                    this.notify('Failed to add products to cart.', 'error');
                }
            },

            notify(message, type = 'success') {
                const bodyEl = document.querySelector('[x-data*=\"globalApp\"]');
                if (bodyEl && bodyEl._x_dataStack && bodyEl._x_dataStack[0]?.notify) {
                    bodyEl._x_dataStack[0].notify(message, type);
                } else {
                    alert(message);
                }
            },
        }));
    });
</script>
@endsection
