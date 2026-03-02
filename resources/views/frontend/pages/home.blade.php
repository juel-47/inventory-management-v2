@extends('layouts.frontend')

@section('title', 'B2B Home')

@section('content')
    @php
        $currencyIcon = optional($settings)->currency_icon ?? 'Tk';
        $roleContext = $roleContext ?? [];

        $isOutletUser = (bool) data_get($roleContext, 'isOutletUser', false);
        $isStandardUser = (bool) data_get($roleContext, 'isStandardUser', false);
        $isOutletCustomer = (bool) data_get($roleContext, 'isOutletCustomer', false);
        $featuredCards = collect($featuredCards ?? []);
        $topBrands = collect($topBrands ?? []);

        $categoriesPaginator = $categories ?? null;
        $isCategoryPaginated = $categoriesPaginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator;
        $categoryItems = $isCategoryPaginated ? collect($categoriesPaginator->items()) : collect($categories ?? []);
        $categoryTotal = $isCategoryPaginated ? (int) $categoriesPaginator->total() : $categoryItems->count();

        $activeProductCount = (int) ($activeProductCount ?? 0);
        $activeBrandCount = (int) ($activeBrandCount ?? 0);
        $inStockProductCount = (int) ($inStockProductCount ?? 0);
        $currentOutletId = (int) data_get($roleContext, 'outletId', $outletId ?? 0);
    @endphp

    <div class="bg-slate-100 py-6 sm:py-8">
        <div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="grid gap-5 p-5 sm:p-6 lg:grid-cols-[1.5fr_1fr] lg:items-center">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-indigo-600">B2B Trading Desk</p>
                        <h1 class="mt-2 text-2xl font-bold leading-tight text-slate-900 sm:text-3xl">
                            Real-time wholesale catalog for outlet and corporate buyers.
                        </h1>
                        <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-600">
                            Live stock visibility, MOQ-driven orders, and role-based prices from your existing backend inventory.
                        </p>

                        <div class="mt-4 flex flex-wrap gap-2.5">
                            <a href="{{ route('shop') }}"
                                class="inline-flex h-10 items-center justify-center rounded-lg bg-slate-900 px-4 text-[11px] font-bold uppercase tracking-[0.14em] text-white transition hover:bg-slate-800">
                                Browse Catalog
                            </a>
                            @auth
                                <a href="{{ route('account.index', ['panel' => 'order-form']) }}"
                                    class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-700 transition hover:border-indigo-300 hover:text-indigo-600">
                                    Quick Order
                                </a>
                            @else
                                <a href="{{ route('login') }}"
                                    class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-700 transition hover:border-indigo-300 hover:text-indigo-600">
                                    Login For Wholesale
                                </a>
                            @endauth
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-4 lg:grid-cols-2">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
                            <p class="text-[9px] font-bold uppercase tracking-[0.18em] text-slate-500">Categories</p>
                            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $categoryTotal }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
                            <p class="text-[9px] font-bold uppercase tracking-[0.18em] text-slate-500">Products</p>
                            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $activeProductCount }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
                            <p class="text-[9px] font-bold uppercase tracking-[0.18em] text-slate-500">Brands</p>
                            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $activeBrandCount }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
                            <p class="text-[9px] font-bold uppercase tracking-[0.18em] text-slate-500">In Stock</p>
                            <p class="mt-1 text-2xl font-bold text-indigo-600">{{ $inStockProductCount }}</p>
                            @if ($currentOutletId > 0)
                                <p class="text-[10px] text-slate-500">Outlet {{ $currentOutletId }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </section>

            <div class="grid gap-5 xl:grid-cols-[1.75fr_1fr]">
                <section id="category-access-panel" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="mb-3.5 flex items-end justify-between gap-2">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">Browse Faster</p>
                            <h2 class="text-lg font-bold text-slate-900">Category Access</h2>
                        </div>
                        <a href="{{ route('shop') }}" class="text-[11px] font-bold uppercase tracking-[0.12em] text-indigo-600 hover:text-indigo-500">View All</a>
                    </div>

                    @if ($categoryItems->isEmpty())
                        <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-5 text-center text-sm text-slate-500">
                            No active categories found.
                        </div>
                    @else
                        <div class="grid gap-2.5 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($categoryItems as $category)
                                <a href="{{ route('shop', ['category' => $category->id]) }}"
                                    class="group rounded-xl border border-slate-200 bg-white px-3.5 py-3 transition hover:border-indigo-200 hover:bg-indigo-50/30">
                                    <p class="line-clamp-1 text-sm font-bold text-slate-900 transition group-hover:text-indigo-600">{{ $category->name }}</p>
                                    <p class="mt-1 text-[11px] font-medium text-slate-500">{{ (int) ($category->active_products_count ?? 0) }} active products</p>
                                </a>
                            @endforeach
                        </div>

                        @if ($isCategoryPaginated && $categoriesPaginator->hasPages())
                            <div class="category-pagination mt-5 flex justify-center border-t border-slate-100 pt-4">
                                {{ $categoriesPaginator->onEachSide(1)->links('vendor.pagination.tailwind') }}
                            </div>
                        @endif
                    @endif
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="mb-3.5">
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">Brand Performance</p>
                        <h2 class="text-lg font-bold text-slate-900">Top Brands</h2>
                    </div>

                    @if ($topBrands->isEmpty())
                        <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-5 text-center text-sm text-slate-500">
                            No active brands found.
                        </div>
                    @else
                        <div class="space-y-2">
                            @foreach ($topBrands as $brand)
                                <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5">
                                    <p class="line-clamp-1 text-sm font-semibold text-slate-900">{{ $brand->name }}</p>
                                    <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-bold text-slate-600">
                                        {{ (int) ($brand->active_products_count ?? 0) }} items
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>

            <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="mb-4 flex items-end justify-between gap-2">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">Live Inventory Feed</p>
                        <h2 class="text-lg font-bold text-slate-900">Latest Products</h2>
                    </div>
                    <a href="{{ route('shop', ['sort' => 'latest']) }}" class="text-[11px] font-bold uppercase tracking-[0.12em] text-indigo-600 hover:text-indigo-500">
                        View Latest
                    </a>
                </div>

                @if ($featuredCards->isEmpty())
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                        No active products available.
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 md:gap-2">
                        @foreach ($featuredCards as $card)
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
                        @endforeach
                    </div>
                @endif
            </section>

            <section id="about" class="scroll-mt-28 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="max-w-3xl">
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-indigo-600">About</p>
                    <h2 class="mt-1.5 text-xl font-bold text-slate-900">B2B Inventory & Ordering Platform</h2>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">
                        This portal is built for outlet and trade customers to place wholesale orders with live stock visibility,
                        variant selection, and role-based pricing from your backend product data.
                    </p>
                </div>
            </section>

            <section id="contact" class="scroll-mt-28 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-indigo-600">Contact Us</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ optional($settings)->site_name ?? config('app.name', 'Inventory B2B') }}</p>
                        <p class="mt-1 text-sm text-slate-600">For order help and business support, connect from your account panel.</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-indigo-600">Support Links</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <a href="{{ route('shop') }}"
                                class="inline-flex h-8 items-center rounded-md border border-slate-300 bg-white px-3 text-[10px] font-bold uppercase tracking-[0.12em] text-slate-700 transition hover:border-indigo-300 hover:text-indigo-600">
                                B2B Shop
                            </a>
                            @auth
                                <a href="{{ route('account.index') }}"
                                    class="inline-flex h-8 items-center rounded-md border border-slate-300 bg-white px-3 text-[10px] font-bold uppercase tracking-[0.12em] text-slate-700 transition hover:border-indigo-300 hover:text-indigo-600">
                                    My Account
                                </a>
                            @else
                                <a href="{{ route('login') }}"
                                    class="inline-flex h-8 items-center rounded-md border border-slate-300 bg-white px-3 text-[10px] font-bold uppercase tracking-[0.12em] text-slate-700 transition hover:border-indigo-300 hover:text-indigo-600">
                                    Sign In
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            let loadingCategoryPage = false;

            async function loadCategoryPage(url, pushState = true) {
                const currentPanel = document.getElementById('category-access-panel');
                if (!currentPanel || loadingCategoryPage) {
                    return;
                }

                loadingCategoryPage = true;
                currentPanel.classList.add('opacity-60', 'pointer-events-none');

                try {
                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Failed to load categories');
                    }

                    const html = await response.text();
                    const parsed = new DOMParser().parseFromString(html, 'text/html');
                    const nextPanel = parsed.getElementById('category-access-panel');

                    if (!nextPanel) {
                        window.location.href = url;
                        return;
                    }

                    const oldPanel = document.getElementById('category-access-panel');
                    oldPanel.outerHTML = nextPanel.outerHTML;

                    if (pushState) {
                        window.history.pushState({ categoryPagination: true }, '', url);
                    }
                } catch (error) {
                    window.location.href = url;
                } finally {
                    loadingCategoryPage = false;
                    const refreshedPanel = document.getElementById('category-access-panel');
                    if (refreshedPanel) {
                        refreshedPanel.classList.remove('opacity-60', 'pointer-events-none');
                    }
                }
            }

            document.addEventListener('click', function (event) {
                const paginationLink = event.target.closest('#category-access-panel .category-pagination a');
                if (!paginationLink) {
                    return;
                }

                event.preventDefault();
                loadCategoryPage(paginationLink.href, true);
            });

            window.addEventListener('popstate', function () {
                const hasCategoryPageParam = new URL(window.location.href).searchParams.has('category_page');
                if (!hasCategoryPageParam) {
                    return;
                }

                loadCategoryPage(window.location.href, false);
            });
        })();
    </script>
@endsection
