@extends('layouts.frontend')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white rounded-[3rem] border border-slate-100 shadow-2xl overflow-hidden">
        <div class="flex flex-col lg:flex-row">
            <!-- Product Images -->
            <div class="lg:w-1/2 p-8 lg:p-12 bg-slate-50">
                <div class="aspect-square rounded-[2.5rem] overflow-hidden bg-white shadow-inner group">
                    @php
                        $imagePath = $product->thumb_image;
                        $displayPath = (strpos($imagePath, 'http') === 0) 
                            ? $imagePath 
                            : (file_exists(public_path($imagePath)) 
                                ? asset($imagePath) 
                                : asset('storage/' . $imagePath));
                    @endphp
                    <img src="{{ $displayPath }}" 
                         alt="{{ $product->name }}" 
                         class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-1000">
                </div>
            </div>

            <!-- Product Details -->
            <div class="lg:w-1/2 p-8 lg:p-16 flex flex-col">
                <div class="mb-8">
                    <span class="px-4 py-1.5 bg-indigo-50 border border-indigo-100 rounded-full text-[10px] font-black text-indigo-600 uppercase tracking-[0.2em] shadow-sm mb-6 inline-block">
                        {{ $product->category->name ?? 'General' }}
                    </span>
                    <h1 class="text-4xl md:text-5xl font-black text-slate-900 tracking-tighter mb-4 leading-tight">
                        {{ $product->name }}
                    </h1>
                    <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">Serial Number: {{ $product->product_number }}</p>
                </div>

                <div class="flex-1">
                    <div class="space-y-8 mb-12">
                        @auth
                            <div class="bg-slate-50 p-8 rounded-[2rem] border border-slate-100">
                                @if(auth()->user()->hasRole('Outlet User'))
                                    <div class="grid grid-cols-2 gap-8">
                                        <div>
                                            <span class="text-[10px] font-black text-indigo-400 uppercase tracking-widest block mb-1">Wholesale Price</span>
                                            <div class="flex items-baseline gap-1">
                                                <span class="text-4xl font-black text-slate-900 tracking-tighter">
                                                    {{$settings->currency_icon}}{{ number_format($product->outlet_price, 2) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="border-l border-slate-200 pl-8">
                                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Retail price</span>
                                            <div class="flex items-baseline gap-1">
                                                <span class="text-2xl font-black text-slate-400 tracking-tighter line-through">
                                                    {{$settings->currency_icon}}{{ number_format($product->price, 2) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Price</span>
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-5xl font-black text-slate-900 tracking-tighter">
                                            {{$settings->currency_icon}}{{ number_format($product->price, 2) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="bg-rose-50 p-8 rounded-[2rem] border border-rose-100">
                                <span class="text-[10px] font-black text-rose-400 uppercase tracking-widest block mb-2">Pricing Hidden</span>
                                <p class="text-rose-600 font-bold leading-relaxed mb-4">Pricing and ordering are exclusively available for verified B2B partners.</p>
                                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 bg-rose-600 text-white px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-rose-700 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    Login to View Price
                                </a>
                            </div>
                        @endauth

                        <div class="prose prose-slate max-w-none text-slate-600 font-medium leading-relaxed">
                            {!! $product->long_description !!}
                        </div>
                    </div>

                    @auth
                        <div class="flex flex-col sm:flex-row gap-4 mt-auto">
                            <button @click="addToCart({{ $product->id }})" class="flex-1 bg-slate-900 text-white py-5 rounded-2xl font-black text-sm uppercase tracking-widest hover:bg-indigo-600 shadow-2xl shadow-indigo-100 transition-all active:scale-95 flex items-center justify-center gap-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                ADD TO INVENTORY
                            </button>
                        </div>
                    @endauth
                </div>

                <div class="mt-12 pt-8 border-t border-slate-50 flex items-center gap-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Quality Verified</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        </div>
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">B2B Exclusive</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
