@extends('layouts.frontend')
@section('title', 'Product Request Details')

@section('content')
@php
    $status = strtolower((string) $productRequest->status);
    $currency = $settings->currency_icon ?? '$';
@endphp
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-3xl font-black text-slate-900">Product Request</h1>
            <p class="text-sm text-slate-500 mt-2">Request No: {{ $productRequest->request_no }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('account.index', ['panel' => 'product-requests']) }}" class="inline-flex items-center gap-2 text-indigo-600 font-bold hover:text-indigo-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path></svg>
                Back to Requests
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <section class="bg-white border border-slate-100 rounded-2xl p-6">
                <h2 class="text-lg font-bold text-slate-900 mb-4">Request Info</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-slate-600">
                    <p><span class="font-semibold text-slate-900">Status:</span> {{ ucfirst($productRequest->status) }}</p>
                    <p><span class="font-semibold text-slate-900">Required Days:</span> {{ $productRequest->required_days ?? '—' }}</p>
                    <p><span class="font-semibold text-slate-900">Total Qty:</span> {{ (int) ($productRequest->total_qty ?? 0) }}</p>
                    <p><span class="font-semibold text-slate-900">Total Amount:</span> {{ $currency }}{{ number_format((float) ($productRequest->total_amount ?? 0), 2) }}</p>
                    <p><span class="font-semibold text-slate-900">Date:</span> {{ $productRequest->created_at?->format('d M Y, h:i A') }}</p>
                </div>
            </section>

            <section class="bg-white border border-slate-100 rounded-2xl p-6">
                <h2 class="text-lg font-bold text-slate-900 mb-4">Requested Items</h2>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[680px]">
                        <thead>
                            <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-[0.12em] text-slate-600">
                                <th class="py-3 pr-4 font-black">Product</th>
                                <th class="py-3 px-4 font-black">Variant</th>
                                <th class="py-3 px-4 font-black text-center">Qty</th>
                                <th class="py-3 px-4 font-black text-right">Unit Price</th>
                                <th class="py-3 pl-4 font-black text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($productRequest->items as $item)
                                @php
                                    $variant = $item->variant;
                                    $variantLabel = null;
                                    if ($variant) {
                                        $name = trim((string) ($variant->name ?? ''));
                                        $color = trim((string) ($variant->color?->name ?? $variant->color ?? ''));
                                        $size = trim((string) ($variant->size?->name ?? $variant->size ?? ''));
                                        $parts = array_filter([$name, $color, $size]);
                                        $variantLabel = !empty($parts) ? implode(' / ', array_unique($parts)) : null;
                                    }
                                @endphp
                                <tr class="border-b border-slate-100 text-sm text-slate-700">
                                    <td class="py-3 pr-4 font-semibold text-slate-800">{{ $item->product?->name ?? '—' }}</td>
                                    <td class="py-3 px-4">{{ $variantLabel ?? '—' }}</td>
                                    <td class="py-3 px-4 text-center">{{ (int) ($item->qty ?? 0) }}</td>
                                    <td class="py-3 px-4 text-right">{{ $currency }}{{ number_format((float) ($item->unit_price ?? 0), 2) }}</td>
                                    <td class="py-3 pl-4 text-right font-semibold">{{ $currency }}{{ number_format((float) ($item->subtotal ?? 0), 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-sm text-slate-500">No items found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            @if(!empty($productRequest->note))
                <section class="bg-white border border-slate-100 rounded-2xl p-6">
                    <h2 class="text-lg font-bold text-slate-900 mb-4">Request Note</h2>
                    <p class="text-sm text-slate-600 whitespace-pre-line">{{ $productRequest->note }}</p>
                </section>
            @endif

            @if(!empty($productRequest->admin_note))
                <section class="bg-white border border-slate-100 rounded-2xl p-6">
                    <h2 class="text-lg font-bold text-slate-900 mb-4">Admin Note</h2>
                    <p class="text-sm text-slate-600 whitespace-pre-line">{{ $productRequest->admin_note }}</p>
                </section>
            @endif
        </div>

        <aside class="lg:col-span-1">
            <div class="sticky top-24 space-y-6">
                <div class="bg-white border border-slate-100 rounded-2xl p-6">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Status</h3>
                    <p class="mb-4">
                        <span class="text-xs font-black px-3 py-1 rounded-full
                            {{ $status === 'completed' ? 'bg-emerald-100 text-emerald-700' : '' }}
                            {{ $status === 'cancelled' ? 'bg-rose-100 text-rose-700' : '' }}
                            {{ $status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}">
                            {{ ucfirst($productRequest->status) }}
                        </span>
                    </p>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Total Qty</span>
                            <span class="font-semibold text-slate-900">{{ (int) ($productRequest->total_qty ?? 0) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Total Amount</span>
                            <span class="font-semibold text-slate-900">{{ $currency }}{{ number_format((float) ($productRequest->total_amount ?? 0), 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-slate-100 rounded-2xl p-6">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Customer Info</h3>
                    <div class="space-y-2 text-sm text-slate-600">
                        <p><span class="font-semibold text-slate-900">Name:</span> {{ $productRequest->user?->name ?? '—' }}</p>
                        <p><span class="font-semibold text-slate-900">Email:</span> {{ $productRequest->user?->email ?? '—' }}</p>
                        <p><span class="font-semibold text-slate-900">Phone:</span> {{ $productRequest->user?->phone ?? '—' }}</p>
                        <p><span class="font-semibold text-slate-900">Outlet/Shop:</span> {{ $productRequest->user?->outlet_name ?? '—' }}</p>
                        <p><span class="font-semibold text-slate-900">Address:</span> {{ $productRequest->user?->address ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
