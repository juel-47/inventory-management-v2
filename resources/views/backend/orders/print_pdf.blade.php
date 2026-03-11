<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Invoice - {{ $order->order_no }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 13px; color: #333; line-height: 1.5; }
        .header { margin-bottom: 25px; border-bottom: 2px solid #eee; padding-bottom: 15px; }
        .left { width: 45%; float: left; }
        .right { width: 50%; float: right; text-align: right; }
        .clearfix:after { content: ""; display: table; clear: both; }
        h1 { margin: 0; font-size: 24px; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #f9f9f9; text-align: left; padding: 10px; border-bottom: 2px solid #eee; text-transform: uppercase; font-size: 11px; color: #777; }
        td { padding: 10px; border-bottom: 1px solid #eee; vertical-align: top; }
        .text-right { text-align: right; }
        .total-row td { font-weight: bold; background-color: #f8f9fa; border-top: 2px solid #ddd; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #999; padding: 16px 0; border-top: 1px solid #eee; }
        .box { margin-top: 10px; }
        .image-cell { text-align: center; }
        .image-cell img { width: 36px; height: 36px; object-fit: cover; border-radius: 3px; border: 1px solid #ddd; }
        .image-empty { font-size: 10px; color: #999; }
    </style>
</head>
<body>
    @php
        $currency = $settings->currency_icon ?? '$';
        $logoPath = optional($settings)->site_logo ?: 'uploads/logo.png';
        $logoFullPath = public_path(ltrim($logoPath, '/'));
        $logoData = null;
        if (is_file($logoFullPath)) {
            $ext = strtolower(pathinfo($logoFullPath, PATHINFO_EXTENSION) ?: 'png');
            $mime = in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp'], true) ? $ext : 'png';
            $logoData = 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($logoFullPath));
        }
    @endphp

    <div class="header clearfix">
        <div class="left">
            <h1>OUTLET/SHOP ORDER</h1>
            <div>Ref: #{{ $order->order_no }}</div>
            <div>Status: {{ strtoupper($order->status) }}</div>
        </div>
        <div class="right">
            @if($logoData)
                <div style="margin-bottom: 6px;">
                    <img src="{{ $logoData }}" alt="Logo" style="height: 40px; max-width: 160px; object-fit: contain;">
                </div>
            @endif
            <div style="font-size: 16px; font-weight: bold;">{{ $settings->site_name ?? 'Inventory Management System' }}</div>
            <div style="font-size: 11px; color: #666;">
                {{ $settings->contact_email ?? '' }}<br>
                {!! nl2br(e($settings->address ?? '')) !!}
            </div>
        </div>
    </div>

    <div class="clearfix box">
        <div class="left">
            <div style="font-weight: bold;">Customer Details:</div>
            <div>{{ $order->billing_name }}</div>
            <div>{{ $order->billing_phone }}</div>
            <div>{{ $order->billing_email }}</div>
            <div>{{ $order->billing_outlet_name ?: ($order->user->outlet_name ?? 'N/A') }}</div>
        </div>
        <div class="right">
            <div style="font-weight: bold;">Order Details:</div>
            <div>Date: {{ $order->created_at?->format('d M, Y h:i A') }}</div>
            <div>Source: {{ $order->shipping_method ?: 'frontend_checkout' }}</div>
            <div>Ship Different: {{ $order->ship_different ? 'YES' : 'NO' }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="12%">Image</th>
                <th width="31%">Product</th>
                <th width="18%">Variant</th>
                <th width="10%" class="text-right">Qty</th>
                <th width="12%" class="text-right">Unit</th>
                <th width="15%" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $index => $item)
                @php
                    $imagePath = (string) ($item->product_image ?? '');
                    $base64 = null;
                    if ($imagePath !== '' && (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://'))) {
                        $remoteData = @file_get_contents($imagePath);
                        if ($remoteData !== false) {
                            $pathPart = parse_url($imagePath, PHP_URL_PATH) ?: '';
                            $ext = strtolower(pathinfo($pathPart, PATHINFO_EXTENSION) ?: 'jpg');
                            $mime = in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp'], true) ? $ext : 'jpeg';
                            $base64 = 'data:image/' . $mime . ';base64,' . base64_encode($remoteData);
                        }
                    }

                    if ($base64 === null && $imagePath !== '' && !str_starts_with($imagePath, 'http://') && !str_starts_with($imagePath, 'https://')) {
                        $normalized = ltrim($imagePath, '/');
                        $candidates = [
                            public_path($normalized),
                            public_path('storage/' . ltrim(str_replace('storage/', '', $normalized), '/')),
                            storage_path('app/public/' . ltrim(str_replace('storage/', '', $normalized), '/')),
                        ];
                        foreach ($candidates as $candidate) {
                            if (is_file($candidate)) {
                                $ext = strtolower(pathinfo($candidate, PATHINFO_EXTENSION) ?: 'jpg');
                                $mime = in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp'], true) ? $ext : 'jpeg';
                                $base64 = 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($candidate));
                                break;
                            }
                        }
                    }
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="image-cell">
                        @if($base64)
                            <img src="{{ $base64 }}" alt="">
                        @else
                            <span class="image-empty">No Image</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight: bold;">{{ $item->product_name }}</div>
                        <div style="font-size: 11px; color: #777;">{{ $item->category_name ?: 'General' }}</div>
                    </td>
                    <td>{{ $item->variant_label ?: 'Standard' }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">{{ $currency }}{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ $currency }}{{ number_format($item->line_total, 2) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="6" class="text-right">Subtotal</td>
                <td class="text-right">{{ $currency }}{{ number_format($order->subtotal_amount, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="6" class="text-right">{{ $order->tax_label ?: 'VAT / Tax' }}</td>
                <td class="text-right">{{ $currency }}{{ number_format($order->tax_amount, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="6" class="text-right">Discount</td>
                <td class="text-right">-{{ $currency }}{{ number_format($order->discount_amount, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="6" class="text-right">Grand Total</td>
                <td class="text-right">{{ $currency }}{{ number_format($order->total_amount, 2) }}</td>
            </tr>
            <tr>
                <td colspan="6" style="text-align: right; border: none; padding: 4px 10px; font-size: 12px;">PAID TOTAL</td>
                <td style="text-align: right; border-bottom: 1px solid #ddd; color: #28a745; font-weight: bold; padding: 4px 10px; font-size: 12px;">{{ $currency }}{{ number_format($order->paid_amount, 2) }}</td>
            </tr>
            <tr>
                <td colspan="6" style="text-align: right; border: none; font-weight: bold; padding: 6px 10px; font-size: 14px;">DUE BALANCE</td>
                <td style="text-align: right; font-weight: bold; color: {{ $order->due_amount > 0 ? '#dc3545' : '#28a745' }}; font-size: 14px; padding: 6px 10px;">{{ $currency }}{{ number_format($order->due_amount, 2) }}</td>
            </tr>

        </tbody>
    </table>

    {{-- @if($hasSavedPiInfo)
        @include('backend.pi._packing_table', [
            'piInfo' => $piInfo,
            'piTotals' => $piTotals,
        ])
    @endif --}}

    @if($order->ship_different)
        <div style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-left: 3px solid #6777ef;">
            <div style="font-weight: bold; font-size: 11px;">Shipping Info:</div>
            <div style="font-size: 11px;">
                {{ $order->shipping_name ?: 'N/A' }},
                {{ $order->shipping_phone ?: 'N/A' }},
                {{ $order->shipping_email ?: 'N/A' }}<br>
                {{ $order->shipping_address ?: 'N/A' }},
                {{ $order->shipping_city ?: '' }} {{ $order->shipping_state ?: '' }},
                {{ $order->shipping_zip_code ?: '' }},
                {{ $order->shipping_country ?: '' }}
            </div>
        </div>
    @endif

    <div class="footer">
        Generated on {{ now()->format('d M, Y h:i A') }} | {{ $settings->site_name ?? 'Inventory Management System' }}
    </div>
</body>
</html>
