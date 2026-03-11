{{-- Footer --}}
<footer class="bg-white border-t border-slate-200 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <div
                class="mx-auto mb-1.5 h-10 w-10 rounded-xl border border-slate-200 bg-white shadow-sm flex items-center justify-center overflow-hidden">
                <img src="{{ asset(optional($settings)->site_logo ?: 'uploads/logo.png') }}"
                    alt="{{ optional($settings)->site_name ?? config('app.name', 'Inventory') }}"
                    class="h-7 w-7 object-contain">
            </div>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-center sm:text-left">
            <div class="text-slate-600 text-sm font-semibold">
                Ideation &amp; Design Shahadat
            </div>
            <div class="text-center">
                <div class="text-slate-800 text-sm font-semibold">
                     {{ date('Y') }} &copy;
                    {{ optional($settings)->site_name ?? config('app.name', 'Inventory Management System') }}
                </div>
            </div>
            <div class="text-slate-600 text-sm font-semibold">
                Developed By <a href="https://inoodex.com/" target="_blank" rel="noopener noreferrer"
                    class="text-slate-700 hover:text-indigo-600 font-semibold">Inoodex</a>
            </div>
        </div>
    </div>
</footer>
