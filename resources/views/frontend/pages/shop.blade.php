@extends('layouts.frontend')
@section('content')
    <div class="bg-slate-50 min-h-screen" x-data="shopFilter()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Sidebar Filters -->
                <aside class="w-full lg:w-72 shrink-0">
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 sticky top-24">
                        <h2 class="text-xl font-bold text-slate-900 mb-8">Filters</h2>

                        <!-- Categories -->
                        <div class="mb-10">
                            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6">Categories</h3>
                            <div class="space-y-4">
                                @foreach ($categories as $category)
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between group">
                                            <button @click="toggleCategory({{ $category->id }})"
                                                class="text-sm font-bold transition-colors text-left"
                                                :class="activeCat === {{ $category->id }} ||
                                                    {{ request('category') == $category->id ? 'true' : 'false' }} ?
                                                    'text-indigo-600' : 'text-slate-600 hover:text-indigo-600'">
                                                {{ $category->name }}
                                            </button>
                                            @if ($category->subCategories->count() > 0)
                                                <button
                                                    @click="activeCat = (activeCat === {{ $category->id }} ? null : {{ $category->id }})"
                                                    class="p-1 rounded-lg hover:bg-slate-50 text-slate-400 transition-transform"
                                                    :class="{ 'rotate-180': activeCat === {{ $category->id }} }">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>

                                        <!-- Subcategories -->
                                        <div x-show="activeCat === {{ $category->id }}" x-collapse>
                                            <div class="pl-4 space-y-2 border-l-2 border-slate-50 mt-2 ml-1">
                                                @foreach ($category->subCategories as $sub)
                                                    <div class="space-y-2">
                                                        <div class="flex items-center justify-between group">
                                                            <button
                                                                @click="toggleSubCategory({{ $category->id }}, {{ $sub->id }})"
                                                                class="text-[13px] font-semibold transition-colors text-left"
                                                                :class="activeSub === {{ $sub->id }} ||
                                                                    {{ request('subcategory') == $sub->id ? 'true' : 'false' }} ?
                                                                    'text-indigo-600' :
                                                                    'text-slate-500 hover:text-indigo-600'">
                                                                {{ $sub->name }}
                                                            </button>
                                                            @if ($sub->childCategories->count() > 0)
                                                                <button
                                                                    @click="activeSub = (activeSub === {{ $sub->id }} ? null : {{ $sub->id }})"
                                                                    class="p-0.5 rounded-md hover:bg-slate-50 text-slate-300 transition-transform"
                                                                    :class="{ 'rotate-180': activeSub === {{ $sub->id }} }">
                                                                    <svg class="w-3 h-3" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                                            stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                                    </svg>
                                                                </button>
                                                            @endif
                                                        </div>

                                                        <!-- Child Categories -->
                                                        <div x-show="activeSub === {{ $sub->id }}" x-collapse>
                                                            <div
                                                                class="pl-4 space-y-1.5 border-l-2 border-slate-50 mt-1 ml-1">
                                                                @foreach ($sub->childCategories as $child)
                                                                    <button
                                                                        @click="toggleChildCategory({{ $category->id }}, {{ $sub->id }}, {{ $child->id }})"
                                                                        class="block text-[12px] font-medium transition-colors text-left"
                                                                        :class="{{ request('childcategory') == $child->id ? 'true' : 'false' }}
                                                                            ? 'text-indigo-600' :
                                                                            'text-slate-400 hover:text-indigo-600'">
                                                                        {{ $child->name }}
                                                                    </button>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Price Filter Slider -->
                    @auth
                        <div class="pt-8 border-t border-slate-100">
                            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6">Price Range</h3>
                            
                            <div class="relative w-full h-10 mt-4">
                                <div class="h-1.5 w-full bg-slate-200 rounded-full absolute top-1/2 -translate-y-1/2"></div>
                                <div class="h-1.5 bg-indigo-600 rounded-full absolute top-1/2 -translate-y-1/2"
                                     :style="`left: ${((minPrice - minRange) / (maxRange - minRange)) * 100}%; right: ${100 - ((maxPrice - minRange) / (maxRange - minRange)) * 100}%`" class="text-indigo-500"></div>
                                
                                <input type="range" 
                                       :min="minRange" :max="maxRange" step="1" 
                                       x-model.number="minPrice" 
                                       @input="if(minPrice > maxPrice) minPrice = maxPrice - 1"
                                       @change="applyFilters()"
                                       class="absolute w-full h-1.5 top-1/2 -translate-y-1/2 appearance-none bg-transparent pointer-events-none px-0 cursor-pointer [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:w-5 [&::-webkit-slider-thumb]:h-5 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-indigo-600 [&::-webkit-slider-thumb]:border-2 [&::-webkit-slider-thumb]:border-white [&::-webkit-slider-thumb]:appearance-none [&::-moz-range-thumb]:pointer-events-auto [&::-moz-range-thumb]:w-5 [&::-moz-range-thumb]:h-5 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:bg-indigo-600 [&::-moz-range-thumb]:border-2 [&::-moz-range-thumb]:border-white">
                                
                                <input type="range" 
                                       :min="minRange" :max="maxRange" step="1" 
                                       x-model.number="maxPrice" 
                                       @input="if(maxPrice < minPrice) maxPrice = minPrice + 1"
                                       @change="applyFilters()"
                                       class="absolute w-full h-1.5 top-1/2 -translate-y-1/2 appearance-none bg-transparent pointer-events-none px-0 cursor-pointer [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:w-5 [&::-webkit-slider-thumb]:h-5 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-indigo-600 [&::-webkit-slider-thumb]:border-2 [&::-webkit-slider-thumb]:border-white [&::-webkit-slider-thumb]:appearance-none [&::-moz-range-thumb]:pointer-events-auto [&::-moz-range-thumb]:w-5 [&::-moz-range-thumb]:h-5 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:bg-indigo-600 [&::-moz-range-thumb]:border-2 [&::-moz-range-thumb]:border-white">
                            </div>

                            <div class="flex items-center justify-between mt-6 px-1">
                                <div class="flex flex-col">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Min Price</span>
                                    <span class="text-sm font-bold text-slate-900" x-text="'$' + minPrice"></span>
                                </div>
                                <div class="flex flex-col text-right">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Max Price</span>
                                    <span class="text-sm font-bold text-slate-900" x-text="'$' + maxPrice"></span>
                                </div>
                            </div>
                        </div>
                    @endauth
                    </div>
                </aside>

                <!-- Main Content -->
                <div class="flex-1">
                    <!-- Top Toolbar -->
                    <div
                        class="bg-white rounded-3xl p-4 mb-8 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-center gap-4 px-2">
                            <span class="text-sm font-bold text-slate-400">Showing <span
                                    class="text-slate-900">{{ $products->count() }}</span> of {{ $products->total() }}
                                results</span>
                        </div>

                        <div class="flex items-center gap-4">
                           @php
                               $auth=Auth::user();
                            //    dd($auth);
                           @endphp
                           @if($auth)
                            <div class="flex items-center gap-2">
                                <label
                                    class="text-xs font-black text-slate-400 uppercase tracking-widest hidden sm:block">Sort
                                    by:</label>
                                <select x-model="sort" @change="applyFilters()"
                                    class="bg-slate-50 border-none rounded-2xl px-6 py-2 content-center text-sm font-bold text-slate-700 outline-none focus:ring-2 focus:ring-indigo-100 cursor-pointer appearance-none pr-10 relative">
                                    <option value="latest">Latest Product</option>
                                    <option value="price_low_high">Price: Low to High</option>
                                    <option value="price_high_low">Price: High to Low</option>
                                </select>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Products Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                        @forelse($products as $product)
                            @php
                                $imagePath = $product->thumb_image;
                                $displayPath = (strpos($imagePath, 'http') === 0) 
                                    ? $imagePath 
                                    : asset('storage/' . ltrim($imagePath, '/'));
                                $canViewInventory = auth()->check() && (
                                    auth()->user()->hasRole('Outlet User') || auth()->user()->hasRole('User')
                                );

                                $pData = [
                                    'id' => $product->id,
                                    'name' => $product->name,
                                    'thumb_image' => $displayPath,
                                    'price' => (float)$product->price,
                                    'outlet_price' => (float)$product->outlet_price,
                                    'category' => $product->category->name ?? 'General',
                                    'minimum_order_qty' => (int)($product->minimum_order_qty ?? 1),
                                    'stock' => $canViewInventory ? (int)$product->inventory_stock : 0,
                                    'inventory_visible' => $canViewInventory,
                                ];
                                $vData = $product->variants->map(fn($v) => [
                                        'id' => $v->id,
                                        'name' => $v->name,
                                        'price' => $v->price > 0 ? (float)$v->price : (float)$product->price,
                                        'outlet_price' => $v->outlet_price > 0 ? (float)$v->outlet_price : (float)$product->outlet_price,
                                        'color' => is_object($v->color) ? $v->color->name : ($v->color ?: ''),
                                        'size' => is_object($v->size) ? $v->size->name : ($v->size ?: ''),
                                        'stock' => $canViewInventory ? (int)$v->inventory_stock : null,
                                    ]);
                            @endphp
                            <div x-data="productItem({{ json_encode($pData) }}, {{ json_encode($vData) }})"
                                 class="group relative flex flex-col bg-white rounded-3xl border border-slate-100 p-4 hover:shadow-xl transition-all duration-300">
                                <!-- Image Container -->
                                <div class="aspect-square rounded-2xl bg-slate-50 overflow-hidden relative mb-4">
                                    <img src="{{ $displayPath }}" 
                                         alt="{{ $product->name }}" 
                                         class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700">
                                    
                                    <div class="absolute top-3 left-3">
                                        <span class="px-2 py-1 bg-white/90 backdrop-blur rounded-lg text-[10px] font-bold text-slate-600 uppercase tracking-wider shadow-sm">
                                            {{ $product->category->name ?? 'General' }}
                                        </span>
                                    </div>                                    <div class="absolute top-3 right-3">
                                        <button @click="toggleWishlist(product.id)"
                                                :class="isWishlisted(product.id)
                                                    ? 'bg-rose-50 border-rose-300 text-rose-500 hover:bg-rose-100'
                                                    : 'bg-white/90 border-slate-200 text-slate-400 hover:border-rose-300 hover:text-rose-400'"
                                                class="h-10 w-10 rounded-xl border-2 backdrop-blur flex items-center justify-center transition-all active:scale-95 shadow-sm"
                                                :title="isWishlisted(product.id) ? 'Remove from Wishlist' : 'Add to Wishlist'">
                                            <template x-if="isWishlisted(product.id)">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                                            </template>
                                            <template x-if="!isWishlisted(product.id)">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                                            </template>
                                        </button>
                                    </div>
                                </div>

                                <!-- Info Area -->
                                <div class="px-1 flex-1 flex flex-col">
                                    <h3 class="text-sm font-bold text-slate-900 leading-tight mb-3 line-clamp-2">
                                        <a href="{{ route('product.details', $product->slug) }}" class="hover:text-indigo-600 transition-colors">{{ $product->name }}</a>
                                    </h3>

                                    <!-- Variants Selection -->
                                    <template x-if="hasVariants">
                                        <div class="mb-4">
                                            <div class="flex flex-wrap gap-1.5">
                                                <template x-for="(v, index) in variants" :key="v.id">
                                                    <button type="button"
                                                            @click="inventoryVisible ? (v.stock > 0 ? (selectedVariantIndex = String(index)) : null) : (selectedVariantIndex = String(index))"
                                                            :disabled="inventoryVisible ? (v.stock <= 0) : false"
                                                                :class="inventoryVisible && v.stock <= 0
                                                                ? 'border-slate-200 bg-slate-100 text-slate-300 cursor-not-allowed'
                                                                : (selectedVariantIndex === String(index)
                                                                    ? 'border-indigo-300 bg-indigo-50 text-indigo-700'
                                                                    : 'border-slate-200 bg-white text-slate-600 hover:border-indigo-200 hover:text-indigo-600')"
                                                            class="px-2 py-1 rounded-lg border text-[10px] font-bold leading-none transition-colors">
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
                                                        @if(auth()->user()->hasRole('Outlet User'))
                                                            <div class="flex flex-col">
                                                                <span class="text-[9px] font-black text-indigo-500 uppercase tracking-widest leading-none mb-1">Wholesale</span>
                                                                <span class="text-lg font-black text-slate-900 leading-none">
                                                                    {{$settings->currency_icon}}<span x-text="selectedVariant ? selectedVariant.outlet_price.toFixed(2) : {{ (float)$product->outlet_price }}"></span>
                                                                </span>
                                                                <span class="text-[9px] font-black text-indigo-500 uppercase tracking-widest leading-none mb-1 mt-1">Selling Price</span>
                                                                <span class="text-sm font-black text-slate-900 leading-none">
                                                                    {{$settings->currency_icon}}<span x-text="selectedVariant ? selectedVariant.price.toFixed(2) : {{ (float)$product->price }}"></span>
                                                                </span>
                                                            </div>
                                                        @elseif(auth()->user()->hasRole('User'))
                                                            <div class="flex flex-col">
                                                                <span class="text-[9px] font-black text-indigo-500 uppercase tracking-widest leading-none mb-1">Wholesale</span>
                                                                <span class="text-lg font-black text-slate-900 leading-none">
                                                                    {{$settings->currency_icon}}<span x-text="selectedVariant ? selectedVariant.outlet_price.toFixed(2) : {{ (float)$product->outlet_price }}"></span>
                                                                </span>
                                                            </div>
                                                        @else
                                                            <div class="flex flex-col">
                                                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Price</span>
                                                                <span class="text-lg font-black text-slate-900 leading-none">
                                                                    {{$settings->currency_icon}}<span x-text="selectedVariant ? selectedVariant.price.toFixed(2) : {{ (float)$product->price }}"></span>
                                                                </span>
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
                                                            <span class="text-slate-500 normal-case tracking-normal">Select variant first</span>
                                                        </template>
                                                    </div>

                                                    <div class="grid grid-cols-2 gap-2">
                                                        <input type="number"
                                                               x-model.number="qty"
                                                               :min="minimumOrderQty"
                                                               :max="maxAddableQty > 0 ? maxAddableQty : minimumOrderQty"
                                                               :step="minimumOrderQty"
                                                               @change="normalizeQty()"
                                                               class="h-10 w-full rounded-lg border border-slate-200 bg-white text-center text-xs font-black text-slate-900 p-0 focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100 focus:outline-none">

                                                        <button @click="canAdd ? addToCart(product, selectedVariant, qty) : notify(cannotAddMessage, 'error')"
                                                                :class="canAdd
                                                                    ? 'bg-slate-900 hover:bg-indigo-600 text-white shadow-md shadow-slate-200'
                                                                    : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                                                                class="h-10 w-full rounded-lg flex items-center justify-center gap-1.5 text-[11px] font-black uppercase tracking-wider transition-all active:scale-95">
                                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                                            Add
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="flex flex-col">
                                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Price</span>
                                                <span class="text-xs font-bold text-rose-500 uppercase tracking-wider bg-rose-50 px-2 py-0.5 rounded-md">Login</span>
                                            </div>
                                        @endauth
                                    </div>
                                </div>
                            </div>

                        @empty
                            <div class="col-span-full py-32 text-center">
                                <div class="w-24 h-24 bg-slate-100 rounded-[2.5rem] flex items-center justify-center mx-auto mb-6">
                                    <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-slate-900 mb-2">No products found</h3>
                                <p class="text-slate-500">Try adjusting your filters or search terms.</p>
                                <button @click="resetFilters()" class="mt-8 px-8 py-3 bg-slate-900 text-white rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-indigo-600 transition-all">Clear All Filters</button>
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    <div class="mt-24 border-t border-slate-100">
                        {{ $products->links('vendor.pagination.tailwind') }}
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('productItem', (product, variants) => ({
                    qty: Math.max(1, parseInt(product.minimum_order_qty) || 1),
                    selectedVariantIndex: '',
                    product: product,
                    variants: variants,
                    get minimumOrderQty() {
                        return Math.max(1, parseInt(this.product.minimum_order_qty) || 1);
                    },
                    get inventoryVisible() {
                        return !!this.product.inventory_visible;
                    },
                    get hasVariants() { return this.variants.length > 0 },
                    get selectedVariant() { 
                        return this.selectedVariantIndex !== '' ? this.variants[this.selectedVariantIndex] : null 
                    },
                    variantLabel(v) {
                        const base = v.name || ([v.color, v.size].filter(Boolean).join(' ') || 'Variant');
                        if (!this.inventoryVisible) {
                            return base;
                        }
                        return v.stock <= 0 ? `${base} - Out` : `${base} - ${v.stock}`;
                    },
                    get currentStock() {
                        if (this.hasVariants) {
                            return this.selectedVariant ? Math.max(0, parseInt(this.selectedVariant.stock) || 0) : 0;
                        }
                        return Math.max(0, parseInt(this.product.stock) || 0);
                    },
                    get maxAddableQty() {
                        const stock = this.currentStock;
                        const moq = this.minimumOrderQty;
                        if (stock < moq) return 0;
                        return Math.floor(stock / moq) * moq;
                    },
                    get canAdd() { 
                        if (this.hasVariants && !this.selectedVariant) return false;
                        const requestedQty = this.normalizedQty;
                        return this.currentStock > 0 && this.maxAddableQty > 0 && requestedQty <= this.currentStock;
                    },
                    get cannotAddMessage() {
                        if (this.hasVariants && !this.selectedVariant) {
                            return 'Please select a variant';
                        }
                        if (this.currentStock <= 0) {
                            return 'Out of stock';
                        }
                        if (this.maxAddableQty === 0) {
                            return `Minimum order ${this.minimumOrderQty}, but stock is ${this.currentStock}`;
                        }
                        return `Available stock: ${this.currentStock}`;
                    },
                    get stockPillText() {
                        if (this.hasVariants && !this.selectedVariant) {
                            return 'Select variant';
                        }
                        if (this.currentStock <= 0) {
                            return 'Out of stock';
                        }
                        if (this.currentStock <= 5) {
                            return `Low stock: ${this.currentStock}`;
                        }
                        return `Available: ${this.currentStock}`;
                    },
                    get stockPillClass() {
                        if (this.hasVariants && !this.selectedVariant) {
                            return 'bg-slate-100 text-slate-500 border-slate-200';
                        }
                        if (this.currentStock <= 0) {
                            return 'bg-rose-50 text-rose-600 border-rose-200';
                        }
                        if (this.currentStock <= 5) {
                            return 'bg-amber-50 text-amber-700 border-amber-200';
                        }
                        return 'bg-emerald-50 text-emerald-700 border-emerald-200';
                    },
                    get normalizedQty() {
                        const inputQty = Math.max(1, parseInt(this.qty) || 1);
                        const moq = this.minimumOrderQty;
                        if (inputQty < moq) return moq;
                        if (inputQty > moq) return Math.ceil(inputQty / moq) * moq;
                        return moq;
                    },
                    normalizeQty() {
                        let adjustedQty = this.normalizedQty;
                        if (this.maxAddableQty > 0 && adjustedQty > this.maxAddableQty) {
                            adjustedQty = this.maxAddableQty;
                        }
                        this.qty = adjustedQty;
                        return this.qty;
                    },
                    
                    async addToCart(prod, variant, qty) {
                        try {
                            const finalQty = this.normalizeQty();
                            // Call the cart store directly
                            await Alpine.store('cart').addItem(prod, variant, finalQty);
                            // Notify user
                            const bodyEl = document.querySelector('[x-data*="globalApp"]');
                            if (bodyEl?._x_dataStack?.[0]) {
                                bodyEl._x_dataStack[0].notify('Added to cart ✓', 'success');
                                bodyEl._x_dataStack[0].isCartOpen = true;
                            }
                        } catch (e) {
                            console.error('Add to cart error:', e);
                            this.notify(e?.message || 'Error adding to cart', 'error');
                        }
                    },
                    
                    async toggleWishlist(productId) {
                        try {
                            await Alpine.store('wishlist').toggle(productId);
                            const message = this.isWishlisted(productId) ? 'Added to wishlist ♥' : 'Removed from wishlist';
                            this.notify(message, 'success');
                        } catch (e) {
                            console.error('Wishlist toggle error:', e);
                            this.notify('Error updating wishlist', 'error');
                        }
                    },
                    
                    isWishlisted(productId) {
                        return Alpine.store('wishlist').ids.includes(productId);
                    },
                    
                    notify(message, type = 'error') {
                        const bodyEl = document.querySelector('[x-data*="globalApp"]');
                        if (bodyEl?._x_dataStack?.[0]) {
                            bodyEl._x_dataStack[0].notify(message, type);
                        }
                    }
                }));
            });

            function shopFilter() {
                return {
                    activeCat: {{ request('category', 'null') }},
                    activeSub: {{ request('subcategory', 'null') }},
                    minRange: {{ $min_range }},
                    maxRange: {{ $max_range }},
                    minPrice: {{ request('min_price', $min_range) }},
                    maxPrice: {{ request('max_price', $max_range) }},
                    sort: '{{ request('sort', 'latest') }}',
                    search: '{{ request('search', '') }}',

                    toggleCategory(id) {
                        if (this.activeCat === id) {
                            this.activeCat = null;
                            window.location.href = "{{ route('shop') }}";
                        } else {
                            this.updateUrl({
                                category: id,
                                subcategory: null,
                                childcategory: null
                            });
                        }
                    },

                    toggleSubCategory(catId, subId) {
                        this.updateUrl({
                            category: catId,
                            subcategory: subId,
                            childcategory: null
                        });
                    },

                    toggleChildCategory(catId, subId, childId) {
                        this.updateUrl({
                            category: catId,
                            subcategory: subId,
                            childcategory: childId
                        });
                    },

                    applyFilters() {
                        this.updateUrl({
                            min_price: this.minPrice,
                            max_price: this.maxPrice,
                            sort: this.sort
                        });
                    },

                    resetFilters() {
                        window.location.href = "{{ route('shop') }}";
                    },

                    updateUrl(params) {
                        const url = new URL(window.location.href);
                        Object.keys(params).forEach(key => {
                            if (params[key] === null || params[key] === undefined) {
                                url.searchParams.delete(key);
                            } else {
                                url.searchParams.set(key, params[key]);
                            }
                        });
                        window.location.href = url.toString();
                    }
                }
            }
        </script>
    @endsection
