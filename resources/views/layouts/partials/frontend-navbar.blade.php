{{-- =====================================================
     GLOBAL ALERT / NOTIFICATION SYSTEM
     ===================================================== --}}
<div class="fixed top-4 right-4 z-[9999] flex flex-col gap-2 pointer-events-none">
    <template x-for="note in notifications" :key="note.id">
        <div x-show="note.show"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="translate-x-full opacity-0"
             x-transition:enter-end="translate-x-0 opacity-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-x-0 opacity-100"
             x-transition:leave-end="translate-x-full opacity-0"
             class="pointer-events-auto bg-white border-l-4 rounded-lg shadow-xl p-4 min-w-[300px] flex items-start gap-3"
             :class="{
                'border-indigo-600': note.type === 'success',
                'border-amber-500': note.type === 'warning',
                'border-rose-500': note.type === 'error'
             }">
            <div class="shrink-0 mt-0.5">
                <template x-if="note.type === 'success'">
                    <svg class="h-5 w-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                </template>
                <template x-if="note.type === 'warning'">
                    <svg class="h-5 w-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                </template>
            </div>
            <div class="flex-1">
                <p class="text-sm font-medium text-slate-800" x-text="note.message"></p>
            </div>
            <button @click="hideNotification(note.id)" class="text-slate-400 hover:text-slate-600">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    </template>
</div>

{{-- =====================================================
     NAVIGATION BAR
     ===================================================== --}}
<nav class="bg-white/90 backdrop-blur-xl border-b border-slate-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">

            {{-- Logo & Site Name --}}
            <div class="flex items-center">
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <div class="w-12 h-12 overflow-hidden rounded-xl border border-slate-100 shadow-sm flex items-center justify-center p-1 bg-white group-hover:border-indigo-200 transition-all duration-300">
                        <img src="{{ asset('uploads/logo.png') }}" alt="{{ config('app.name') }}" class="w-full h-full object-contain">
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xl font-bold text-slate-900 leading-none group-hover:text-indigo-600 transition-colors">{{ config('app.name', 'Inventory B2B') }}</span>
                        <span class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest mt-1">Global Trade Hub</span>
                    </div>
                </a>
            </div>

            {{-- Navigation Links --}}
            <div class="hidden md:flex items-center gap-8">
                <a href="{{ route('shop') }}" class="text-sm font-semibold text-slate-600 hover:text-indigo-600 transition-colors">B2B Shop</a>
                <a href="#" class="text-sm font-semibold text-slate-600 hover:text-indigo-600 transition-colors">Blog</a>
                <a href="#" class="text-sm font-semibold text-slate-600 hover:text-indigo-600 transition-colors">Contact</a>
                <a href="#" class="text-sm font-semibold text-slate-600 hover:text-indigo-600 transition-colors">About Us</a>
            </div>

            {{-- Right Icons --}}
            <div class="flex items-center gap-2 sm:gap-4">
                {{-- Search --}}
                <button class="p-2.5 rounded-xl text-slate-500 hover:bg-slate-100 hover:text-indigo-600 transition-all duration-300">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </button>

                {{-- Wishlist --}}
                <button class="p-2.5 rounded-xl text-slate-500 hover:bg-slate-100 hover:text-rose-500 transition-all duration-300 relative">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                </button>

                {{-- Cart --}}
                <button @click="isCartOpen = true" class="p-2.5 rounded-xl text-slate-500 hover:bg-slate-100 hover:text-indigo-600 transition-all duration-300 relative group">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    <span x-show="cartCount > 0" x-text="cartCount" class="absolute top-1.5 right-1.5 bg-rose-500 text-white text-[10px] font-bold h-5 w-5 rounded-full border-2 border-white flex items-center justify-center"></span>
                </button>

                <div class="h-8 w-px bg-slate-200 mx-2 hidden sm:block"></div>

                {{-- User Menu --}}
                @auth
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center gap-2 p-1 pl-2 rounded-full hover:bg-slate-100 transition-all duration-300 group">
                            <span class="text-sm font-bold text-slate-700 hidden lg:block">{{ Auth::user()->name }}</span>
                            <div class="w-9 h-9 bg-indigo-600 rounded-full flex items-center justify-center text-white font-bold shadow-lg shadow-indigo-100">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                        </button>
                        <div x-show="open" @click.away="open = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             class="absolute right-0 mt-3 w-56 bg-white border border-slate-200 rounded-2xl shadow-2xl py-2 z-50 overflow-hidden" x-cloak>
                            <div class="px-4 py-3 border-b border-slate-50">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Signed in as</p>
                                <p class="text-sm font-bold text-slate-900 truncate">{{ Auth::user()->name }}</p>
                            </div>
                            @if(Auth::user()->hasRole('Admin'))
                                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-50 hover:text-indigo-600 border-t border-slate-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                    Admin Dashboard
                                </a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="p-2.5 rounded-xl text-slate-500 hover:bg-slate-100 hover:text-indigo-600 transition-all duration-300 group flex items-center gap-2">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        <span class="text-sm font-bold hidden sm:block">Sign In</span>
                    </a>
                @endauth
            </div>

        </div>
    </div>
</nav>
