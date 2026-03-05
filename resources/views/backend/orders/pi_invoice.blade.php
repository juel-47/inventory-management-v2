<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>PI Invoice #{{ $order->order_no }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }
        @media screen {
            body {
                background-color: #f0f0f0;
                padding: 40px 0;
            }
            .container {
                max-width: 900px;
                background: #fff;
                box-shadow: 0 0 15px rgba(0,0,0,0.1);
                border-radius: 4px;
                padding: 40px;
            }
            .no-print {
                margin-bottom: 20px;
                display: flex;
                justify-content: flex-end;
            }
            .btn {
                display: inline-block;
                padding: 8px 16px;
                margin-left: 10px;
                border-radius: 4px;
                text-decoration: none;
                font-weight: bold;
                font-size: 13px;
                cursor: pointer;
                border: none;
            }
            .btn-print { background: #ffc107; color: #000; }
            .btn-download { background: #3abaf4; color: #fff; }
            .btn-back { background: #6777ef; color: #fff; }
            .btn-close { background: #6c757d; color: #fff; }
        }
        @media print {
            .no-print { display: none; }
            body { background: #fff; }
            .container { width: 100%; padding: 0; box-shadow: none; }
        }
        .header {
            width: 100%;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
        }
        .company-info {
            text-align: right;
            float: right;
            width: 55%;
        }
        .invoice-title {
            float: left;
            width: 45%;
        }
        .invoice-title h1 {
            margin: 0;
            color: #333;
            font-size: 28px;
            text-transform: uppercase;
        }
        .clearfix:after {
            content: "";
            display: table;
            clear: both;
        }
        .details-box {
            margin-bottom: 30px;
        }
        .box-left {
            float: left;
            width: 48%;
        }
        .box-right {
            float: right;
            width: 48%;
            text-align: right;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #f8f9fa;
            color: #333;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #ddd;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        .total-row td {
            font-weight: bold;
            background-color: #f8f9fa;
            border-top: 2px solid #ddd;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #fff;
        }
        .badge-warning { background-color: #ffc107; color: #000; }
        .badge-info { background-color: #17a2b8; }
        .badge-primary { background-color: #6777ef; }
        .badge-success { background-color: #28a745; }
        .badge-danger { background-color: #dc3545; }
        .text-right { text-align: right; }
        .image-cell {
            text-align: center;
        }
        .image-cell img {
            width: 44px;
            height: 44px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #e5e7eb;
        }
        .image-empty {
            font-size: 11px;
            color: #999;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #777;
            font-size: 12px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    @php
        $status = strtolower((string) $order->status);
        $statusClass = match($status) {
            'pending' => 'badge-warning',
            'approved' => 'badge-info',
            'processing' => 'badge-primary',
            'shipped' => 'badge-primary',
            'completed' => 'badge-success',
            'rejected', 'cancelled' => 'badge-danger',
            default => 'badge-info',
        };
        $currency = $settings->currency_icon ?? '$';
    @endphp

    <div class="container">
        <div class="no-print">
            <button onclick="window.print()" class="btn btn-print">Print Now</button>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-back">Back to List</a>
            <button type="button" onclick="window.close(); if(!window.closed){ window.history.back(); }" class="btn btn-close">Close</button>
        </div>

        <div class="header clearfix">
            <div class="invoice-title">
                <h1>PI Invoice</h1>
                <p><strong>Ref:</strong> #{{ $order->order_no }}</p>
                <div style="margin-top: 10px;">
                    <span class="badge {{ $statusClass }}">{{ ucfirst($order->status) }}</span>
                </div>
            </div>
            <div class="company-info">
                <h3>{{ $settings->site_name ?? 'Inventory Management System' }}</h3>
                <p>
                    {{ $settings->contact_email ?? '' }}<br>
                    {!! nl2br(e($settings->address ?? '')) !!}
                </p>
            </div>
        </div>

        <div class="details-box clearfix">
            <div class="box-left">
                <h4>Customer Details:</h4>
                <p>
                    <strong>{{ $order->billing_name }}</strong><br>
                    Outlet/Shop: {{ $order->billing_outlet_name ?: ($order->user->outlet_name ?? 'N/A') }}<br>
                    Phone: {{ $order->billing_phone }}<br>
                    Email: {{ $order->pi_email ?: $order->billing_email }}
                </p>
            </div>
            <div class="box-right">
                <h4>Order Details:</h4>
                <p>
                    <strong>Date:</strong> {{ $order->created_at?->format('d M, Y h:i A') }}<br>
                    <strong>Source:</strong> {{ $order->shipping_method ?: 'frontend_checkout' }}<br>
                    <strong>Status:</strong> {{ strtoupper($order->status) }}
                </p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 15%;">Image</th>
                    <th style="width: 20%;">Product Information</th>
                    <th style="width: 10%;">Product No</th>
                    <th style="width: 15%;">Category</th>
                    <th style="width: 10%;">Unit</th>
                    <th style="width: 15%;">Variants Ordered</th>
                    <th style="width: 10%;" class="text-right">Total Qty</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $groupedItems = [];
                    foreach($order->items as $item) {
                        $productId = $item->product_id;
                        if (!isset($groupedItems[$productId])) {
                            $groupedItems[$productId] = [
                                'first_item' => $item,
                                'total_qty' => 0,
                                'variants' => []
                            ];
                        }
                        $groupedItems[$productId]['total_qty'] += $item->quantity;
                        $variantName = $item->variant_label ?: 'Standard';
                        
                        if (!isset($groupedItems[$productId]['variants'][$variantName])) {
                            $groupedItems[$productId]['variants'][$variantName] = 0;
                        }
                        $groupedItems[$productId]['variants'][$variantName] += $item->quantity;
                    }
                    $index = 0;
                @endphp

                @foreach($groupedItems as $productId => $group)
                    @php
                        $item = $group['first_item'];
                        $index++;
                        $imagePath = (string) ($item->product_image ?? '');
                        $imageUrl = null;
                        if ($imagePath !== '') {
                            if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://')) {
                                $imageUrl = $imagePath;
                            } elseif (is_file(public_path(ltrim($imagePath, '/')))) {
                                $imageUrl = asset(ltrim($imagePath, '/'));
                            } elseif (str_starts_with($imagePath, 'storage/')) {
                                $imageUrl = asset($imagePath);
                            } else {
                                $imageUrl = asset('storage/' . ltrim($imagePath, '/'));
                            }
                        }
                    @endphp
                    <tr>
                        <td>{{ $index }}</td>
                        <td class="image-cell">
                            @if($imageUrl)
                                <img src="{{ $imageUrl }}" alt="{{ $item->product_name }}">
                            @else
                                <span class="image-empty">No Image</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $item->product_name }}</strong><br>

                            @if($item->product)
                                @if($item->product->slug)
                                    <small><strong>Slug:</strong> {{ $item->product->slug }}</small><br>
                                @endif
                                @if($item->product->brand)
                                    <small><strong>Brand:</strong> {{ $item->product->brand->name }}</small><br>
                                @endif
                                @if($item->product->vendor)
                                    <small><strong>Vendor:</strong> {{ $item->product->vendor->shop_name ?? 'N/A' }}</small><br>
                                @endif
                                @if($item->product->barcode)
                                    <small><strong>Barcode:</strong> {{ $item->product->barcode }}</small><br>
                                @endif
                                @if($item->product->self_number)
                                    <small><strong>Shelf No:</strong> {{ $item->product->self_number }}</small><br>
                                @endif
                                @if($item->product->productType)
                                    <small><strong>Type:</strong> {{ $item->product->productType->name }}</small><br>
                                @endif
                                @if($item->product->custom_label)
                                    <small><strong>Label:</strong> {{ $item->product->custom_label }}</small><br>
                                @endif
                                @if($item->product->long_description)
                                    <div style="font-size: 11px; margin-top: 5px; color: #555;">
                                        <strong>Description:</strong><br>
                                        {!! strip_tags($item->product->long_description) !!}
                                    </div>
                                @endif
                            @else
                                <small>No extra product details.</small>
                            @endif
                        </td>
                        
                        <!-- Product No Column -->
                        <td>
                            @if($item->product && $item->product->product_number)
                                <small>{{ $item->product->product_number }}</small>
                            @else
                                <small>N/A</small>
                            @endif
                        </td>
                        
                        <!-- Category Column -->
                        <td>
                            @if($item->product)
                                <small><strong>Main:</strong> {{ $item->product->category->name ?? 'N/A' }}</small><br>
                                @if($item->product->subCategory)
                                    <small><strong>Sub:</strong> {{ $item->product->subCategory->name }}</small><br>
                                @endif
                                @if($item->product->childCategory)
                                    <small><strong>Child:</strong> {{ $item->product->childCategory->name }}</small><br>
                                @endif
                            @else
                                <small>{{ $item->category_name ?: 'General' }}</small>
                            @endif
                        </td>
                        
                        <!-- Unit Column -->
                        <td>
                            @if($item->product && $item->product->unit)
                                <small>{{ $item->product->unit->name }}</small>
                            @else
                                <small>N/A</small>
                            @endif
                        </td>

                        <!-- Variants Column -->
                        <td>
                            @if(count($group['variants']) > 0)
                                @foreach($group['variants'] as $vName => $vQty)
                                    <div style="margin-bottom: 3px;">
                                        <span class="badge badge-info" style="color: #0f0f0f; font-size: 11px;">{{ $vName }} &times; {{ $vQty }}</span>
                                    </div>
                                @endforeach
                            @else
                                <small>Standard</small>
                            @endif
                        </td>

                        <td class="text-right"><strong>{{ $group['total_qty'] }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($order->ship_different)
            <div style="margin-top: 15px; background: #f8f9fa; padding: 15px; border-left: 4px solid #6777ef;">
                <strong>Shipping Address:</strong><br>
                {{ $order->shipping_name ?: 'N/A' }} |
                {{ $order->shipping_phone ?: 'N/A' }} |
                {{ $order->shipping_email ?: 'N/A' }}<br>
                {{ $order->shipping_address ?: 'N/A' }},
                {{ $order->shipping_city ?: '' }} {{ $order->shipping_state ?: '' }},
                {{ $order->shipping_zip_code ?: '' }},
                {{ $order->shipping_country ?: '' }}
            </div>
        @endif

        <div class="footer">
            <p>Generated by {{ $settings->site_name ?? 'Inventory Management System' }} on {{ now()->format('d M, Y h:i A') }}</p>
        </div>
    </div>
</body>
</html>
