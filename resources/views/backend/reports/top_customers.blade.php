@extends('backend.layouts.master')
@section('title', 'Top Customers')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1><i class="fas fa-crown mr-2 text-warning"></i>Top Customers</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item active"><a href="{{ route('admin.reports.index') }}">Reports</a></div>
                <div class="breadcrumb-item">Top Customers</div>
            </div>
        </div>

        <div class="section-body">

            {{-- Summary Cards --}}
            <div class="row mb-4">
                <div class="col-lg-4 col-md-4 col-sm-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-info">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Total Customers</h4></div>
                            <div class="card-body">{{ number_format($grandTotals->total_customers ?? 0) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-warning">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Total Orders</h4></div>
                            <div class="card-body">{{ number_format($grandTotals->grand_total_orders ?? 0) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-success">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Grand Total Value</h4></div>
                            <div class="card-body">{!! formatWithCurrency($grandTotals->grand_total_value ?? 0) !!}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Table Card --}}
            <div class="row">
                <div class="col-12">
                    <div class="card border shadow-sm">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap" style="gap:10px;">
                            <h4 class="mb-0"><i class="fas fa-crown mr-2 text-warning"></i>All Customers by Order Value</h4>
                            <div class="d-flex align-items-center" style="gap:8px;">
                                <form method="GET" action="{{ route('admin.reports.top-customers') }}" class="d-flex" style="gap:6px;">
                                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name / outlet / email..." value="{{ request('search') }}" style="min-width:240px;">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
                                    @if(request('search'))
                                        <a href="{{ route('admin.reports.top-customers') }}" class="btn btn-danger btn-sm"><i class="fas fa-times"></i> Clear</a>
                                    @endif
                                </form>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            @if($customers->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="bg-whitesmoke">
                                            <tr>
                                                <th class="pl-4" style="width:50px;">#</th>
                                                <th>User / Outlet</th>
                                                <th>Email</th>
                                                <th class="text-right pr-4">Value</th>
                                                <th class="text-center">Orders</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($customers as $i => $customer)
                                                @php
                                                    $displayName = optional($customer->user)->outlet_name ?: (optional($customer->user)->name ?? 'N/A');
                                                    $email       = optional($customer->user)->email ?? '—';
                                                    $rank        = ($customers->currentPage() - 1) * $customers->perPage() + $i + 1;
                                                @endphp
                                                <tr>
                                                    <td class="pl-4">
                                                        @if($rank <= 3)
                                                            @php
                                                                $medals = [1 => '🥇', 2 => '🥈', 3 => '🥉'];
                                                            @endphp
                                                            <span title="Rank #{{ $rank }}">{{ $medals[$rank] }}</span>
                                                        @else
                                                            <span class="text-muted">{{ $rank }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="font-weight-bold">{{ $displayName }}</td>
                                                    <td class="text-muted" style="font-size:13px;">{{ $email }}</td>
                                                    <td class="text-right pr-4 font-weight-bold text-dark">{!! formatWithCurrency($customer->total_value) !!}</td>
                                                    <td class="text-center">
                                                        <span class="badge badge-info px-2" style="font-size:13px;">{{ number_format($customer->total_orders) }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="{{ route('admin.reports.orders', ['user_id' => $customer->user_id]) }}"
                                                           class="btn btn-outline-primary btn-sm rounded-pill px-3"
                                                           title="View Order Report for {{ $displayName }}">
                                                            <i class="fas fa-chart-bar mr-1"></i> Report
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        {{-- <tfoot class="bg-light">
                                            <tr>
                                                <td colspan="3" class="pl-4 font-weight-bold text-dark">Grand Total</td>
                                                <td class="text-center font-weight-bold text-dark">{{ number_format($grandTotals->grand_total_orders ?? 0) }}</td>
                                                <td class="text-right pr-4 font-weight-bold text-dark">{!! formatWithCurrency($grandTotals->grand_total_value ?? 0) !!}</td>
                                                <td></td>
                                            </tr>
                                        </tfoot> --}}
                                    </table>
                                </div>

                                <div class="card-body d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                                    <p class="text-muted mb-0" style="font-size:14px;">
                                        Showing <strong>{{ $customers->firstItem() }}</strong>–<strong>{{ $customers->lastItem() }}</strong>
                                        of <strong>{{ $customers->total() }}</strong> customers
                                    </p>
                                    <div class="custom-pagination">
                                        {{ $customers->links() }}
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-crown fa-3x mb-3 text-warning" style="opacity:0.3;"></i>
                                    <p class="mb-0">
                                        @if(request('search'))
                                            No customers found matching <strong>"{{ request('search') }}"</strong>.
                                        @else
                                            No order data available yet.
                                        @endif
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection
