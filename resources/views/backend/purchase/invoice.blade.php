<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Purchase Invoice #{{ $purchase->invoice_no }}</title>
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

        /* Web View Styling */
        @media screen {
            body {
                background-color: #f0f0f0;
                padding: 40px 0;
            }

            .container {
                max-width: 800px;
                background: #fff;
                box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
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

            .btn-print {
                background: #ffc107;
                color: #000;
            }

            .btn-download {
                background: #007bff;
                color: #fff;
            }

            .btn-back {
                background: #6c757d;
                color: #fff;
            }
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                background: #fff;
            }

            .container {
                width: 100%;
                padding: 0;
                box-shadow: none;
            }
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
            width: 60%;
        }

        .invoice-title {
            float: left;
            width: 40%;
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

        .table-responsive {
            width: 100%;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
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
        }

        .total-row td {
            font-weight: bold;
            background-color: #f8f9fa;
            border-top: 2px solid #ddd;
        }

        .variant-tag {
            display: inline-block;
            padding: 2px 6px;
            background: #eee;
            border-radius: 3px;
            font-size: 11px;
            margin-right: 5px;
            margin-top: 3px;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            color: #777;
            font-size: 12px;
            border-top: 1px solid #eee;
            padding-top: 20px;
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

        .badge-success {
            background-color: #28a745;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="no-print">
            <button onclick="window.print()" class="btn btn-print">Print Now</button>
            <a href="{{ route('admin.purchases.download-pdf', $purchase->id) }}" class="btn btn-download">Download
                PDF</a>
            <button onclick="window.close()" class="btn btn-back">Close</button>
        </div>

        <div class="header clearfix">
            <div class="invoice-title">
                <h1>ORDER RECEIVE</h1>
                <p><strong>Invoice:</strong> #{{ $purchase->invoice_no }}</p>
                <div style="margin-top: 10px;">
                    <span class="badge badge-success">Received</span>
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
                <h4>Vendor Details:</h4>
                <p>
                    <strong>{{ $purchase->vendor->shop_name }}</strong><br>
                    Phone: {{ $purchase->vendor->phone }}<br>
                    Address: {{ $purchase->vendor->address }}
                </p>
            </div>
            <div class="box-right">
                <h4>Purchase Details:</h4>
                <p>
                    <strong>Date:</strong> {{ \Carbon\Carbon::parse($purchase->date)->format('d M, Y') }}<br>
                    <strong>Shipping Method:</strong> {{ $purchase->shipping_method ?? 'N/A' }}<br>
                    <strong>Received By:</strong> {{ $purchase->user->name }}
                </p>
            </div>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 10%; text-align: center;">Image</th>
                        <th style="width: 45%;">Product Details</th>
                        <th style="width: 15%; text-align: center;">Qty</th>
                        <th style="width: 15%; text-align: right;">Unit Cost</th>
                        <th style="width: 10%; text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalQty = 0; @endphp
                    @foreach ($purchase->details as $index => $detail)
                        @php $totalQty += $detail->qty; @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td style="text-align: center;">
                                @if ($detail->product && $detail->product->thumb_image)
                                    <img src="{{ asset('storage/' . $detail->product->thumb_image) }}"
                                        style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;">
                                @else
                                    <div
                                        style="width: 50px; height: 50px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center; color: #ccc; font-size: 10px;">
                                        N/A</div>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $detail->product->name }}</strong>
                                @if ($detail->variant_info)
                                    <div style="margin-top: 5px;">
                                        @foreach ($detail->variant_info as $name => $qty)
                                            <span class="variant-tag">{{ $name }}: {{ $qty }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td style="text-align: center;">{{ (float) $detail->qty }}</td>
                            <td style="text-align: right;">{{ $settings->currency_icon }}{{ number_format($detail->unit_cost, 2) }}</td>
                            <td style="text-align: right;">{{ $settings->currency_icon }}{{ number_format($detail->total, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="3" style="text-align: right;">GRAND TOTAL (LOCAL)</td>
                        <td style="text-align: center;">{{ (float) $totalQty }}</td>
                        <td></td>
                        <td style="text-align: right;">{{ $settings->currency_icon }}{{ number_format($purchase->total_amount, 2) }}</td>
                    </tr>
                    @if($purchase->vendor)
                        @php
                            $vendorSubtotal = $purchase->details->sum(function($d) { 
                                return $d->unit_cost_vendor * $d->qty; 
                            });
                        @endphp
                        @if($vendorSubtotal > 0)
                            <tr style="background-color: #f0f9f4;">
                                <td colspan="3" style="text-align: right; color: #28a745; font-weight: bold;">VENDOR TOTAL</td>
                                <td style="text-align: center;"></td>
                                <td></td>
                                <td style="text-align: right; color: #28a745; font-weight: bold;">{{ $purchase->vendor->currency_icon }}{{ number_format($vendorSubtotal, 2) }}</td>
                            </tr>
                        @endif
                    @endif
                </tbody>
            </table>
        </div>

        @if ($purchase->note)
            <div style="margin-bottom: 30px; background: #f8f9fa; padding: 15px; border-left: 4px solid #ddd;">
                <strong>Note/Reference:</strong><br>
                {{ $purchase->note }}
            </div>
        @endif

        <div class="footer">
            <p>Generated by {{ $settings->site_name ?? 'Inventory Management System' }} on {{ date('d M, Y h:i A') }}
            </p>
        </div>
    </div>
</body>

</html>
