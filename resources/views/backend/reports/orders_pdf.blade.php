<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $settings->site_name ?? 'Inventory' }} — Order & Issue Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #222; line-height: 1.6; padding: 20px; }

        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #1a73e8; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #1a73e8; font-size: 20px; }
        .header .sub { font-size: 14px; font-weight: bold; margin-top: 3px; }
        .header .info { font-size: 10px; color: #666; margin-top: 4px; }

        .profile-box { background: #f5f7fa; border: 1px solid #dde1e6; padding: 10px 14px; margin-bottom: 12px; border-radius: 4px; }
        .profile-box .name { font-size: 15px; font-weight: bold; color: #1a73e8; }
        .profile-box .details { font-size: 11px; color: #555; margin-top: 3px; }

        .summary-row { text-align: center; margin-bottom: 12px; }
        .summary-box { display: inline-block; width: 11.5%; padding: 6px 3px; margin: 0 1px; border: 1px solid #dde1e6; border-radius: 4px; background: #fafbfc; vertical-align: top; }
        .summary-box .lbl { font-size: 8px; color: #666; text-transform: uppercase; }
        .summary-box .val { font-size: 13px; font-weight: bold; color: #1a73e8; margin-top: 2px; }

        .section-title { font-size: 13px; font-weight: bold; margin: 12px 0 5px; padding: 5px 0; border-bottom: 1px solid #ccc; color: #333; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        table th { background: #1a73e8; color: #fff; border: 1px solid #1a73e8; padding: 5px 6px; text-align: left; font-size: 12px; }
        table td { border: 1px solid #dde1e6; padding: 4px 6px; vertical-align: middle; font-size: 16px; }
        table tr:nth-child(even) td { background: #f8f9fa; }
        .tc { text-align: center; }
        .tr { text-align: right; }

        .pct-bar { display: inline-block; height: 14px; border-radius: 7px; background: #e9ecef; width: 100px; vertical-align: middle; }
        .pct-fill { height: 14px; border-radius: 7px; background: #28a745; }

        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #eee; padding-top: 8px; }
        .page-break { page-break-before: always; }
    </style>
</head>

<body>

    <div class="header">
        <h1>{{ $settings->site_name ?? 'Inventory Management System' }}</h1>
        <div class="sub">Order & Issue Report</div>
        <div class="info">
            Generated: {{ date('d M Y, h:i A') }}
            @php $req = $request ?? []; @endphp
            @if (is_array($req) ? !empty($req['date_from']) : $req->filled('date_from'))
                | From: {{ is_array($req) ? $req['date_from'] : $req->date_from }}
            @endif
            @if (is_array($req) ? !empty($req['date_to']) : $req->filled('date_to'))
                | To: {{ is_array($req) ? $req['date_to'] : $req->date_to }}
            @endif
            @if (is_array($req) ? !empty($req['month']) : $req->filled('month'))
                | Month: {{ date('F', mktime(0,0,0, is_array($req) ? $req['month'] : $req->month, 1)) }}
            @endif
            @if (is_array($req) ? !empty($req['year']) : $req->filled('year'))
                | Year: {{ is_array($req) ? $req['year'] : $req->year }}
            @endif
        </div>
    </div>

    @isset($user)
        {{-- ═══════ 360° PER-USER PDF ═══════ --}}
        <div class="profile-box">
            <div class="name">{{ $user->name }} @if($user->outlet_name) ({{ $user->outlet_name }}) @endif</div>
            <div class="details">
                {{ $user->email ?? '—' }} | {{ $user->phone ?? '—' }} | {{ $user->address ?? '—' }}
                | Member since {{ $user->created_at->format('M Y') }}
                @if($user->discount_type) | Discount: {{ $user->discount_value }}{{ $user->discount_type === 'percent' ? '%' : ' Flat' }} @endif
            </div>
        </div>

        <div class="summary-row">
            <div class="summary-box"><div class="lbl">Orders</div><div class="val">{{ number_format($summary->total_orders) }}</div></div>
            <div class="summary-box"><div class="lbl">Order Value</div><div class="val" style="color:#28a745">{!! formatConverted($summary->total_value) !!}</div></div>
            {{-- <div class="summary-box"><div class="lbl">Issues</div><div class="val" style="color:#e67e22">{{ number_format($issueStats->total_issues) }}</div></div>
            <div class="summary-box"><div class="lbl">Issue Qty</div><div class="val" style="color:#3498db">{{ number_format($issueStats->total_issued_qty) }}</div></div>
            <div class="summary-box"><div class="lbl">Issue Value</div><div class="val" style="color:#2c3e50">{!! formatConverted($issueValue) !!}</div></div> --}}
            <div class="summary-box"><div class="lbl">Paid</div><div class="val" style="color:#7f8c8d">{!! formatConverted($paymentStats->total_paid) !!}</div></div>
            <div class="summary-box"><div class="lbl">Due</div><div class="val" style="color:#e74c3c">{!! formatConverted($totalDue) !!}</div></div>
            <div class="summary-box"><div class="lbl">Pending</div><div class="val" style="color:#8e44ad">{!! formatConverted($pendingValue) !!}</div></div>
        </div>

        {{-- Orders --}}
        <div class="section-title">Orders ({{ $orders->count() }})</div>
        <table>
            <thead><tr>
                <th>#</th><th>Order No</th><th>Date</th><th class="tc">Items</th><th class="tr">Total</th><th class="tr">Paid</th><th class="tr">Due</th><th>Status</th>
            </tr></thead>
            <tbody>
                @forelse($orders as $i => $o)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $o->order_no }}</td>
                        <td>{{ $o->placed_at->format('d M Y') }}</td>
                        <td class="tc">{{ $o->items->count() }}</td>
                        <td class="tr">{!! formatConverted($o->total_amount) !!}</td>
                        <td class="tr">{!! formatConverted($o->paid_amount) !!}</td>
                        <td class="tr">{!! formatConverted($o->due_amount) !!}</td>
                        <td>{{ ucfirst($o->payment_status) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="tc" style="color:#999">No orders.</td></tr>
                @endforelse
            </tbody>
        </table>

        {{-- Issues --}}{{-- 
        <div class="section-title">Issues ({{ $issues->count() }})</div>
        <table>
            <thead><tr>
                <th>#</th><th>Issue No</th><th>Date</th><th>Order</th><th class="tc">Items</th><th class="tc">Qty</th><th class="tr">Value</th>
            </tr></thead>
            <tbody>
                @forelse($issues as $i => $issue)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $issue->issue_no }}</td>
                        <td>{{ $issue->created_at->format('d M Y') }}</td>
                        <td>{{ optional($issue->order)->order_no ?? '—' }}</td>
                        <td class="tc">{{ $issue->items->count() }}</td>
                        <td class="tc">{{ number_format($issue->total_qty) }}</td>
                        <td class="tr">{!! formatConverted($issue->computed_value) !!}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="tc" style="color:#999">No issues.</td></tr>
                @endforelse
            </tbody>
        </table> --}}

        {{-- Payments --}}
        <div class="section-title">Payments ({{ $payments->count() }})</div>
        <table>
            <thead><tr>
                <th>#</th><th>Date</th><th>Order</th><th>Method</th><th>Transaction</th><th class="tr">Amount</th>
            </tr></thead>
            <tbody>
                @forelse($payments as $i => $p)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $p->created_at->format('d M Y') }}</td>
                        <td>{{ $p->order->order_no ?? '—' }}</td>
                        <td>{{ $p->payment_method ?? '—' }}</td>
                        <td>{{ $p->transaction_id ?? '—' }}</td>
                        <td class="tr">{!! formatConverted($p->amount) !!}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="tc" style="color:#999">No payments.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="page-break"></div>

        {{-- Product Comparison --}}{{-- 
        <div class="section-title">Product Comparison — Ordered vs Issued</div>
        <table>
            <thead><tr>
                <th>#</th><th>Product</th><th class="tc">Ordered Qty</th><th class="tr">Ordered Value</th>
                <th class="tc">Issued Qty</th><th class="tc">Pending</th><th>Fulfillment</th>
            </tr></thead>
            <tbody>
                @forelse($productComparison as $i => $pc)
                    @php $pct = $pc->ordered_qty > 0 ? round(($pc->issued_qty / $pc->ordered_qty) * 100) : 0; @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $pc->product_name }}</td>
                        <td class="tc">{{ number_format($pc->ordered_qty) }}</td>
                        <td class="tr">{!! formatConverted($pc->ordered_value) !!}</td>
                        <td class="tc">{{ number_format($pc->issued_qty) }}</td>
                        <td class="tc">{{ number_format($pc->pending_qty) }}</td>
                        <td>
                            <span class="pct-bar"><span class="pct-fill" style="width:{{ $pct }}%"></span></span>
                            {{ $pct }}%
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="tc" style="color:#999">No products.</td></tr>
                @endforelse
            </tbody>
        </table> --}}

        {{-- Monthly Trend --}}
        <div class="section-title">Monthly Trend</div>
        <table>
            <thead><tr>
                <th>Month</th><th class="tc">Orders</th><th class="tr">Value</th>{{-- <th class="tc">Issue Qty</th><th class="tc">Products</th> --}}
            </tr></thead>
            <tbody>
                @forelse($monthlyTrend as $t)
                    <tr>
                        <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $t->month)->format('M Y') }}</td>
                        <td class="tc">{{ number_format($t->orders_count) }}</td>
                        <td class="tr">{!! formatConverted($t->total_amount) !!}</td>
                        {{-- <td class="tc">{{ number_format($t->issue_qty) }}</td>
                        <td class="tc">{{ number_format($t->unique_products) }}</td> --}}
                    </tr>
                @empty
                    <tr><td colspan="3" class="tc" style="color:#999">No data.</td></tr>
                @endforelse
            </tbody>
        </table>

    @else
        {{-- ═══════ GLOBAL PDF ═══════ --}}
        <div class="summary-row">
            <div class="summary-box"><div class="lbl">Orders</div><div class="val">{{ number_format($summary->total_orders) }}</div></div>
            <div class="summary-box"><div class="lbl">Total Amount</div><div class="val" style="color:#28a745">{!! formatConverted($totalRevenue) !!}</div></div>
            {{-- <div class="summary-box"><div class="lbl">Issue Value</div><div class="val" style="color:#28a745">{!! formatConverted($issueValue) !!}</div></div>
            <div class="summary-box"><div class="lbl">Issues</div><div class="val" style="color:#e67e22">{{ number_format($issueStats->total_issues) }}</div></div>
            <div class="summary-box"><div class="lbl">Issue Qty</div><div class="val" style="color:#3498db">{{ number_format($issueStats->total_issued_qty) }}</div></div> --}}
        </div>

        <div class="section-title">Product Frequency</div>
        <table>
            <thead><tr>
                <th>#</th><th>Product Name</th><th class="tc">Times Ordered</th><th class="tc">Total Qty</th><th class="tr">Total Value</th>
            </tr></thead>
            <tbody>
                @forelse($productFrequency as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $item->product_name }}</td>
                        <td class="tc">{{ number_format($item->times_ordered) }}</td>
                        <td class="tc">{{ number_format($item->total_qty) }}</td>
                        <td class="tr">{!! formatConverted($item->total_value) !!}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="tc" style="color:#999">No data.</td></tr>
                @endforelse
            </tbody>
        </table>

        {{-- User Summary --}}{{-- 
        <div class="section-title">User Summary</div>
        <table>
            <thead><tr>
                <th>#</th><th>User / Outlet</th><th class="tc">Issues</th><th class="tr">Issue Value</th><th class="tc">Issue Qty</th>
            </tr></thead>
            <tbody>
                @forelse($userSummary as $userId => $usr)
                    @php
                        $userName = optional(\App\Models\User::find($userId))->name ?? 'User #'.$userId;
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $userName }}</td>
                        <td class="tc">{{ number_format($usr->total_orders) }}</td>
                        <td class="tr">{!! formatConverted($usr->total_value) !!}</td>
                        <td class="tc">{{ number_format($usr->total_qty ?? 0) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="tc" style="color:#999">No data.</td></tr>
                @endforelse
            </tbody>
        </table> --}}
    @endisset

    <div class="footer">
        {{ $settings->site_name ?? 'Inventory Management System' }} | {{ date('Y') }}
    </div>

</body>
</html>
