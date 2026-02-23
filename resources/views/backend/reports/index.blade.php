@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>All Reports</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-primary">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Stock Value</h4>
                            </div>
                            <div class="card-body">
                                {!! formatWithCurrency($totalStockValue) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-success">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Products</h4>
                            </div>
                            <div class="card-body">
                                {{ $totalProducts }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Low Stock Items</h4>
                            </div>
                            <div class="card-body">
                                {{ $lowStockCount }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-danger">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Monthly Purchases</h4>
                            </div>
                            <div class="card-body">
                                {!! formatConverted($monthlyPurchases) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 col-md-12 col-sm-12 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-primary">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Sale Revenue</h4>
                            </div>
                            <div class="card-body">
                                {!! formatConverted($totalRevenue) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12 col-sm-12 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-warning">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Gross Profit</h4>
                            </div>
                            <div class="card-body">
                                {!! formatConverted($grossProfit) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Quick Access to Reports</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <a href="{{ route('admin.reports.stock') }}" class="btn btn-primary btn-lg btn-block">
                                        <i class="fas fa-warehouse"></i> Stock Valuation Report
                                    </a>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <a href="{{ route('admin.reports.purchase') }}" class="btn btn-success btn-lg btn-block">
                                        <i class="fas fa-file-invoice"></i> Purchase History
                                    </a>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <a href="{{ route('admin.reports.product-purchase-history') }}" class="btn btn-info btn-lg btn-block">
                                        <i class="fas fa-history"></i> Product Purchase Tracking
                                    </a>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <a href="{{ route('admin.reports.low-stock') }}" class="btn btn-warning btn-lg btn-block">
                                        <i class="fas fa-exclamation-circle"></i> Low Stock Alert
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
