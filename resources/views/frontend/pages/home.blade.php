@extends('layouts.frontend')

@section('title', 'B2B Home')

@section('content')
    @php
        $currencyIcon = optional($settings)->currency_icon ?? 'Tk';
        $roleContext = $roleContext ?? [];

        $isOutletUser = (bool) data_get($roleContext, 'isOutletUser', false);
        $isStandardUser = (bool) data_get($roleContext, 'isStandardUser', false);
        $isOutletCustomer = (bool) data_get($roleContext, 'isOutletCustomer', false);
        $sliders = collect($sliders ?? []);
        $latestCategoryBlocks = collect($latestCategoryBlocks ?? []);
    @endphp

    <div class="bg-slate-100 py-6 sm:py-8">
        @if ($sliders->isNotEmpty())
            <section class="w-full overflow-hidden bg-gradient-to-br from-indigo-950 via-slate-900 to-slate-950 shadow-sm">
                <div class="relative" data-home-slider>
                    <div class="relative h-80 sm:h-[420px] lg:h-[520px]">
                        @foreach ($sliders as $index => $slider)
                            <div
                                class="absolute inset-0 transition-opacity duration-700 {{ $index === 0 ? 'opacity-100' : 'opacity-0 pointer-events-none' }}"
                                data-slide="{{ $index }}"
                                aria-hidden="{{ $index === 0 ? 'false' : 'true' }}">
                                <div class="absolute inset-0">
                                    <div class="mx-auto flex h-full w-full max-w-7xl flex-col items-center gap-6 px-5 py-8 sm:px-7 lg:flex-row lg:gap-10 lg:px-10">
                                        <div class="flex w-full items-center justify-center lg:w-1/2">
                                            <img src="{{ $slider->banner ? Storage::url($slider->banner) : asset('uploads/default.png') }}"
                                                alt="{{ $slider->title }}"
                                                class="max-h-64 w-full object-contain sm:max-h-80 lg:max-h-[420px]">
                                        </div>
                                        <div class="w-full text-center text-white lg:w-1/2 lg:text-left">
                                            <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-indigo-200">Featured</p>
                                            <h2 class="mt-3 text-3xl font-semibold leading-tight text-white sm:text-4xl lg:text-5xl">
                                                “{{ $slider->title }}”
                                            </h2>
                                            @if (!empty($slider->description))
                                                <p class="mt-4 text-sm text-white/70 sm:text-base">
                                                    {{ $slider->description }}
                                                </p>
                                            @endif
                                            <div class="mt-6 flex flex-wrap items-center justify-center gap-3 lg:justify-start">
                                                @if (!empty($slider->starting_price) && (float) $slider->starting_price > 0)
                                                    <span class="inline-flex items-center rounded-full bg-white/10 px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-white/90">
                                                        Starting {{ $currencyIcon }}{{ number_format((float) $slider->starting_price, 2) }}
                                                    </span>
                                                @endif
                                                @if (!empty($slider->button_url))
                                                    <a href="{{ $slider->button_url }}"
                                                        class="inline-flex h-11 items-center justify-center rounded-full bg-indigo-500 px-6 text-[12px] font-semibold uppercase tracking-[0.2em] text-white transition hover:bg-indigo-400">
                                                        Shop Now
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="absolute bottom-4 left-0 right-0 flex justify-center gap-2">
                        @foreach ($sliders as $index => $slider)
                            <button type="button" data-indicator="{{ $index }}"
                                class="h-2.5 w-2.5 rounded-full {{ $index === 0 ? 'bg-indigo-300' : 'bg-white/30' }}"></button>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if ($latestCategoryBlocks->isEmpty())
                <section class="rounded-2xl border border-slate-200 bg-white p-6 text-center text-sm text-slate-500 shadow-sm">
                    No active categories with products found.
                </section>
            @else
                @foreach ($latestCategoryBlocks as $block)
                    <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                        <div class="mb-4 flex items-end justify-between gap-2">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">Latest Products</p>
                                <h2 class="text-lg font-bold text-slate-900">{{ $block['category']->name }}</h2>
                            </div>
                            <a href="{{ route('shop', ['category' => $block['category']->id]) }}"
                                class="text-[11px] font-bold uppercase tracking-[0.12em] text-indigo-600 hover:text-indigo-500">
                                View All
                            </a>
                        </div>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 md:gap-2">
                            @foreach ($block['cards'] as $card)
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
                    </section>
                @endforeach
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            const slider = document.querySelector('[data-home-slider]');
            if (!slider) {
                return;
            }

            const slides = Array.from(slider.querySelectorAll('[data-slide]'));
            if (slides.length === 0) {
                return;
            }

            const indicators = Array.from(slider.querySelectorAll('[data-indicator]'));

            let activeIndex = 0;
            let timerId = null;

            const setActive = (index) => {
                activeIndex = index;
                slides.forEach((slide, idx) => {
                    const isActive = idx === index;
                    slide.classList.toggle('opacity-100', isActive);
                    slide.classList.toggle('opacity-0', !isActive);
                    slide.classList.toggle('pointer-events-none', !isActive);
                    slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
                });

                indicators.forEach((dot, idx) => {
                    dot.classList.toggle('bg-white', idx === index);
                    dot.classList.toggle('bg-white/40', idx !== index);
                });
            };

            const next = () => {
                setActive((activeIndex + 1) % slides.length);
            };

            const prev = () => {
                setActive((activeIndex - 1 + slides.length) % slides.length);
            };

            const stop = () => {
                if (timerId) {
                    clearInterval(timerId);
                    timerId = null;
                }
            };

            const start = () => {
                if (slides.length < 2) {
                    return;
                }
                stop();
                timerId = setInterval(next, 6000);
            };

            indicators.forEach((dot, idx) => {
                dot.addEventListener('click', () => {
                    setActive(idx);
                    start();
                });
            });

            slider.addEventListener('click', function (event) {
                if (event.target.closest('[data-indicator]')) {
                    return;
                }
                next();
                start();
            });

            slider.addEventListener('mouseenter', stop);
            slider.addEventListener('mouseleave', start);

            setActive(0);
            start();
        })();

    </script>
@endsection
