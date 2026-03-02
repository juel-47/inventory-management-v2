@extends('layouts.frontend')

@section('content')
    @php
        $currencyIcon = optional($settings)->currency_icon ?? 'Tk';
        $siteName = optional($settings)->site_name ?? config('app.name', 'Inventory B2B');
        $roleContext = $roleContext ?? [];
        $isOutletUser = (bool) data_get($roleContext, 'isOutletUser', false);
        $isStandardUser = (bool) data_get($roleContext, 'isStandardUser', false);
        $canViewInventory = (bool) data_get($roleContext, 'canViewInventory', false);
        $displayPath = $displayPath ?? null;
        $productCategoryName = $productCategoryName ?? '';
        $productBrandName = $productBrandName ?? '';
        $productSku = $productSku ?? '';
        $productNumber = $productNumber ?? '';
        $summaryText = $summaryText ?? null;
        $hasLongDescription = (bool) ($hasLongDescription ?? false);
        $detailProductData = $detailProductData ?? [];
        $detailVariantData = collect($detailVariantData ?? []);
        $isWishlisted = (bool) ($isWishlisted ?? false);
        $relatedCards = collect($relatedCards ?? []);
    @endphp

    <div class="bg-slate-100 py-6 sm:py-8">
        <div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">
            <section class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm sm:px-5">
                <div class="flex flex-wrap items-center gap-2 text-[11px] font-semibold text-slate-500">
                    <a href="{{ route('home') }}" class="transition hover:text-emerald-700">Home</a>
                    <span>/</span>
                    <a href="{{ route('shop') }}" class="transition hover:text-emerald-700">Catalog</a>
                    <span>/</span>
                    <span class="line-clamp-1 text-slate-700">{{ $product->name }}</span>
                    <span class="ml-auto hidden text-[10px] uppercase tracking-[0.16em] text-slate-400 sm:inline">{{ $siteName }}</span>
                </div>
            </section>

            <section x-data="productDetail(@js($detailProductData), @js($detailVariantData), @js($isWishlisted))"
                class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="grid gap-5 p-4 sm:p-5 lg:grid-cols-12 lg:gap-6 lg:p-6">
                    <div class="space-y-3.5 lg:col-span-5">
                        <div class="overflow-hidden rounded-xl border border-slate-200 bg-slate-100">
                            @if ($displayPath)
                                <img src="{{ $displayPath }}" alt="{{ $product->name }}" class="aspect-square w-full object-cover">
                            @else
                                <div class="flex aspect-square items-center justify-center">
                                    <span class="rounded-full border border-slate-300 bg-white px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500">
                                        No image uploaded
                                    </span>
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.12em] text-slate-600">
                                {{ $productCategoryName !== '' ? $productCategoryName : 'Category not set' }}
                            </span>
                            @if ($detailVariantData->isNotEmpty())
                                <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.12em] text-emerald-700">
                                    {{ $detailVariantData->count() }} Variants
                                </span>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-[11px]">
                            <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5">
                                <p class="text-[9px] font-bold uppercase tracking-[0.16em] text-slate-500">MOQ</p>
                                <p class="mt-1 text-sm font-bold text-slate-900">{{ max(1, (int) ($product->minimum_order_qty ?? 1)) }}</p>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5">
                                <p class="text-[9px] font-bold uppercase tracking-[0.16em] text-slate-500">Stock</p>
                                @if ($canViewInventory)
                                    <p class="mt-1 text-sm font-bold text-slate-900" x-text="currentStock"></p>
                                @else
                                    <p class="mt-1 text-sm font-semibold text-slate-600">Login required</p>
                                @endif
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5">
                                <p class="text-[9px] font-bold uppercase tracking-[0.16em] text-slate-500">SKU</p>
                                <p class="mt-1 line-clamp-1 text-sm font-semibold text-slate-700">{{ $productSku !== '' ? $productSku : 'Not set' }}</p>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5">
                                <p class="text-[9px] font-bold uppercase tracking-[0.16em] text-slate-500">Product No</p>
                                <p class="mt-1 line-clamp-1 text-sm font-semibold text-slate-700">{{ $productNumber !== '' ? $productNumber : 'Not set' }}</p>
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-white p-3">
                            <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500">Description</p>
                            @if ($hasLongDescription)
                                <div class="prose prose-sm mt-2 max-h-52 max-w-none overflow-auto pr-1 text-slate-600">
                                    {!! $product->long_description !!}
                                </div>
                            @else
                                <p class="mt-2 text-sm text-slate-500">No description provided for this product.</p>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-4 lg:col-span-7">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-emerald-700">B2B Product Profile</p>
                            <h1 class="mt-1.5 text-2xl font-bold leading-tight text-slate-900 sm:text-[30px]">{{ $product->name }}</h1>
                            @if ($summaryText)
                                <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $summaryText }}</p>
                            @endif
                        </div>

                        <div class="grid gap-2 sm:grid-cols-3">
                            <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-[11px] text-slate-700">
                                <span class="font-bold text-slate-800">Category:</span>
                                {{ $productCategoryName !== '' ? $productCategoryName : 'Not set' }}
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-[11px] text-slate-700">
                                <span class="font-bold text-slate-800">Brand:</span>
                                {{ $productBrandName !== '' ? $productBrandName : 'Not set' }}
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-[11px] text-slate-700">
                                <span class="font-bold text-slate-800">Variant count:</span>
                                {{ $detailVariantData->count() }}
                            </div>
                        </div>

                        @auth
                            <div class="rounded-xl border border-slate-200 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 p-4 text-white">
                                @if ($isOutletUser)
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div class="rounded-lg border border-white/15 bg-white/10 px-3 py-2.5">
                                            <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-emerald-200">Wholesale Price</p>
                                            <p class="mt-1 text-2xl font-bold">{{ $currencyIcon }}<span x-text="outletDisplayPrice"></span></p>
                                        </div>
                                        <div class="rounded-lg border border-white/15 bg-white/10 px-3 py-2.5">
                                            <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-200">Selling Price</p>
                                            <p class="mt-1 text-xl font-bold">{{ $currencyIcon }}<span x-text="retailDisplayPrice"></span></p>
                                        </div>
                                    </div>
                                @elseif ($isStandardUser)
                                    <div class="rounded-lg border border-white/15 bg-white/10 px-3 py-2.5">
                                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-emerald-200">Wholesale Price</p>
                                        <p class="mt-1 text-2xl font-bold">{{ $currencyIcon }}<span x-text="outletDisplayPrice"></span></p>
                                    </div>
                                @else
                                    <div class="rounded-lg border border-white/15 bg-white/10 px-3 py-2.5">
                                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-200">Price</p>
                                        <p class="mt-1 text-2xl font-bold">{{ $currencyIcon }}<span x-text="retailDisplayPrice"></span></p>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-amber-700">Pricing Access</p>
                                <p class="mt-1.5 text-sm text-slate-700">Login as customer account to see wholesale rate and place an order.</p>
                                <a href="{{ route('login') }}"
                                    class="mt-3 inline-flex h-9 items-center justify-center rounded-md bg-slate-900 px-3 text-[11px] font-bold uppercase tracking-[0.12em] text-white transition hover:bg-slate-800">
                                    Login Now
                                </a>
                            </div>
                        @endauth

                        <template x-if="hasVariants">
                            <div class="rounded-xl border border-slate-200 bg-white p-3.5">
                                <div class="mb-2.5 flex items-center justify-between gap-2">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500">Select Variant</p>
                                    <template x-if="hasVariants && !selectedVariant">
                                        <span class="text-[11px] font-semibold text-amber-700">Required</span>
                                    </template>
                                </div>
                                <div class="flex flex-wrap gap-1.5">
                                    <template x-for="(v, index) in variants" :key="v.id">
                                        <button type="button"
                                            @click="selectVariant(index)"
                                            :disabled="!canSelectVariant(index)"
                                            :class="!canSelectVariant(index)
                                                ? 'border-slate-200 bg-slate-100 text-slate-300 cursor-not-allowed'
                                                : (selectedVariantIndex === String(index)
                                                    ? 'border-emerald-300 bg-emerald-50 text-emerald-700'
                                                    : 'border-slate-200 bg-white text-slate-600 hover:border-emerald-200 hover:text-emerald-700')"
                                            class="rounded-md border px-2.5 py-1 text-[11px] font-bold leading-none transition-colors">
                                            <span x-text="variantLabel(v)"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>

                        @auth
                            <div class="rounded-xl border border-slate-200 bg-slate-50/90 p-4">
                                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-[11px] font-bold"
                                        :class="stockPillClass" x-text="stockPillText"></span>
                                    <p class="text-[11px] font-semibold text-slate-500">
                                        MOQ: <span x-text="minimumOrderQty"></span>
                                    </p>
                                </div>

                                <div class="grid gap-2.5 sm:grid-cols-[160px_1fr_auto]">
                                    <div class="flex h-10 items-center rounded-md border border-slate-200 bg-white">
                                        <button type="button"
                                            @click="qty = Math.max(minimumOrderQty, (parseInt(qty) || minimumOrderQty) - minimumOrderQty); normalizeQty()"
                                            class="h-full w-9 text-slate-500 transition hover:text-slate-800">
                                            -
                                        </button>
                                        <input type="number"
                                            x-model.number="qty"
                                            :min="minimumOrderQty"
                                            :max="inventoryVisible && maxAddableQty > 0 ? maxAddableQty : minimumOrderQty"
                                            :step="minimumOrderQty"
                                            @change="normalizeQty()"
                                            class="h-full w-full border-x border-slate-200 bg-transparent p-0 text-center text-sm font-bold text-slate-900 focus:outline-none">
                                        <button type="button"
                                            @click="qty = (parseInt(qty) || minimumOrderQty) + minimumOrderQty; normalizeQty()"
                                            class="h-full w-9 text-slate-500 transition hover:text-slate-800">
                                            +
                                        </button>
                                    </div>

                                    <button @click="canAdd ? addToCart() : notify(cannotAddMessage, 'error')"
                                        :disabled="adding"
                                        :class="canAdd
                                            ? 'bg-slate-900 text-white hover:bg-emerald-700'
                                            : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                                        class="h-10 rounded-md px-4 text-[11px] font-bold uppercase tracking-[0.14em] transition">
                                        <span x-show="!adding">Add To Cart</span>
                                        <span x-show="adding">Adding...</span>
                                    </button>

                                    <button @click="toggleWishlist()"
                                        :disabled="wishlistBusy"
                                        class="h-10 w-10 rounded-md border-2 transition"
                                        :class="isInWishlist
                                            ? 'border-rose-300 bg-rose-50 text-rose-500 hover:bg-rose-100'
                                            : 'border-slate-200 bg-white text-slate-400 hover:border-rose-300 hover:text-rose-500'"
                                        :title="isInWishlist ? 'Remove from wishlist' : 'Add to wishlist'">
                                        <template x-if="isInWishlist">
                                            <svg class="mx-auto h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                                            </svg>
                                        </template>
                                        <template x-if="!isInWishlist">
                                            <svg class="mx-auto h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                            </svg>
                                        </template>
                                    </button>
                                </div>

                                <template x-if="inventoryVisible && (!hasVariants || selectedVariant) && maxAddableQty > 0">
                                    <p class="mt-2 text-[11px] font-semibold text-slate-500" x-text="`Max addable: ${maxAddableQty}`"></p>
                                </template>
                            </div>
                        @endauth

                        <div class="grid gap-2 sm:grid-cols-2">
                            <div class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-[11px] font-semibold text-slate-600">Verified product quality</div>
                            <div class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-[11px] font-semibold text-slate-600">Ready for outlet distribution</div>
                        </div>
                    </div>
                </div>
            </section>

            @if ($relatedCards->count() > 0)
                <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5 lg:p-6">
                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500">Same Category</p>
                            <h2 class="text-lg font-bold text-slate-900">Related Products</h2>
                        </div>
                        <a href="{{ route('shop', ['category' => $product->category_id]) }}"
                            class="hidden text-[11px] font-bold uppercase tracking-[0.12em] text-emerald-700 transition hover:text-emerald-600 md:inline-flex">
                            View All
                        </a>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2 md:grid-cols-3 lg:grid-cols-5">
                        @foreach ($relatedCards as $card)
                            <x-frontend.product-card
                                :product="$card['product']"
                                :variants="$card['variants']"
                                :display-path="$card['display_path']"
                                :category-name="$card['category_name']"
                                :currency-icon="$currencyIcon"
                                :is-outlet-user="$isOutletUser"
                                :is-standard-user="$isStandardUser"
                                :details-url="$card['details_url']"
                                class="rounded-xl border-slate-200 p-2.5" />
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    @include('frontend.partials.product-card-script')

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('productDetail', (product, variants, initiallyWishlisted = false) => ({
                qty: Math.max(1, parseInt(product.minimum_order_qty, 10) || 1),
                product,
                variants,
                selectedVariantIndex: '',
                adding: false,
                wishlistBusy: false,
                isInWishlist: !!initiallyWishlisted,

                init() {
                    if (!this.hasVariants || !this.inventoryVisible) {
                        return;
                    }

                    const firstAvailableIndex = this.variants.findIndex((variant) => (parseInt(variant.stock, 10) || 0) > 0);
                    this.selectedVariantIndex = firstAvailableIndex >= 0 ? String(firstAvailableIndex) : '';
                },

                get inventoryVisible() {
                    return !!this.product.inventory_visible;
                },

                get hasVariants() {
                    return this.variants.length > 0;
                },

                canSelectVariant(index) {
                    const variant = this.variants[index];
                    if (!variant) {
                        return false;
                    }

                    if (!this.inventoryVisible) {
                        return true;
                    }

                    return (parseInt(variant.stock, 10) || 0) > 0;
                },

                selectVariant(index) {
                    if (!this.canSelectVariant(index)) {
                        return;
                    }

                    this.selectedVariantIndex = String(index);
                    this.normalizeQty();
                },

                get selectedVariant() {
                    if (this.selectedVariantIndex === '') {
                        return null;
                    }

                    const index = parseInt(this.selectedVariantIndex, 10);
                    if (!this.canSelectVariant(index)) {
                        return null;
                    }

                    return this.variants[index] ?? null;
                },

                variantLabel(v) {
                    const base = v.name || [v.color, v.size].filter(Boolean).join(' ') || 'Variant';
                    if (!this.inventoryVisible) {
                        return base;
                    }

                    const stock = parseInt(v.stock, 10) || 0;
                    return stock > 0 ? `${base} - ${stock}` : `${base} - Out`;
                },

                get outletDisplayPrice() {
                    const price = this.selectedVariant
                        ? (this.selectedVariant.outlet_price || this.selectedVariant.price || this.product.outlet_price || this.product.price || 0)
                        : (this.product.outlet_price || this.product.price || 0);

                    return Number(price).toFixed(2);
                },

                get retailDisplayPrice() {
                    const price = this.selectedVariant
                        ? (this.selectedVariant.price || this.product.price || 0)
                        : (this.product.price || 0);

                    return Number(price).toFixed(2);
                },

                get minimumOrderQty() {
                    return Math.max(1, parseInt(this.product.minimum_order_qty, 10) || 1);
                },

                get currentStock() {
                    if (this.hasVariants) {
                        return this.selectedVariant ? Math.max(0, parseInt(this.selectedVariant.stock, 10) || 0) : 0;
                    }

                    return Math.max(0, parseInt(this.product.stock, 10) || 0);
                },

                get maxAddableQty() {
                    if (!this.inventoryVisible) {
                        return Number.MAX_SAFE_INTEGER;
                    }

                    const stock = this.currentStock;
                    const moq = this.minimumOrderQty;
                    if (stock < moq) {
                        return 0;
                    }

                    return Math.floor(stock / moq) * moq;
                },

                get normalizedQty() {
                    const inputQty = Math.max(1, parseInt(this.qty, 10) || 1);
                    const moq = this.minimumOrderQty;

                    if (inputQty < moq) {
                        return moq;
                    }

                    if (inputQty > moq) {
                        return Math.ceil(inputQty / moq) * moq;
                    }

                    return moq;
                },

                normalizeQty() {
                    let adjustedQty = this.normalizedQty;

                    if (this.inventoryVisible && this.maxAddableQty > 0 && adjustedQty > this.maxAddableQty) {
                        adjustedQty = this.maxAddableQty;
                    }

                    this.qty = adjustedQty;
                    return this.qty;
                },

                get canAdd() {
                    if (this.hasVariants && !this.selectedVariant) {
                        return false;
                    }

                    if (!this.inventoryVisible) {
                        return true;
                    }

                    const requestedQty = this.normalizedQty;
                    return this.currentStock > 0 && this.maxAddableQty > 0 && requestedQty <= this.currentStock;
                },

                get cannotAddMessage() {
                    if (this.hasVariants && !this.selectedVariant) {
                        return 'Please select a variant';
                    }

                    if (!this.inventoryVisible) {
                        return 'Cannot add this item right now';
                    }

                    if (this.currentStock <= 0) {
                        return 'Out of stock';
                    }

                    if (this.maxAddableQty === 0) {
                        return `Minimum order is ${this.minimumOrderQty}, but stock is ${this.currentStock}`;
                    }

                    return `Available stock: ${this.currentStock}`;
                },

                get stockPillText() {
                    if (!this.inventoryVisible) {
                        return 'Available';
                    }

                    if (this.hasVariants && !this.selectedVariant) {
                        return 'Select variant';
                    }

                    if (this.currentStock <= 0) {
                        return 'Out of stock';
                    }

                    if (this.currentStock <= 5) {
                        return `Low stock: ${this.currentStock}`;
                    }

                    return `In stock: ${this.currentStock}`;
                },

                get stockPillClass() {
                    if (!this.inventoryVisible) {
                        return 'border-slate-200 bg-slate-50 text-slate-600';
                    }

                    if (this.hasVariants && !this.selectedVariant) {
                        return 'border-slate-200 bg-slate-100 text-slate-500';
                    }

                    if (this.currentStock <= 0) {
                        return 'border-rose-200 bg-rose-50 text-rose-600';
                    }

                    if (this.currentStock <= 5) {
                        return 'border-amber-200 bg-amber-50 text-amber-700';
                    }

                    return 'border-emerald-200 bg-emerald-50 text-emerald-700';
                },

                async addToCart() {
                    if (this.adding || !this.canAdd) {
                        return;
                    }

                    this.adding = true;
                    try {
                        const finalQty = this.normalizeQty();
                        await Alpine.store('cart').addItem(this.product, this.selectedVariant, finalQty);

                        const bodyEl = document.querySelector('[x-data*="globalApp"]');
                        if (bodyEl?._x_dataStack?.[0]) {
                            bodyEl._x_dataStack[0].notify('Added to cart ✓', 'success');
                            bodyEl._x_dataStack[0].isCartOpen = true;
                        }
                    } catch (e) {
                        console.error('Add to cart error:', e);
                        this.notify(e?.message || 'Error adding to cart', 'error');
                    } finally {
                        this.adding = false;
                    }
                },

                async toggleWishlist() {
                    if (this.wishlistBusy) {
                        return;
                    }

                    this.wishlistBusy = true;
                    try {
                        const result = await Alpine.store('wishlist').toggle(this.product.id);
                        this.isInWishlist = Alpine.store('wishlist').isWishlisted(this.product.id);

                        const fallback = this.isInWishlist ? 'Added to wishlist' : 'Removed from wishlist';
                        this.notify(result?.message || fallback, this.isInWishlist ? 'success' : 'warning');
                    } catch (e) {
                        console.error('Wishlist toggle error:', e);
                        this.notify('Error updating wishlist', 'error');
                    } finally {
                        this.wishlistBusy = false;
                    }
                },

                notify(message, type = 'info') {
                    const bodyEl = document.querySelector('[x-data*="globalApp"]');
                    if (bodyEl?._x_dataStack?.[0]) {
                        bodyEl._x_dataStack[0].notify(message, type);
                    }
                },
            }));

        });
    </script>
@endsection
