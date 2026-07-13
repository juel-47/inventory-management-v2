@extends('backend.layouts.master')
@section('title', 'Best Seller Products')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1><i class="fas fa-fire mr-2 text-danger"></i>Best Seller Products</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item active"><a href="{{ route('admin.reports.index') }}">Reports</a></div>
                <div class="breadcrumb-item">Best Sellers</div>
            </div>
        </div>

        <div class="section-body">

            {{-- Summary Cards --}}
            <div class="row mb-4">
                <div class="col-lg-4 col-md-4 col-sm-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-danger">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Unique Products</h4></div>
                            <div class="card-body">{{ number_format($products->total()) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-warning">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Total Qty Ordered</h4></div>
                            <div class="card-body">{{ number_format($grandTotals->grand_total_qty ?? 0) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-success">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>Total Order Value</h4></div>
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
                            <h4 class="mb-0"><i class="fas fa-fire mr-2 text-danger"></i>All Products by Order Frequency</h4>
                            <div class="d-flex align-items-center" style="gap:8px;">
                                <form method="GET" action="{{ route('admin.reports.best-sellers') }}" class="d-flex" style="gap:6px;">
                                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search product name..." value="{{ request('search') }}" style="min-width:200px;">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
                                    @if(request('search'))
                                        <a href="{{ route('admin.reports.best-sellers') }}" class="btn btn-danger btn-sm"><i class="fas fa-times"></i> Clear</a>
                                    @endif
                                </form>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            @if($products->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="bg-whitesmoke">
                                            <tr>
                                                <th class="pl-4" style="width:50px;">#</th>
                                                <th>Product Name</th>
                                                <th class="text-center">Times Ordered</th>
                                                <th class="text-center">Total Qty</th>
                                                <th class="text-right pr-4">Total Value</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($products as $i => $product)
                                                <tr>
                                                    <td class="pl-4 text-muted">{{ ($products->currentPage() - 1) * $products->perPage() + $i + 1 }}</td>
                                                    <td class="font-weight-bold">{{ $product->product_name }}</td>
                                                    <td class="text-center">
                                                        <span class="badge badge-danger px-2" style="font-size:13px;">{{ number_format($product->times_ordered) }}</span>
                                                    </td>
                                                    <td class="text-center font-weight-bold">{{ number_format($product->total_qty) }}</td>
                                                    <td class="text-right pr-4 font-weight-bold text-dark">{!! formatWithCurrency($product->total_value) !!}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        {{-- <tfoot class="bg-light">
                                            <tr>
                                                <td colspan="2" class="pl-4 font-weight-bold text-dark">Grand Total</td>
                                                <td class="text-center">—</td>
                                                <td class="text-center font-weight-bold text-dark">{{ number_format($grandTotals->grand_total_qty ?? 0) }}</td>
                                                <td class="text-right pr-4 font-weight-bold text-dark">{!! formatWithCurrency($grandTotals->grand_total_value ?? 0) !!}</td>
                                            </tr>
                                        </tfoot> --}}
                                    </table>
                                </div>

                                <div class="card-body d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                                    <p class="text-muted mb-0" style="font-size:14px;">
                                        Showing <strong>{{ $products->firstItem() }}</strong>–<strong>{{ $products->lastItem() }}</strong>
                                        of <strong>{{ $products->total() }}</strong> products
                                    </p>
                                    <div class="custom-pagination">
                                        {{ $products->links() }}
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-fire fa-3x mb-3 text-danger" style="opacity:0.3;"></i>
                                    <p class="mb-0">
                                        @if(request('search'))
                                            No products found matching <strong>"{{ request('search') }}"</strong>.
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
