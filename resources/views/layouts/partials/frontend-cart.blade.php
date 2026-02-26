{{-- =====================================================
     CART DRAWER (Slide-over Panel)
     ===================================================== --}}
<div x-show="isCartOpen"
     class="fixed inset-0 z-[100] overflow-hidden"
     aria-labelledby="slide-over-title"
     role="dialog"
     aria-modal="true"
     x-cloak>
    <div class="absolute inset-0 overflow-hidden">

        {{-- Backdrop --}}
        <div x-show="isCartOpen"
             x-transition:enter="ease-in-out duration-500"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in-out duration-500"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="isCartOpen = false"
             class="absolute inset-0 bg-indigo-900/40 backdrop-blur-[2px] transition-opacity"></div>

        {{-- Panel --}}
        <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
            <div x-show="isCartOpen"
                 x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full"
                 class="pointer-events-auto w-screen max-w-md">
                <div class="flex h-full flex-col overflow-y-scroll bg-white shadow-2xl">

                    {{-- Header --}}
                    <div class="flex-1 overflow-y-auto px-6 py-8">
                        <div class="flex items-start justify-between">
                            <h2 class="text-xl font-bold text-slate-900" id="slide-over-title">Shopping Cart</h2>
                            <div class="ml-3 flex h-7 items-center">
                                <button @click="isCartOpen = false" type="button" class="relative -m-2 p-2 text-slate-400 hover:text-slate-500 transition-colors">
                                    <span class="absolute -inset-0.5"></span>
                                    <span class="sr-only">Close panel</span>
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        </div>

                        {{-- Cart Items --}}
                        <div class="mt-12">
                            <div class="flow-root">
                                <ul role="list" class="-my-6 divide-y divide-slate-100">
                                    <template x-for="item in cartItems" :key="item.id">
                                        <li class="flex py-6">
                                            <div class="h-24 w-24 shrink-0 overflow-hidden rounded-2xl border border-slate-100 bg-slate-50">
                                                <img :src="item.image" :alt="item.name" class="h-full w-full object-cover object-center">
                                            </div>
                                            <div class="ml-4 flex flex-1 flex-col">
                                                <div>
                                                    <div class="flex justify-between text-base font-bold text-slate-900">
                                                        <h3 class="line-clamp-1"><span x-text="item.name"></span></h3>
                                                        <p class="ml-4" x-text="'{{ $settings->currency_icon }}' + (item.price * item.quantity).toFixed(2)"></p>
                                                    </div>
                                                    <p class="mt-1 text-[10px] font-black text-indigo-500 uppercase tracking-widest" x-text="item.category"></p>
                                                    <template x-if="item.variant_label">
                                                        <p class="mt-1 text-xs text-slate-500" x-text="item.variant_label"></p>
                                                    </template>
                                                </div>
                                                <div class="flex flex-1 items-end justify-between text-sm">
                                                    {{-- Quantity Controls --}}
                                                    <div class="flex items-center gap-2 bg-slate-50 rounded-lg p-1">
                                                        <button @click="updateCartQty(item.id, item.quantity - 1)" class="w-6 h-6 flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:bg-white rounded-md transition-all">-</button>
                                                        <input type="number"
                                                               :value="item.quantity"
                                                               readonly
                                                               class="w-8 text-center bg-transparent border-none text-xs font-bold text-slate-900 p-0 focus:ring-0 cursor-not-allowed"
                                                               title="Use +/- buttons to change quantity">
                                                        <button @click="updateCartQty(item.id, item.quantity + 1)" class="w-6 h-6 flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:bg-white rounded-md transition-all">+</button>
                                                    </div>
                                                    {{-- Remove --}}
                                                    <div class="flex">
                                                        <button @click="removeFromCart(item.id)" type="button" class="font-bold text-rose-500 hover:text-rose-600 transition-colors">Remove</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </template>

                                    {{-- Empty State --}}
                                    <template x-if="cartItems.length === 0">
                                        <div class="text-center py-20">
                                            <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                            </div>
                                            <p class="text-slate-500 font-medium">Your cart is empty</p>
                                            <button @click="isCartOpen = false" class="mt-6 text-indigo-600 font-bold hover:text-indigo-700 transition-colors">Continue Shopping &rarr;</button>
                                        </div>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Cart Footer: Subtotal + Checkout --}}
                    <div x-show="cartItems.length > 0" class="border-t border-slate-100 px-6 py-8 bg-slate-50/50">
                        <div class="flex justify-between text-base font-bold text-slate-900">
                            <p>Subtotal</p>
                            <p x-text="'{{ $settings->currency_icon }}' + cartTotal.toFixed(2)"></p>
                        </div>
                        <p class="mt-0.5 text-sm text-slate-500">Shipping and taxes calculated at checkout.</p>
                        <div class="mt-8">
                            @auth
                            <a href="{{ route('checkout.index') }}" class="flex items-center justify-center rounded-2xl bg-indigo-600 px-6 py-4 text-base font-bold text-white shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all duration-300">Checkout</a>
                        @else
                            <a href="{{ route('login') }}" class="flex items-center justify-center rounded-2xl bg-indigo-600 px-6 py-4 text-base font-bold text-white shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all duration-300">Checkout</a>
                        @endauth
                        </div>
                        <div class="mt-6 flex justify-center text-center text-sm text-slate-500 uppercase tracking-widest font-bold">
                            <p>
                                or
                                <button @click="isCartOpen = false" type="button" class="text-indigo-600 hover:text-indigo-500 ml-1">
                                    Continue Shopping <span aria-hidden="true"> &rarr;</span>
                                </button>
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
