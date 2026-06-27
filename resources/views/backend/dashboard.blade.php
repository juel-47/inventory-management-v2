@extends('backend.layouts.master')
@section('title', 'Admin Dashboard')
@section('content')
    <section class="section">
        <div class="section-header">
            <h1><i class="fas fa-chart-pie mr-2 text-primary"></i>Dashboard</h1>
        </div>

        <div class="row">
            {{-- Admin Stats --}}
            @can('Manage Reports')
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-primary">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                         <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Issues</h4>
                            </div>
                             <div class="card-body">
                                <div>{{ $totalIssues }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-danger">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Pending Requests</h4>
                            </div>
                            <div class="card-body">
                                {{ $pendingRequests }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-warning">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Active Products</h4>
                            </div>
                            <div class="card-body">
                                {{ $totalActiveProducts }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-secondary">
                            <i class="fas fa-ban"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Inactive Products</h4>
                            </div>
                            <div class="card-body">
                                {{ $totalInactiveProducts }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-info">
                            <i class="fas fa-layer-group"></i>
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
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-success">
                            <i class="fas fa-store"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Active Outlets</h4>
                            </div>
                            <div class="card-body">
                                {{ $totalOutlets }}
                            </div>
                        </div>
                    </div>
                </div>
            @endcan

            {{-- User/Outlet Stats - Now based on permissions instead of just "else" --}}
            @can('Accountants')
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-success">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Spent</h4>
                            </div>
                            <div class="card-body">
                                {!! formatWithCurrency($myTotalSpent) !!}
                            </div>
                        </div>
                    </div>
                </div>
            @endcan

            @can('Manage Product Requests')
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-info">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Requests</h4>
                            </div>
                            <div class="card-body">
                                {{ $myTotalRequests }}
                            </div>
                        </div>
                    </div>
                </div>
            @endcan

            @can('Manage Order Place')
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-warning">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Pending Orders</h4>
                            </div>
                            <div class="card-body">
                                {{ $myPendingRequests }}
                            </div>
                        </div>
                    </div>
                </div>
            @endcan
        </div>

        @if(Auth::user()->can('Manage Reports'))
        <div class="row">
            <div class="col-lg-8 col-md-12 col-12 col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Monthly Issues Statistics</h4>
                        <div class="card-header-action">
                            
                        </div>
                    </div>
                    <div class="card-body" style="min-height: 250px;">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-12 col-12 col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Request Status Info</h4>
                    </div>
                    <div class="card-body" style="min-height: 300px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="row">
            <div class="col-lg-12">
                <div class="card border shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h4 class="text-dark"><i class="fas fa-history mr-2 text-primary"></i>Recent Orders</h4>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-primary btn-sm rounded-pill">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-whitesmoke">
                                    <tr>
                                        <th class="pl-4">Order No</th>
                                        @if(Auth::user()->can('Manage Reports'))
                                        <th>Customer</th>
                                        @endif
                                         <th>Date</th>
                                         <th class="text-right d-none">Total ({{@ $settings->base_currency_name }})</th>
                                         <th class="text-right">Total ({{@ $settings->currency_name }})</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-right pr-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentRequests as $request)
                                        <tr>
                                            <td class="pl-4 font-weight-bold">{{ $request->order_no }}</td>
                                            @if(Auth::user()->can('Manage Reports'))
                                            <td>{{ $request->user->name ?? 'N/A' }}</td>
                                            @endif
                                             <td>{{ $request->created_at->format('d M, Y') }}</td>
                                             <td class="text-right font-weight-bold text-dark d-none">{{ $settings->base_currency_icon . number_format($request->total_amount, 2) }}</td>
                                             <td class="text-right font-weight-bold text-dark">{!! formatConverted($request->total_amount) !!}</td>
                                            <td class="text-center">
                                                @php
                                                    $statusClasses = [
                                                        'pending' => 'warning',
                                                        'approved' => 'info',
                                                        'shipped' => 'primary',
                                                        'completed' => 'success',
                                                        'rejected' => 'danger',
                                                        'cancelled' => 'danger',
                                                    ];
                                                    $class = $statusClasses[$request->status] ?? 'dark';
                                                @endphp
                                                <span class="badge badge-{{ $class }} text-uppercase">{{ $request->status }}</span>
                                            </td>
                                            <td class="text-right pr-4">
                                                <a href="{{ route('admin.orders.show', $request->id) }}" class="btn btn-primary btn-sm rounded-pill px-3">Details</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ Auth::user()->can('Manage Reports') ? 7 : 6 }}" class="text-center py-4 text-muted font-italic">No recent orders found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
@if(Auth::user()->can('Manage Reports'))
    <script>
        "use strict";

        document.addEventListener('DOMContentLoaded', function() {
            var currencyIcon = "{{  $settings->currency_icon ?? '' }}";
            
            // Monthly Issues Statistics Chart
            var salesCtx = document.getElementById("salesChart");
            if (salesCtx) {
                var issueLabels = {!! json_encode($issueLabels) !!};
                var issueData = {!! json_encode($issueData) !!};
                
                salesCtx = salesCtx.getContext('2d');
                var salesChart = new Chart(salesCtx, {
                    type: 'line',
                    data: {
                        labels: issueLabels,
                        datasets: [{
                            label: 'Issues',
                            data: issueData,
                            borderWidth: 2,
                            backgroundColor: 'rgba(63,82,227,.8)',
                            borderColor: 'rgba(63,82,227,1)',
                            pointBorderWidth: 0,
                            pointRadius: 3.5,
                            pointBackgroundColor: 'rgba(63,82,227,1)',
                            pointHoverBackgroundColor: 'rgba(63,82,227,.8)',
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.parsed.y.toLocaleString() + ' Issues';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    callback: function(value) {
                                        return value;
                                    }
                                },
                                grid: {
                                    drawBorder: false,
                                    color: '#f2f2f2',
                                }
                            },
                            x: {
                                grid: {
                                    display: false,
                                }
                            }
                        },
                    }
                });
            }

            // Request Status Info Chart
            var statusCtx = document.getElementById("statusChart");
            if (statusCtx) {
                var statusData = {!! json_encode($statusData) !!};
                
                // Check if we have valid data
                var hasData = statusData && statusData.length > 0 && statusData.some(val => val > 0);
                
                if (!hasData) {
                    // Display a message instead of chart
                    var parentDiv = statusCtx.parentElement;
                    parentDiv.innerHTML = '<div class="text-center py-5"><i class="fas fa-chart-pie fa-3x text-muted mb-3"></i><p class="text-muted mb-1"><strong>No Request Data Yet</strong></p><p class="text-muted small">This chart will populate once outlets create requests</p></div>';
                } else {
                    statusCtx = statusCtx.getContext('2d');
                    var statusChart = new Chart(statusCtx, {
                        type: 'doughnut',
                        data: {
                            datasets: [{
                                data: statusData,
                                backgroundColor: [
                                    '#ffa426', // Pending - Warning
                                    '#6777ef', // Approved - Primary/Info
                                    '#fc544b', // Rejected - Danger
                                ],
                                borderWidth: 0
                            }],
                            labels: [
                                'Pending',
                                'Approved',
                                'Rejected'
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 15,
                                        usePointStyle: true
                                    }
                                }
                            }
                        }
                    });
                }
            }
        });
    </script>
@endif
@endpush
