@extends('layouts.frontend')
@section('content')
<div class="min-h-screen bg-white">

    <!-- Hero Slider -->
    <section class="relative h-[80vh] min-h-[500px] overflow-hidden bg-slate-100" 
             x-data="{ 
                activeSlide: 0,
                slides: [
                    { 
                        image: 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?q=80&w=2070&auto=format&fit=crop', 
                        title: 'Danish Souvenirs', 
                        subtitle: 'Take a piece of Denmark home with you.',
                        cta: 'Shop Now',
                        bgColor: 'bg-indigo-950'
                    },
                    { 
                        image: 'https://images.unsplash.com/photo-1590005354167-6da97870c91d?q=80&w=2081&auto=format&fit=crop', 
                        title: 'Viking Heritage', 
                        subtitle: 'Traditional crafts and timeless designs.',
                        cta: 'Explore',
                        bgColor: 'bg-slate-900'
                    },
                    { 
                        image: 'https://images.unsplash.com/photo-1605276374104-dee2a0ed3cd6?q=80&w=2070&auto=format&fit=crop', 
                        title: 'Hygge Memories', 
                        subtitle: 'Cozy essentials for your collection.',
                        cta: 'Discover',
                        bgColor: 'bg-indigo-900'
                    }
                ],
                next() { this.activeSlide = (this.activeSlide + 1) % this.slides.length },
                prev() { this.activeSlide = (this.activeSlide - 1 + this.slides.length) % this.slides.length },
                autoplay() { setInterval(() => this.next(), 6000) }
             }"
             x-init="autoplay()">
        
        <template x-for="(slide, index) in slides" :key="index">
            <div x-show="activeSlide === index" 
                 x-transition:enter="transition ease-out duration-1000"
                 x-transition:enter-start="opacity-0 scale-105"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-1000"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-105"
                 class="absolute inset-0 w-full h-full"
                 :class="slide.bgColor">
                
                <!-- Background Image with Overlay -->
                <div class="absolute inset-0 bg-slate-900/40 z-10"></div>
                <img :src="slide.image" 
                     class="w-full h-full object-cover" 
                     :alt="slide.title"
                     loading="eager">
                
                <!-- Content -->
                <div class="absolute inset-0 z-20 flex items-center justify-center text-center px-4">
                    <div class="max-w-4xl">
                        <span x-text="slide.subtitle" 
                              class="text-indigo-400 text-sm font-black uppercase tracking-[0.3em] mb-4 block transform transition-all duration-1000 delay-300"
                              :class="activeSlide === index ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0'"></span>
                        <h1 x-text="slide.title" 
                            class="text-5xl md:text-8xl font-black text-white mb-8 leading-none transform transition-all duration-1000 delay-500"
                            :class="activeSlide === index ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0'"></h1>
                        <a href="{{ route('shop') }}" 
                           class="inline-block px-10 md:px-12 py-4 md:py-5 bg-white text-slate-900 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-indigo-600 hover:text-white transform transition-all duration-1000 delay-700 shadow-2xl"
                           :class="activeSlide === index ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0'">
                           <span x-text="slide.cta"></span>
                        </a>
                    </div>
                </div>
            </div>
        </template>

        <!-- Slider Controls -->
        <div class="absolute bottom-10 left-1/2 -translate-x-1/2 z-30 flex gap-4">
            <template x-for="(slide, index) in slides" :key="index">
                <button @click="activeSlide = index" 
                        class="h-1.5 transition-all duration-300 rounded-full"
                        :class="activeSlide === index ? 'w-12 bg-white' : 'w-4 bg-white/30 hover:bg-white/50'"></button>
            </template>
        </div>
    </section>

    <!-- Categories Section -->
    {{-- <section class="py-24 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-6">
            <div>
                <span class="text-indigo-600 text-[10px] font-black uppercase tracking-[0.2em] mb-4 block">Curated Collections</span>
                <h2 class="text-4xl font-black text-slate-900">Shop by Category</h2>
            </div>
            <a href="{{ route('shop') }}" class="group flex items-center gap-3 text-sm font-bold text-slate-400 hover:text-indigo-600 transition-colors">
                View All Categories
                <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($categories as $category)
                <a href="{{ route('shop', ['category' => $category->id]) }}" 
                   class="group relative h-80 rounded-[2.5rem] overflow-hidden bg-slate-100 shadow-sm border border-slate-100">
                    @php
                        $catImage = $category->image;
                        $displayCatImage = (strpos($catImage, 'http') === 0) 
                            ? $catImage 
                            : (file_exists(public_path($catImage)) 
                                ? asset($catImage) 
                                : ($catImage ? asset('storage/' . $catImage) : 'https://images.unsplash.com/photo-1582555172866-f73bb12a2ab3?q=80&w=500&auto=format&fit=crop'));
                    @endphp
                    <img src="{{ $displayCatImage }}" 
                         alt="{{ $category->name }}" 
                         class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-1000">
                    
                    <div class="absolute inset-0 bg-linear-to-t from-slate-900/80 via-transparent to-transparent opacity-60 group-hover:opacity-80 transition-opacity"></div>
                    
                    <div class="absolute bottom-8 left-8 right-8">
                        <span class="text-[10px] font-black text-white/60 uppercase tracking-widest mb-1 block">
                            {{ $category->subCategories->count() }} Subcategories
                        </span>
                        <h3 class="text-2xl font-black text-white mb-4">{{ $category->name }}</h3>
                        <div class="flex items-center gap-2 text-[10px] font-black text-white uppercase tracking-widest opacity-0 group-hover:opacity-100 group-hover:translate-y-0 translate-y-4 transition-all duration-500">
                            Explore Collection 
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </section> --}}

    <!-- Trust Section -->
    <section class="py-24 border-t border-slate-100 bg-slate-50/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                <div class="flex flex-col items-center text-center group">
                    <div class="w-16 h-16 bg-white rounded-2xl shadow-sm border border-slate-100 flex items-center justify-center mb-6 group-hover:rotate-6 transition-transform">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h4 class="text-lg font-bold text-slate-900 mb-2">Authentic Quality</h4>
                    <p class="text-sm text-slate-500 max-w-[250px]">Carefully selected souvenirs that embody the spirit of Denmark.</p>
                </div>
                <div class="flex flex-col items-center text-center group">
                    <div class="w-16 h-16 bg-white rounded-2xl shadow-sm border border-slate-100 flex items-center justify-center mb-6 group-hover:rotate-6 transition-transform">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h4 class="text-lg font-bold text-slate-900 mb-2">Fast Logistics</h4>
                    <p class="text-sm text-slate-500 max-w-[250px]">Efficient B2B processing to keep your inventory moving.</p>
                </div>
                <div class="flex flex-col items-center text-center group">
                    <div class="w-16 h-16 bg-white rounded-2xl shadow-sm border border-slate-100 flex items-center justify-center mb-6 group-hover:rotate-6 transition-transform">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04c0 4.835 1.503 9.359 4.081 13.045a11.959 11.959 0 0112.537 0c2.578-3.686 4.081-8.11 4.081-13.045z"></path></svg>
                    </div>
                    <h4 class="text-lg font-bold text-slate-900 mb-2">Secure B2B Portal</h4>
                    <p class="text-sm text-slate-500 max-w-[250px]">Authorized access only for verified outlet and regular users.</p>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
