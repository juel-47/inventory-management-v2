@extends('layouts.frontend')
@section('content')
    @php
        $currencyIcon = optional($settings)->currency_icon ?? 'Tk';
        $roleContext = $roleContext ?? [];
        $isOutletUser = (bool) data_get($roleContext, 'isOutletUser', false);
        $isStandardUser = (bool) data_get($roleContext, 'isStandardUser', false);
        $shopCards = collect($shopCards ?? []);
    @endphp

    <div id="shop-page-root" class="bg-slate-50 min-h-screen" x-data="shopFilter()">
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
                                    <span class="text-sm font-bold text-slate-900" x-text="'{{ $currencyIcon }}' + minPrice"></span>
                                </div>
                                <div class="flex flex-col text-right">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Max Price</span>
                                    <span class="text-sm font-bold text-slate-900" x-text="'{{ $currencyIcon }}' + maxPrice"></span>
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
                            <div class="flex items-center gap-2">
                                <label
                                    class="text-xs font-black text-slate-400 uppercase tracking-widest hidden sm:block">Sort
                                    by:</label>
                                <select x-model="sort" @change="applyFilters()"
                                    class="bg-slate-50 border-none rounded-2xl px-6 py-2 content-center text-sm font-bold text-slate-700 outline-none focus:ring-2 focus:ring-indigo-100 cursor-pointer appearance-none pr-10 relative">
                                    <option value="stock_first">In Stock First</option>
                                    <option value="latest">Latest Product</option>
                                    <option value="price_low_high">Price: Low to High</option>
                                    <option value="price_high_low">Price: High to Low</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Products Grid -->
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 md:gap-2">
                        @forelse($shopCards as $card)
                            <x-frontend.product-card
                                :product="$card['product']"
                                :variants="$card['variants']"
                                :display-path="$card['display_path']"
                                :category-name="$card['category_name']"
                                :currency-icon="$currencyIcon"
                                :is-outlet-user="$isOutletUser"
                                :is-standard-user="$isStandardUser"
                                :details-url="$card['details_url']"
                                class="rounded-2xl p-3" />

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
                    <div id="shop-pagination" class="mt-24 border-t border-slate-100">
                        {{ $products->links('vendor.pagination.tailwind') }}
                    </div>
                </div>
            </div>
        </div>

        <script>
            (function () {
                let loadingShopPage = false;
                const shopBaseUrl = @json(route('shop'));

                async function loadShopPage(url, pushState = true) {
                    const currentRoot = document.getElementById('shop-page-root');
                    if (!currentRoot || loadingShopPage) {
                        return;
                    }

                    loadingShopPage = true;
                    currentRoot.classList.add('opacity-60', 'pointer-events-none');

                    try {
                        const response = await fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (!response.ok) {
                            throw new Error('Failed to load shop page');
                        }

                        const html = await response.text();
                        const parsed = new DOMParser().parseFromString(html, 'text/html');
                        const nextRoot = parsed.getElementById('shop-page-root');

                        if (!nextRoot) {
                            window.location.href = url;
                            return;
                        }

                        const oldRoot = document.getElementById('shop-page-root');
                        oldRoot.outerHTML = nextRoot.outerHTML;

                        if (pushState) {
                            window.history.pushState({ shopAjax: true }, '', url);
                        }
                    } catch (error) {
                        window.location.href = url;
                    } finally {
                        loadingShopPage = false;
                        const refreshedRoot = document.getElementById('shop-page-root');
                        if (refreshedRoot) {
                            refreshedRoot.classList.remove('opacity-60', 'pointer-events-none');
                        }
                    }
                }

                window.shopFilter = function () {
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
                                this.updateUrl({
                                    category: null,
                                    subcategory: null,
                                    childcategory: null,
                                });
                                return;
                            }

                            this.updateUrl({
                                category: id,
                                subcategory: null,
                                childcategory: null
                            });
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
                            loadShopPage(shopBaseUrl, true);
                        },

                        updateUrl(params) {
                            const url = new URL(window.location.href);
                            Object.keys(params).forEach(key => {
                                if (params[key] === null || params[key] === undefined || params[key] === '') {
                                    url.searchParams.delete(key);
                                } else {
                                    url.searchParams.set(key, params[key]);
                                }
                            });

                            loadShopPage(url.toString(), true);
                        }
                    };
                };

                document.addEventListener('click', function (event) {
                    const paginationLink = event.target.closest('#shop-pagination a');
                    if (!paginationLink) {
                        return;
                    }

                    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                        return;
                    }

                    event.preventDefault();
                    loadShopPage(paginationLink.href, true);
                });

                window.addEventListener('popstate', function () {
                    const currentUrl = new URL(window.location.href);
                    const shopUrl = new URL(shopBaseUrl);

                    if (currentUrl.pathname !== shopUrl.pathname) {
                        return;
                    }

                    loadShopPage(window.location.href, false);
                });
            })();
        </script>
    @endsection
