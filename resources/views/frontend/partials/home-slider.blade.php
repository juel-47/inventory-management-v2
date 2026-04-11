@php
    $currencyIcon = $currencyIcon ?? optional($settings)->currency_icon ?? 'Tk';
    $sliders = collect($sliders ?? []);
@endphp

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
                                        "{{ $slider->title }}"
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
