@props([
    'product' => [],
    'variants' => [],
    'displayPath' => null,
    'categoryName' => 'General',
    'currencyIcon' => 'Tk',
    'isOutletUser' => false,
    'isStandardUser' => false,
    'alpineComponent' => 'productCardItem',
    'detailsUrl' => null,
])

@php
    $productName = (string) ($product['name'] ?? 'Product');
    $resolvedCategoryName = trim((string) $categoryName) !== ''
        ? (string) $categoryName
        : (string) ($product['category'] ?? 'General');

    $resolvedDisplayPath = is_string($displayPath) && $displayPath !== ''
        ? $displayPath
        : (string) ($product['thumb_image'] ?? '');

    $resolvedDetailsUrl = is_string($detailsUrl) && $detailsUrl !== ''
        ? $detailsUrl
        : '#';
@endphp

<article x-data="{{ $alpineComponent }}(@js($product), @js($variants))"
    {{ $attributes->merge(['class' => 'group relative flex flex-col rounded-3xl border border-slate-100 bg-white p-4 transition-all duration-300 hover:shadow-xl']) }}>
    <div class="relative mb-4 aspect-square overflow-hidden rounded-2xl bg-slate-50">
        @if ($resolvedDisplayPath !== '')
            <img src="{{ $resolvedDisplayPath }}" alt="{{ $productName }}"
                class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105">
        @else
            <div class="flex h-full w-full items-center justify-center text-[10px] font-bold uppercase tracking-wider text-slate-400">
                No image uploaded
            </div>
        @endif

        <div class="absolute left-3 top-3 flex flex-col gap-1">
            <span class="rounded-lg bg-white/90 px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-600 shadow-sm backdrop-blur">
                {{ $resolvedCategoryName }}
            </span>
            <template x-if="hasDiscount">
                <span class="rounded-lg bg-emerald-600/95 px-2 py-1 text-[10px] font-black uppercase tracking-wider text-white shadow-sm">
                    <span x-text="discountBadgeText"></span>
                </span>
            </template>
        </div>

        <div class="absolute right-3 top-3">
            @auth
                <button @click="toggleWishlist(product.id)"
                    :class="isWishlisted(product.id)
                        ? 'border-rose-300 bg-rose-50 text-rose-500 hover:bg-rose-100'
                        : 'border-slate-200 bg-white/90 text-slate-400 hover:border-rose-300 hover:text-rose-400'"
                    class="flex h-10 w-10 items-center justify-center rounded-xl border-2 backdrop-blur transition-all active:scale-95"
                    :title="isWishlisted(product.id) ? 'Remove from Wishlist' : 'Add to Wishlist'">
                    <template x-if="isWishlisted(product.id)">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                        </svg>
                    </template>
                    <template x-if="!isWishlisted(product.id)">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </template>
                </button>
            @else
                <a href="{{ route('login') }}"
                    class="flex h-10 w-10 items-center justify-center rounded-xl border-2 border-slate-200 bg-white/90 text-slate-400 backdrop-blur transition-all hover:border-indigo-300 hover:text-indigo-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </a>
            @endauth
        </div>
    </div>

    <div class="flex flex-1 flex-col px-1">
        <h3 class="mb-3 line-clamp-2 text-sm font-bold leading-tight text-slate-900">
            <a href="{{ $resolvedDetailsUrl }}" class="transition-colors hover:text-indigo-600">{{ $productName }}</a>
        </h3>

        <template x-if="hasVariants">
            <div class="mb-4">
                <div class="flex flex-wrap gap-1.5">
                    <template x-for="(v, index) in variants" :key="v.id">
                        <button type="button"
                            @click="selectVariant(index)"
                            :disabled="!canSelectVariant(index)"
                            :class="!canSelectVariant(index)
                                ? 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-300'
                                : (selectedVariantIndex === String(index)
                                    ? 'border-indigo-300 bg-indigo-50 text-indigo-700'
                                    : 'border-slate-200 bg-white text-slate-600 hover:border-indigo-200 hover:text-indigo-600')"
                            class="rounded-lg border px-2 py-1 text-[10px] font-bold leading-none transition-colors">
                            <span x-text="variantLabel(v)"></span>
                        </button>
                    </template>
                </div>
            </div>
        </template>

        <div class="mt-auto">
            @auth
                <div class="space-y-3">
                    <div class="flex items-end justify-between gap-3">
                        <div class="flex flex-col">
                            @if ($isOutletUser)
                                <div class="flex flex-col">
                                    <span class="mb-1 text-[9px] font-black uppercase leading-none tracking-widest text-indigo-500">Wholesale</span>
                                    <span class="text-lg font-black leading-none text-slate-900">
                                        {{ $currencyIcon }}<span x-text="outletDisplayPrice"></span>
                                    </span>
                                    <template x-if="showOutletOriginalPrice">
                                        <span class="mt-1 text-[11px] font-semibold leading-none text-slate-400 line-through">
                                            {{ $currencyIcon }}<span x-text="outletOriginalDisplayPrice"></span>
                                        </span>
                                    </template>
                                    <span class="mb-1 mt-1 text-[9px] font-black uppercase leading-none tracking-widest text-indigo-500">Selling Price</span>
                                    <span class="text-sm font-black leading-none text-slate-900">
                                        {{ $currencyIcon }}<span x-text="retailDisplayPrice"></span>
                                    </span>
                                    <template x-if="showRetailOriginalPrice">
                                        <span class="mt-1 text-[11px] font-semibold leading-none text-slate-400 line-through">
                                            {{ $currencyIcon }}<span x-text="retailOriginalDisplayPrice"></span>
                                        </span>
                                    </template>
                                </div>
                            @elseif ($isStandardUser)
                                <div class="flex flex-col">
                                    <span class="mb-1 text-[9px] font-black uppercase leading-none tracking-widest text-indigo-500">Wholesale</span>
                                    <span class="text-lg font-black leading-none text-slate-900">
                                        {{ $currencyIcon }}<span x-text="outletDisplayPrice"></span>
                                    </span>
                                    <template x-if="showOutletOriginalPrice">
                                        <span class="mt-1 text-[11px] font-semibold leading-none text-slate-400 line-through">
                                            {{ $currencyIcon }}<span x-text="outletOriginalDisplayPrice"></span>
                                        </span>
                                    </template>
                                </div>
                            @else
                                <div class="flex flex-col">
                                    <span class="mb-1 text-[9px] font-black uppercase leading-none tracking-widest text-slate-400">Price</span>
                                    <span class="text-lg font-black leading-none text-slate-900">
                                        {{ $currencyIcon }}<span x-text="retailDisplayPrice"></span>
                                    </span>
                                    <template x-if="showRetailOriginalPrice">
                                        <span class="mt-1 text-[11px] font-semibold leading-none text-slate-400 line-through">
                                            {{ $currencyIcon }}<span x-text="retailOriginalDisplayPrice"></span>
                                        </span>
                                    </template>
                                </div>
                            @endif
                        </div>

                        <div class="shrink-0 text-right">
                            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-bold"
                                :class="stockPillClass"
                                x-text="stockPillText"></span>
                            <template x-if="(!hasVariants || selectedVariant) && maxAddableQty > 0">
                                <p class="mt-1 text-[10px] font-semibold text-slate-400" x-text="`Max add: ${maxAddableQty}`"></p>
                            </template>
                            <template x-if="(!hasVariants || selectedVariant) && currentStock > 0 && maxAddableQty === 0">
                                <p class="mt-1 text-[10px] font-semibold text-amber-600" x-text="`MOQ ${minimumOrderQty}, stock ${currentStock}`"></p>
                            </template>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-2.5">
                        <div class="mb-2 flex items-center justify-between text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                            <span>MOQ: <span class="text-slate-600" x-text="minimumOrderQty"></span></span>
                            <template x-if="!hasVariants || selectedVariant">
                                <span>In stock: <span class="text-slate-600" x-text="currentStock"></span></span>
                            </template>
                            <template x-if="hasVariants && !selectedVariant">
                                <span class="normal-case tracking-normal text-slate-500">Select variant first</span>
                            </template>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <input type="number"
                                x-model.number="qty"
                                :min="minimumOrderQty"
                                :max="maxAddableQty > 0 ? maxAddableQty : minimumOrderQty"
                                :step="minimumOrderQty"
                                @change="normalizeQty()"
                                class="h-10 w-full rounded-lg border border-slate-200 bg-white p-0 text-center text-xs font-black text-slate-900 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-100">

                            <button @click="canAdd ? addToCart(product, selectedVariant, qty) : notify(cannotAddMessage, 'error')"
                                :class="canAdd
                                    ? 'bg-slate-900 text-white shadow-md shadow-slate-200 hover:bg-indigo-600'
                                    : 'cursor-not-allowed bg-slate-200 text-slate-400'"
                                class="flex h-10 w-full items-center justify-center gap-1.5 rounded-lg text-[11px] font-black uppercase tracking-wider transition-all active:scale-95">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <div class="flex flex-col">
                    <span class="mb-1 text-[9px] font-black uppercase tracking-widest text-slate-400">Price</span>
                    <span class="rounded-md bg-rose-50 px-2 py-0.5 text-xs font-bold uppercase tracking-wider text-rose-500">Login</span>
                </div>
            @endauth
        </div>
    </div>

    {{ $slot }}
</article>
