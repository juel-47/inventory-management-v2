@extends('backend.layouts.master')

@section('title', 'Analytics Dashboard')

@section('content')
    <section class="section">
        {{-- Header --}}
        <div class="section-header">
            <div class="d-flex align-items-center flex-wrap w-100">
                <div>
                    <h1 class="mb-0 d-flex align-items-center">
                        <i class="fas fa-chart-pie mr-2 text-primary"></i>
                        Analytics Dashboard
                    </h1>
                    <small class="text-muted">
                        <i class="fas fa-calendar-alt mr-1"></i>
                        {{ now()->format('l, F j, Y') }} | 
                        <i class="fas fa-clock ml-2 mr-1"></i>
                        {{ now()->format('h:i A') }}
                    </small>
                </div>
                <div class="ml-auto d-flex align-items-center flex-wrap">
                    <div class="section-header-breadcrumb">
                        <div class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                            </a>
                        </div>
                        <div class="breadcrumb-item active">Analytics</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="section-body">
            {{-- Welcome Message --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card bg-gradient-primary text-white shadow-lg border-0">
                        <div class="card-body p-3 p-sm-4">
                            <div class="d-flex flex-wrap align-items-center justify-content-between">
                                <div>
                                    <h2 class="text-white font-weight-bold mb-2">
                                        <i class="fas fa-wave-square mr-2"></i>
                                        Welcome to Analytics!
                                    </h2>
                                    <p class="text-white-50 mb-0">
                                        <i class="fas fa-chart-bar mr-1"></i>
                                        Here's your business performance overview
                                    </p>
                                </div>
                                <div class="mt-2 mt-sm-0">
                                    <span class="badge badge-light p-2 px-3">
                                        <i class="fas fa-sync-alt mr-1 text-primary"></i>
                                        Live Data
                                    </span>
                                    <span class="badge badge-success p-2 px-3 ml-2">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        All Systems Active
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Statistics Cards --}}
            <div class="row">
                <div class="col-12 col-sm-6 col-lg-3 mb-3 mb-sm-4">
                    <div class="card card-statistic-2 shadow-sm border-0 hover-card">
                        <div class="card-icon-wrapper bg-primary">
                            <div class="card-icon">
                                <i class="fas fa-coins"></i>
                            </div>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Stock Value</h4>
                            </div>
                            <div class="card-body">
                                <span class="font-weight-bold text-primary">{!! formatWithCurrency($totalStockValue) !!}</span>
                            </div>
                            <div class="card-footer">
                                <span class="text-success">
                                    <i class="fas fa-arrow-up mr-1"></i> +12.5%
                                </span>
                                <span class="text-muted ml-2">vs last month</span>
                            </div>
                        </div>
                        <div class="card-progress-bar">
                            <div class="progress-bar" style="width: 75%;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-3 mb-3 mb-sm-4">
                    <div class="card card-statistic-2 shadow-sm border-0 hover-card">
                        <div class="card-icon-wrapper bg-success">
                            <div class="card-icon">
                                <i class="fas fa-cubes"></i>
                            </div>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Products</h4>
                            </div>
                            <div class="card-body">
                                <span class="font-weight-bold text-success">{{ $totalProducts }}</span>
                            </div>
                            <div class="card-footer">
                                <span class="text-success">
                                    <i class="fas fa-arrow-up mr-1"></i> +5.2%
                                </span>
                                <span class="text-muted ml-2">vs last month</span>
                            </div>
                        </div>
                        <div class="card-progress-bar">
                            <div class="progress-bar" style="width: 60%;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-3 mb-3 mb-sm-4">
                    <div class="card card-statistic-2 shadow-sm border-0 hover-card">
                        <div class="card-icon-wrapper bg-warning">
                            <div class="card-icon">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Low Stock Items</h4>
                            </div>
                            <div class="card-body">
                                <span class="font-weight-bold text-warning">{{ $lowStockCount }}</span>
                            </div>
                            <div class="card-footer">
                                <span class="text-danger">
                                    <i class="fas fa-arrow-up mr-1"></i> +2.1%
                                </span>
                                <span class="text-muted ml-2">needs attention</span>
                            </div>
                        </div>
                        <div class="card-progress-bar">
                            <div class="progress-bar bg-danger" style="width: 30%;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-3 mb-3 mb-sm-4">
                    <div class="card card-statistic-2 shadow-sm border-0 hover-card">
                        <div class="card-icon-wrapper bg-danger">
                            <div class="card-icon">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Monthly Purchases</h4>
                            </div>
                            <div class="card-body">
                                <span class="font-weight-bold text-danger">{!! formatConverted($monthlyPurchases) !!}</span>
                            </div>
                            <div class="card-footer">
                                <span class="text-success">
                                    <i class="fas fa-arrow-up mr-1"></i> +8.7%
                                </span>
                                <span class="text-muted ml-2">vs last month</span>
                            </div>
                        </div>
                        <div class="card-progress-bar">
                            <div class="progress-bar" style="width: 85%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Revenue & Profit Cards --}}
            <div class="row mt-2 mt-sm-4">
                <div class="col-12 col-lg-6 mb-3 mb-lg-4">
                    <div class="card card-statistic-3 shadow-sm border-0 hover-card">
                        <div class="card-icon-wrapper bg-info">
                            <div class="card-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Sale Revenue</h4>
                            </div>
                            <div class="card-body">
                                <span class="font-weight-bold text-info">{!! formatConverted($totalRevenue) !!}</span>
                            </div>
                            <div class="card-footer">
                                <span class="text-success">
                                    <i class="fas fa-arrow-up mr-1"></i> +15.3%
                                </span>
                                <span class="text-muted ml-2">vs last month</span>
                            </div>
                        </div>
                        <div class="card-progress-bar">
                            <div class="progress-bar" style="width: 90%;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-6 mb-3 mb-lg-4">
                    <div class="card card-statistic-3 shadow-sm border-0 hover-card">
                        <div class="card-icon-wrapper bg-warning">
                            <div class="card-icon">
                                <i class="fas fa-arrow-trend-up"></i>
                            </div>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Gross Profit</h4>
                            </div>
                            <div class="card-body">
                                <span class="font-weight-bold text-warning">{!! formatConverted($grossProfit) !!}</span>
                            </div>
                            <div class="card-footer">
                                <span class="text-success">
                                    <i class="fas fa-arrow-up mr-1"></i> +22.8%
                                </span>
                                <span class="text-muted ml-2">vs last month</span>
                            </div>
                        </div>
                        <div class="card-progress-bar">
                            <div class="progress-bar" style="width: 95%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chart Section --}}
            <div class="row mt-2 mt-sm-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-2 py-sm-3 border-0 flex-wrap">
                            <div class="d-flex align-items-center flex-wrap w-100">
                                <div class="d-flex align-items-center mb-2 mb-sm-0">
                                    <div class="mr-2 mr-sm-3 p-2 bg-primary rounded-circle text-white d-none d-sm-flex">
                                        <i class="fas fa-chart-bar"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0 font-weight-bold text-dark">Performance Overview</h5>
                                        <small class="text-muted">Monthly revenue and profit analysis</small>
                                    </div>
                                </div>
                                <div class="ml-auto">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-primary" data-period="week">Week</button>
                                        <button class="btn btn-outline-primary" data-period="month">Month</button>
                                        <button class="btn btn-outline-primary" data-period="year">Year</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-2 p-sm-3">
                            <div class="chart-container" style="position: relative; height: 250px; height: 300px;">
                                <canvas id="revenueChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="row mt-2 mt-sm-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-2 py-sm-3 border-0 flex-wrap">
                            <div class="d-flex align-items-center flex-wrap w-100">
                                <div class="d-flex align-items-center mb-2 mb-sm-0">
                                    <div class="mr-2 mr-sm-3 p-2 bg-primary rounded-circle text-white d-none d-sm-flex">
                                        <i class="fas fa-rocket"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0 font-weight-bold text-dark">Quick Actions</h5>
                                        <small class="text-muted">Generate reports and insights instantly</small>
                                    </div>
                                </div>
                                <div class="ml-auto">
                                    <span class="badge badge-primary p-2">
                                        <i class="fas fa-file-alt mr-1"></i> 6 Reports
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-2 p-sm-3">
                            <div class="row">
                                <div class="col-12 col-sm-6 col-lg-4 mb-2 mb-sm-3">
                                    <a href="{{ route('admin.reports.stock') }}" 
                                       class="btn btn-primary btn-block report-btn shadow-sm">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <i class="fas fa-warehouse mr-2"></i>
                                            <span>Stock Valuation</span>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-4 mb-2 mb-sm-3">
                                    <a href="{{ route('admin.reports.purchase') }}" 
                                       class="btn btn-success btn-block report-btn shadow-sm">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <i class="fas fa-file-invoice mr-2"></i>
                                            <span>Purchase History</span>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-4 mb-2 mb-sm-3">
                                    <a href="{{ route('admin.reports.product-purchase-history') }}" 
                                       class="btn btn-info btn-block report-btn shadow-sm">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <i class="fas fa-history mr-2"></i>
                                            <span>Product Tracking</span>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-4 mb-2 mb-sm-3">
                                    <a href="{{ route('admin.reports.low-stock') }}" 
                                       class="btn btn-warning btn-block report-btn shadow-sm">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <i class="fas fa-exclamation-circle mr-2"></i>
                                            <span>Low Stock Alert</span>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-4 mb-2 mb-sm-3">
                                    <a href="{{ route('admin.reports.audit') }}" 
                                       class="btn btn-dark btn-block report-btn shadow-sm">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <i class="fas fa-user-shield mr-2"></i>
                                            <span>Audit Report</span>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-12 col-sm-6 col-lg-4 mb-2 mb-sm-3">
                                    <a href="{{ route('admin.reports.orders') }}" 
                                       class="btn btn-secondary btn-block report-btn shadow-sm">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <i class="fas fa-shopping-cart mr-2"></i>
                                            <span>Order & Issue</span>
                                        </div>
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

@push('styles')
<style>
    /* =============================================
       ANALYTICS DASHBOARD - FULLY RESPONSIVE
       ============================================= */

    .bg-gradient-primary {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
    }

    /* Card Styles */
    .hover-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        cursor: default;
        border-radius: 16px !important;
        overflow: hidden !important;
        position: relative;
        background: #ffffff !important;
        height: 100%;
    }

    .hover-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #4e73df, #1cc88a, #f6c23e, #e74a3b);
        opacity: 0;
        transition: all 0.4s ease;
        z-index: 1;
    }

    .hover-card:hover::before {
        opacity: 1;
        height: 4px;
    }

    .hover-card:hover {
        transform: translateY(-10px) !important;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12) !important;
    }

    .hover-card:hover .card-icon-wrapper {
        transform: scale(1.1);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    }

    /* Card Icon Wrapper */
    .card-icon-wrapper {
        position: absolute;
        top: -20px;
        right: -20px;
        width: 100px;
        height: 100px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0.10;
        transition: all 0.4s ease;
        pointer-events: none;
    }

    .card-icon-wrapper .card-icon {
        font-size: 3rem;
        color: inherit;
    }

    .card-icon-wrapper.bg-primary {
        background: #4e73df !important;
        color: #4e73df !important;
    }

    .card-icon-wrapper.bg-success {
        background: #1cc88a !important;
        color: #1cc88a !important;
    }

    .card-icon-wrapper.bg-warning {
        background: #f6c23e !important;
        color: #f6c23e !important;
    }

    .card-icon-wrapper.bg-danger {
        background: #e74a3b !important;
        color: #e74a3b !important;
    }

    .card-icon-wrapper.bg-info {
        background: #36b9cc !important;
        color: #36b9cc !important;
    }

    /* Card Wrap */
    .card-wrap {
        padding: 20px 25px 15px 25px !important;
        position: relative;
        z-index: 2;
    }

    /* Card Header */
    .card-statistic-2 .card-header,
    .card-statistic-3 .card-header {
        padding: 0 !important;
        border: none !important;
        background: transparent !important;
    }

    .card-statistic-2 .card-header h4,
    .card-statistic-3 .card-header h4 {
        font-size: 0.75rem !important;
        font-weight: 700 !important;
        color: #858796 !important;
        margin: 0 !important;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* Card Body */
    .card-statistic-2 .card-body,
    .card-statistic-3 .card-body {
        padding: 8px 0 5px 0 !important;
        font-size: 1.6rem !important;
        font-weight: 700 !important;
    }

    /* Card Footer */
    .card-statistic-2 .card-footer,
    .card-statistic-3 .card-footer {
        padding: 5px 0 0 0 !important;
        background: transparent !important;
        border: none !important;
    }

    .card-statistic-2 .card-footer .text-success,
    .card-statistic-3 .card-footer .text-success {
        font-weight: 600;
        font-size: 0.8rem;
    }

    .card-statistic-2 .card-footer .text-danger,
    .card-statistic-3 .card-footer .text-danger {
        font-weight: 600;
        font-size: 0.8rem;
    }

    .card-statistic-2 .card-footer .text-muted,
    .card-statistic-3 .card-footer .text-muted {
        font-size: 0.75rem;
    }

    /* Progress Bar */
    .card-progress-bar {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: #f1f1f1;
        border-radius: 0 0 16px 16px;
        overflow: hidden;
    }

    .card-progress-bar .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #4e73df, #1cc88a);
        border-radius: 0 0 16px 16px;
        transition: width 1s ease;
        width: 0%;
    }

    .card-progress-bar .progress-bar.bg-danger {
        background: linear-gradient(90deg, #e74a3b, #f6c23e);
    }

    .hover-card:hover .card-progress-bar .progress-bar {
        animation: progressPulse 1s ease;
    }

    @keyframes progressPulse {
        0% { opacity: 0.6; }
        50% { opacity: 1; }
        100% { opacity: 0.6; }
    }

    /* Report Buttons */
    .report-btn {
        border-radius: 12px !important;
        padding: 12px 15px !important;
        font-weight: 600 !important;
        font-size: 0.85rem !important;
        transition: all 0.3s ease !important;
        text-transform: none !important;
        letter-spacing: 0.3px !important;
        position: relative;
        overflow: hidden;
        border: none !important;
        min-height: 48px;
    }

    .report-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.6s ease;
    }

    .report-btn:hover::before {
        left: 100%;
    }

    .report-btn:hover {
        transform: translateY(-4px) !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
    }

    .report-btn:active {
        transform: scale(0.97) !important;
    }

    .report-btn i {
        font-size: 1rem !important;
    }

    .report-btn.btn-primary {
        background: linear-gradient(135deg, #4e73df, #224abe) !important;
    }

    .report-btn.btn-success {
        background: linear-gradient(135deg, #1cc88a, #13855c) !important;
    }

    .report-btn.btn-info {
        background: linear-gradient(135deg, #36b9cc, #258391) !important;
    }

    .report-btn.btn-warning {
        background: linear-gradient(135deg, #f6c23e, #dda20a) !important;
        color: #1a1a2e !important;
    }

    .report-btn.btn-dark {
        background: linear-gradient(135deg, #5a5c69, #2d2d3f) !important;
    }

    .report-btn.btn-secondary {
        background: linear-gradient(135deg, #858796, #5a5c69) !important;
    }

    /* Chart Container */
    .chart-container {
        position: relative !important;
        width: 100% !important;
    }

    /* Badge Styles */
    .badge {
        font-weight: 600 !important;
        padding: 0.35rem 0.75rem !important;
        border-radius: 50rem !important;
    }

    .badge-light {
        background: rgba(255, 255, 255, 0.2) !important;
        color: #fff !important;
    }

    .badge-success {
        background: #1cc88a !important;
        color: #fff !important;
    }

    .badge-primary {
        background: #4e73df !important;
        color: #fff !important;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    @keyframes iconBounce {
        0% { transform: scale(1); }
        30% { transform: scale(1.3) rotate(-5deg); }
        60% { transform: scale(0.9) rotate(3deg); }
        100% { transform: scale(1) rotate(0); }
    }

    .card-statistic-2:hover .card-icon-wrapper .card-icon,
    .card-statistic-3:hover .card-icon-wrapper .card-icon {
        animation: iconBounce 0.6s ease;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .card {
        animation: fadeInUp 0.4s ease-out;
    }

    /* =============================================
       RESPONSIVE BREAKPOINTS
       ============================================= */

    /* Extra Small Devices (Phones) */
    @media (max-width: 575.98px) {
        .section-header {
            padding: 10px 15px !important;
        }
        .section-header h1 {
            font-size: 1rem !important;
        }
        .section-header .ml-auto {
            width: 100% !important;
            justify-content: space-between !important;
        }
        .section-header .breadcrumb {
            font-size: 0.75rem !important;
        }
        
        .card-body {
            padding: 12px !important;
        }
        .card-header {
            padding: 10px 12px !important;
        }
        
        .card-statistic-2 .card-body,
        .card-statistic-3 .card-body {
            font-size: 1.1rem !important;
        }
        .card-statistic-2 .card-wrap,
        .card-statistic-3 .card-wrap {
            padding: 12px 15px 10px 15px !important;
        }
        
        .card-icon-wrapper {
            width: 60px !important;
            height: 60px !important;
            top: -12px;
            right: -12px;
        }
        .card-icon-wrapper .card-icon {
            font-size: 1.6rem !important;
        }
        
        .report-btn {
            padding: 10px 12px !important;
            font-size: 0.75rem !important;
            min-height: 40px;
        }
        .report-btn i {
            font-size: 0.85rem !important;
        }
        
        .bg-gradient-primary .card-body {
            padding: 12px 15px !important;
        }
        .bg-gradient-primary h2 {
            font-size: 1.1rem !important;
        }
        .bg-gradient-primary p {
            font-size: 0.85rem !important;
        }
        .bg-gradient-primary .badge {
            font-size: 0.65rem !important;
            padding: 0.2rem 0.5rem !important;
        }
        
        .btn-group {
            flex-wrap: wrap !important;
        }
        .btn-group .btn {
            font-size: 0.65rem !important;
            padding: 3px 8px !important;
            margin: 2px !important;
        }
        
        .chart-container {
            height: 200px !important;
        }
        
        .card-statistic-2 .card-header h4,
        .card-statistic-3 .card-header h4 {
            font-size: 0.65rem !important;
        }
        
        .card-statistic-2 .card-footer .text-success,
        .card-statistic-3 .card-footer .text-success,
        .card-statistic-2 .card-footer .text-danger,
        .card-statistic-3 .card-footer .text-danger {
            font-size: 0.7rem !important;
        }
        
        .card-statistic-2 .card-footer .text-muted,
        .card-statistic-3 .card-footer .text-muted {
            font-size: 0.65rem !important;
        }
        
        .badge-primary {
            font-size: 0.65rem !important;
            padding: 0.2rem 0.5rem !important;
        }
    }

    /* Small Devices (Tablets) */
    @media (min-width: 576px) and (max-width: 767.98px) {
        .section-header {
            padding: 12px 20px !important;
        }
        .section-header h1 {
            font-size: 1.1rem !important;
        }
        
        .card-body {
            padding: 15px !important;
        }
        
        .card-statistic-2 .card-body,
        .card-statistic-3 .card-body {
            font-size: 1.2rem !important;
        }
        .card-statistic-2 .card-wrap,
        .card-statistic-3 .card-wrap {
            padding: 15px 18px 12px 18px !important;
        }
        
        .card-icon-wrapper {
            width: 75px !important;
            height: 75px !important;
        }
        .card-icon-wrapper .card-icon {
            font-size: 2rem !important;
        }
        
        .report-btn {
            padding: 10px 14px !important;
            font-size: 0.8rem !important;
            min-height: 44px;
        }
        
        .chart-container {
            height: 250px !important;
        }
        
        .btn-group .btn {
            font-size: 0.7rem !important;
            padding: 4px 10px !important;
        }
    }

    /* Medium Devices (Tablets & Small Desktops) */
    @media (min-width: 768px) and (max-width: 991.98px) {
        .section-header {
            padding: 15px 25px !important;
        }
        
        .card-statistic-2 .card-body,
        .card-statistic-3 .card-body {
            font-size: 1.3rem !important;
        }
        
        .card-icon-wrapper {
            width: 85px !important;
            height: 85px !important;
        }
        .card-icon-wrapper .card-icon {
            font-size: 2.2rem !important;
        }
        
        .report-btn {
            padding: 12px 16px !important;
            font-size: 0.85rem !important;
        }
        
        .chart-container {
            height: 280px !important;
        }
    }

    /* Large Devices (Desktops) */
    @media (min-width: 992px) {
        .section-header {
            padding: 20px 30px !important;
        }
        
        .card-statistic-2 .card-body,
        .card-statistic-3 .card-body {
            font-size: 1.6rem !important;
        }
        
        .card-icon-wrapper {
            width: 100px !important;
            height: 100px !important;
        }
        .card-icon-wrapper .card-icon {
            font-size: 3rem !important;
        }
        
        .report-btn {
            padding: 15px 20px !important;
            font-size: 0.9rem !important;
            min-height: 52px;
        }
        
        .chart-container {
            height: 350px !important;
        }
    }

    /* Touch Devices */
    @media (hover: none) {
        .hover-card:hover {
            transform: none !important;
        }
        .hover-card:hover .card-icon-wrapper {
            transform: none !important;
        }
        .report-btn:hover {
            transform: none !important;
        }
        .report-btn:active {
            transform: scale(0.97) !important;
        }
        .hover-card:active {
            transform: scale(0.98) !important;
        }
    }

    /* Scrollbar */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    ::-webkit-scrollbar-thumb {
        background: #4e73df;
        border-radius: 10px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: #224abe;
    }

    /* Card Borders */
    .card {
        border-radius: 16px !important;
    }
    .card-header:first-child {
        border-radius: 16px 16px 0 0 !important;
    }
    .card-footer:last-child {
        border-radius: 0 0 16px 16px !important;
    }

    /* Button Group */
    .btn-group .btn {
        border-radius: 8px !important;
        margin: 0 3px !important;
        padding: 5px 14px !important;
        font-weight: 600 !important;
        transition: all 0.3s ease !important;
        font-size: 0.8rem !important;
    }
    .btn-group .btn-primary {
        background: linear-gradient(135deg, #4e73df, #224abe) !important;
        border-color: #4e73df !important;
    }
    .btn-group .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(78, 115, 223, 0.3);
    }
    .btn-group .btn-outline-primary {
        color: #4e73df !important;
        border-color: #4e73df !important;
    }
    .btn-group .btn-outline-primary:hover {
        background: linear-gradient(135deg, #4e73df, #224abe) !important;
        color: #fff !important;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(78, 115, 223, 0.2);
    }

    /* Chart Responsive */
    @media (max-width: 575.98px) {
        .chart-container {
            height: 200px !important;
        }
        .card-header .d-flex {
            flex-direction: column !important;
            align-items: flex-start !important;
        }
        .card-header .ml-auto {
            margin-left: 0 !important;
            margin-top: 8px !important;
            width: 100% !important;
        }
        .btn-group {
            width: 100% !important;
            display: flex !important;
            justify-content: center !important;
        }
        .btn-group .btn {
            flex: 1 !important;
            text-align: center !important;
        }
    }

    @media (min-width: 576px) and (max-width: 767.98px) {
        .chart-container {
            height: 250px !important;
        }
    }

    @media (min-width: 768px) and (max-width: 991.98px) {
        .chart-container {
            height: 280px !important;
        }
    }

    @media (min-width: 992px) {
        .chart-container {
            height: 350px !important;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let revenueChart;

    $(document).ready(function() {
        // Performance Chart
        const ctx1 = document.getElementById('revenueChart').getContext('2d');
        revenueChart = new Chart(ctx1, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Revenue',
                    data: [12000, 15000, 18000, 14000, 20000, 25000, 22000, 28000, 30000, 27000, 35000, 40000],
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#4e73df',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }, {
                    label: 'Profit',
                    data: [3000, 4500, 6000, 4000, 7000, 9000, 8000, 10000, 12000, 10000, 14000, 16000],
                    borderColor: '#1cc88a',
                    backgroundColor: 'rgba(28, 200, 138, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#1cc88a',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            font: {
                                size: 11,
                                weight: 600
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleFont: { size: 13, weight: 600 },
                        bodyFont: { size: 12 },
                        padding: 10,
                        cornerRadius: 8
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            },
                            font: {
                                size: 10
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 10
                            }
                        }
                    }
                }
            }
        });

        // Period filter buttons
        $('.btn-group .btn').on('click', function() {
            $('.btn-group .btn').removeClass('btn-primary').addClass('btn-outline-primary');
            $(this).removeClass('btn-outline-primary').addClass('btn-primary');
            updateChartData($(this).data('period'));
        });
    });

    function updateChartData(period) {
        const data = {
            week: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                revenue: [5000, 7000, 6000, 8000, 9000, 11000, 10000],
                profit: [1500, 2000, 1800, 2500, 2800, 3500, 3000]
            },
            month: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                revenue: [25000, 30000, 28000, 35000],
                profit: [8000, 10000, 9000, 12000]
            },
            year: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                revenue: [12000, 15000, 18000, 14000, 20000, 25000, 22000, 28000, 30000, 27000, 35000, 40000],
                profit: [3000, 4500, 6000, 4000, 7000, 9000, 8000, 10000, 12000, 10000, 14000, 16000]
            }
        };

        const selectedData = data[period] || data.month;
        revenueChart.data.labels = selectedData.labels;
        revenueChart.data.datasets[0].data = selectedData.revenue;
        revenueChart.data.datasets[1].data = selectedData.profit;
        revenueChart.update();
    }
</script>
@endpush
