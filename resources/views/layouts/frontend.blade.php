<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Inventory B2B'))</title>
    <link rel="icon" type="image/png" href="{{ asset('uploads/logo.png') }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Assets (Vite) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Outfit', sans-serif; }
        [x-cloak] { display: none !important; }

        /* Global frontend cursor behavior for interactive controls */
        a[href],
        button,
        [role="button"],
        summary,
        label[for],
        input[type="button"],
        input[type="submit"],
        input[type="reset"] {
            cursor: pointer;
        }

        button:disabled,
        [role="button"][aria-disabled="true"],
        input:disabled {
            cursor: not-allowed;
        }
    </style>

    @yield('head')
</head>

<body x-data="globalApp" class="bg-slate-50 text-slate-900 min-h-screen flex flex-col" x-cloak>

    {{-- ── Notifications ─────────────────────────────── --}}
    @include('layouts.partials.frontend-navbar')

    {{-- ── Main Content ──────────────────────────────── --}}
    <main class="flex-1">
        @yield('content')
    </main>

    {{-- ── Cart Drawer ────────────────────────────────── --}}
    @include('layouts.partials.frontend-cart')

    {{-- ── Footer ─────────────────────────────────────── --}}
    @include('layouts.partials.frontend-footer')

    {{-- ── Alpine.js Store & App Logic ─────────────────── --}}
    @include('layouts.partials.frontend-scripts')

    @yield('scripts')

</body>
</html>
