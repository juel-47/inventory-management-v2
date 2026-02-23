@extends('layouts.frontend')

@section('title', 'Login — ' . config('app.name', 'Inventory B2B'))

@section('content')
<div class="min-h-[calc(100vh-10rem)] flex items-center justify-center py-12 px-4 bg-slate-50">
    <div class="max-w-md w-full">

        {{-- Header --}}
        <div class="text-center mb-10">
            <div class="w-20 h-20 bg-white rounded-3xl shadow-xl flex items-center justify-center mx-auto mb-6 border border-slate-100">
                <img src="{{ asset('uploads/logo.png') }}" alt="{{ config('app.name') }}" class="w-12 h-12 object-contain">
            </div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Welcome Back</h1>
            <p class="text-slate-500 font-medium mt-2">Sign in to your B2B account</p>
        </div>

        {{-- Login Card --}}
        <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 overflow-hidden">
            <div class="p-10">

                {{-- Error --}}
                @if(session('error'))
                    <div class="bg-rose-50 border border-rose-100 text-rose-600 px-4 py-3 rounded-2xl mb-8 flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                        <span class="text-sm font-bold">{{ session('error') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    {{-- Email --}}
                    <div class="space-y-2">
                        <label for="email" class="text-xs font-black text-slate-400 uppercase tracking-widest pl-1">Email Address</label>
                        <div class="relative group">
                            <input type="email" id="email" name="email" required autofocus
                                   value="{{ old('email') }}"
                                   class="w-full bg-slate-50 border-2 border-transparent focus:border-indigo-500 focus:bg-white rounded-2xl px-5 py-4 outline-none transition-all font-bold text-slate-700 placeholder-slate-300"
                                   placeholder="outlet@merchant.com">
                            <div class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"></path></svg>
                            </div>
                        </div>
                        @error('email')
                            <p class="text-rose-500 text-[10px] font-bold uppercase tracking-widest pl-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between pl-1">
                            <label for="password" class="text-xs font-black text-slate-400 uppercase tracking-widest">Password</label>
                            <a href="{{ route('password.request') }}" class="text-[10px] font-black text-indigo-500 uppercase tracking-widest hover:text-indigo-700">Forgot?</a>
                        </div>
                        <div class="relative group">
                            <input type="password" id="password" name="password" required
                                   class="w-full bg-slate-50 border-2 border-transparent focus:border-indigo-500 focus:bg-white rounded-2xl px-5 py-4 outline-none transition-all font-bold text-slate-700 placeholder-slate-300"
                                   placeholder="••••••••">
                            <div class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </div>
                        </div>
                        @error('password')
                            <p class="text-rose-500 text-[10px] font-bold uppercase tracking-widest pl-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Remember Me --}}
                    <div class="flex items-center gap-3 px-1">
                        <input type="checkbox" name="remember" id="remember" class="w-5 h-5 rounded-lg border-2 border-slate-200 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                        <label for="remember" class="text-sm font-bold text-slate-500 cursor-pointer select-none">Stay logged in</label>
                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="w-full bg-indigo-600 text-white font-black py-4 rounded-2xl hover:bg-indigo-700 hover:-translate-y-0.5 shadow-lg shadow-indigo-100 active:translate-y-0 transition-all duration-300">
                        Sign In
                    </button>
                </form>
            </div>

            {{-- Admin Portal Link --}}
            <a href="{{ route('admin.login') }}" class="block p-6 bg-slate-50 border-t border-slate-100 text-center group">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] group-hover:text-indigo-500 transition-colors">Administrative Staff Access Portal &rarr;</span>
            </a>
        </div>

    </div>
</div>
@endsection
