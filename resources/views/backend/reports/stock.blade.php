@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <div>
                <h1><i class="fas fa-chart-pie mr-2 text-primary"></i>Stock Report</h1>
                {{-- @if($settings)
                    <small class="text-muted"><i class="fas fa-building mr-1"></i>{{ $settings->site_name ?? 'Inventory Management System' }} | {{ $settings->contact_email ?? '' }}</small>
                @endif --}}
            </div>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></div>
                <div class="breadcrumb-item">Stock</div>
            </div>
        </div>

        <div class="section-body">
            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-primary">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Stock Qty</h4>
                            </div>
                            <div class="card-body">
                                <span id="span-total-qty">{{ number_format($totalQty) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-success">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Asset Value</h4>
                            </div>
                            <div class="card-body">
                                <span id="span-total-value">{{ $settings->currency_icon }}{{ number_format($totalValue, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-info">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Potential Revenue</h4>
                            </div>
                            <div class="card-body">
                                <span id="span-potential-revenue">{{ $settings->currency_icon }}{{ number_format($potentialRevenue, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1 shadow-sm">
                        <div class="card-icon bg-warning">
                             <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Potential Profit</h4>
                            </div>
                            <div class="card-body">
                                <span id="span-potential-profit">{{ $settings->currency_icon }}{{ number_format($potentialProfit, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h4><i class="fas fa-filter mr-2"></i>Filter Options</h4>
                    <div class="card-header-action">
                        <a data-collapse="#mycard-collapse" class="btn btn-icon btn-info" href="#"><i class="fas fa-minus"></i></a>
                    </div>
                </div>
                <div class="collapse show" id="mycard-collapse">
                    <div class="card-body">
                        <form action="{{ route('admin.reports.stock') }}" method="GET">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>Category</label>
                                        <select name="category_id" class="form-control select2">
                                            <option value="">All Categories</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}" {{ request()->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>Brand</label>
                                        <select name="brand_id" class="form-control select2">
                                            <option value="">All Brands</option>
                                            @foreach ($brands as $brand)
                                                <option value="{{ $brand->id }}" {{ request()->brand_id == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2" style="margin-top: 29px;">
                                    <a href="{{ route('admin.reports.stock') }}" class="btn btn-danger btn-block"><i class="fas fa-undo"></i> Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Report Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h4>Detailed Stock List</h4>
                            <div class="card-header-action">
                                <button class="btn btn-primary btn-sm" id="btn-export-excel">
                                    <i class="fas fa-file-excel"></i> Export Excel
                                </button>
                                <button class="btn btn-primary btn-sm ml-1" id="btn-export-pdf">
                                    <i class="fas fa-file-pdf"></i> Export PDF
                                </button>
                                <button class="btn btn-primary btn-sm ml-1" id="btn-print">
                                    <i class="fas fa-print"></i> Print
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Export Header - Will be included in PDF/Excel/Print (Hidden from UI) -->
                            <div class="export-header d-none" style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-left: 4px solid #007bff;">
                                <div style="text-align: center; margin-bottom: 10px;">
                                    <h3 style="margin: 0; color: #333;">{{ $settings->site_name ?? 'Inventory Management System' }}</h3>
                                    @if($settings->contact_email)
                                        <p style="margin: 5px 0; color: #666;"><strong>Email:</strong> {{ $settings->contact_email }}</p>
                                    @endif
                                    @if($settings->address)
                                        <p style="margin: 5px 0; color: #666;"><strong>Address:</strong> {{ $settings->address }}</p>
                                    @endif
                                    <hr style="margin: 10px 0; border-top: 2px solid #007bff;">
                                    <h4 style="margin: 10px 0; color: #007bff;">Stock Valuation Report</h4>
                                    <p style="margin: 5px 0; color: #666; font-size: 14px;"><strong>Generated on:</strong> {{ date('F d, Y h:i A') }}</p>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="table-stock">
                                    <thead>
                                        <tr>
                                            <th>Product Info</th>
                                            <th>Category / Brand</th>
                                            <th class="text-center">Stock Qty</th>
                                            <th class="text-right">Unit Cost</th>
                                            <th class="text-right">Unit Price</th>
                                            <th class="text-right">Total Asset Value</th>
                                            <th class="text-right">Profit Potential</th>
                                        </tr>
                                    </thead>
                                    <tbody id="stock-table-body">
                                        @include('backend.reports.partials.stock_table_rows')
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-light font-weight-bold">
                                            <td colspan="5" class="text-right text-dark">GRAND TOTAL:</td>
                                            <td class="text-right text-primary"><span id="span-grand-total-value">{{ $settings->currency_icon }}{{ number_format($totalValue, 2) }}</span></td>
                                            <td class="text-right text-success"><span id="span-grand-total-profit">{{ $settings->currency_icon }}{{ number_format($potentialProfit, 2) }}</span></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <p class="text-muted mb-3 text-center" style="font-size: 14px; font-weight: 500;">
                                Showing 
                                <span class="text-dark font-weight-bold">
                                    {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }}
                                </span> 
                                of 
                                 <span class="text-dark font-weight-bold">
                                    {{ $products->total() }}
                                </span> 
                                products
                            </p>
                            
                            <div class="d-flex justify-content-center flex-wrap custom-pagination">
                                {{ $products->links() }}
                            </div>
                            {{-- <div class="d-flex justify-content-center flex-wrap custom-pagination">
                                {{ $products->links() }}
                            </div> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        // Prepare export header text
        var exportHeader = '{{ $settings->site_name ?? "Inventory Management System" }}\n';
        exportHeader += 'Email: {{ $settings->contact_email ?? "N/A" }}\n';
        exportHeader += 'Address: {{ $settings->address ?? "N/A" }}\n';
        exportHeader += '-------------------------------------------\n';
        exportHeader += 'Stock Valuation Report\n';
        exportHeader += 'Generated on: {{ date("F d, Y h:i A") }}\n';
        exportHeader += '-------------------------------------------\n\n';

        var table = $("#table-stock").dataTable({
            dom: 'Brt',
            paging: false,
            info: false,
            searching: false,
            buttons: [
                {
                    extend: 'copy',
                    messageTop: exportHeader
                },
                {
                    extend: 'csv',
                    messageTop: exportHeader
                },
                {
                    extend: 'excel',
                    messageTop: exportHeader,
                    title: ''
                },
                {
                    extend: 'pdf',
                    messageTop: '',
                    title: '',
                    exportOptions: {
                        format: {
                            body: function (data, row, column, node) {
                                // column 0 is the product info column which contains Image, Name and SKU
                                if (column === 0) {
                                    // Extract only the product name (font-weight-bold class)
                                    var nameElement = $(node).find('.font-weight-bold');
                                    if (nameElement.length > 0) {
                                        return nameElement.text().trim();
                                    }
                                }
                                return data;
                            }
                        }
                    },
                    customize: function(doc) {
                        doc.content.splice(0, 0, {
                            text: [
                                { text: '{{ $settings->site_name ?? "Inventory Management System" }}\n', fontSize: 16, bold: true, alignment: 'center' },
                                { text: 'Email: {{ $settings->contact_email ?? "N/A" }}\n', fontSize: 10, alignment: 'center' },
                                { text: 'Address: {{ $settings->address ?? "N/A" }}\n\n', fontSize: 10, alignment: 'center' },
                                { text: 'Stock Valuation Report\n', fontSize: 14, bold: true, alignment: 'center', color: '#007bff' },
                                { text: 'Generated on: {{ date("F d, Y h:i A") }}\n\n', fontSize: 10, alignment: 'center' }
                            ]
                        });
                    }
                },
                {
                    extend: 'print',
                    messageTop: function() {
                        return $('.export-header').html();
                    },
                    title: ''
                }
            ]
        });

        // Hide default DataTables buttons (we'll use our custom styled buttons)
        $('.dt-buttons').hide();

        // Wire up custom export buttons
        function initExportButtons() {
            $('#btn-export-excel').off('click').on('click', function() {
                table.DataTable().button('.buttons-excel').trigger();
            });

            $('#btn-export-pdf').off('click').on('click', function() {
                table.DataTable().button('.buttons-pdf').trigger();
            });

            $('#btn-print').off('click').on('click', function() {
                table.DataTable().button('.buttons-print').trigger();
            });
        }

        // Initialize export buttons
        initExportButtons();

        // AJAX Filter on Change
        $('select[name="category_id"], select[name="brand_id"]').on('change', function() {
            let category_id = $('select[name="category_id"]').val();
            let brand_id = $('select[name="brand_id"]').val();

            $.ajax({
                url: "{{ route('admin.reports.stock') }}",
                method: 'GET',
                data: {
                    category_id: category_id,
                    brand_id: brand_id
                },
                beforeSend: function() {
                    // Optional: Show loader or opacity
                    $('#table-stock').css('opacity', '0.5');
                },
                success: function(response) {
                    $('#table-stock').css('opacity', '1');
                    
                    // Update Summary Cards
                    $('#span-total-qty').text(response.totalQty);
                    $('#span-total-value').text(response.totalValue);
                    $('#span-potential-revenue').text(response.potentialRevenue);
                    $('#span-potential-profit').text(response.potentialProfit);
                    
                    // Update Footer Totals (Reuse summary values)
                    $('#span-grand-total-value').text(response.totalValue);
                    $('#span-grand-total-profit').text(response.potentialProfit);

                    // Update Table Content
                    $('#pagination-container').html(response.pagination);
                    
                    // Destroy datatable, replace body, re-init datatable
                    table.fnDestroy(); 
                    $('#stock-table-body').html(response.html);
                    table = $("#table-stock").dataTable({
                        dom: 'Brt',
                        paging: false,
                        info: false,
                        searching: false,
                        buttons: [
                            {
                                extend: 'copy',
                                messageTop: exportHeader
                            },
                            {
                                extend: 'csv',
                                messageTop: exportHeader
                            },
                            {
                                extend: 'excel',
                                messageTop: exportHeader,
                                title: ''
                            },
                            {
                                extend: 'pdf',
                                messageTop: '',
                                title: '',
                                exportOptions: {
                                    format: {
                                        body: function (data, row, column, node) {
                                            // column 0 is the product info column which contains Image, Name and SKU
                                            if (column === 0) {
                                                // Extract only the product name (font-weight-bold class)
                                                var nameElement = $(node).find('.font-weight-bold');
                                                if (nameElement.length > 0) {
                                                    return nameElement.text().trim();
                                                }
                                            }
                                            return data;
                                        }
                                    }
                                },
                                customize: function(doc) {
                                    doc.content.splice(0, 0, {
                                        text: [
                                            { text: '{{ $settings->site_name ?? "Inventory Management System" }}\n', fontSize: 16, bold: true, alignment: 'center' },
                                            { text: 'Email: {{ $settings->contact_email ?? "N/A" }}\n', fontSize: 10, alignment: 'center' },
                                            { text: 'Address: {{ $settings->address ?? "N/A" }}\n\n', fontSize: 10, alignment: 'center' },
                                            { text: 'Stock Valuation Report\n', fontSize: 14, bold: true, alignment: 'center', color: '#007bff' },
                                            { text: 'Generated on: {{ date("F d, Y h:i A") }}\n\n', fontSize: 10, alignment: 'center' }
                                        ]
                                    });
                                }
                            },
                            {
                                extend: 'print',
                                messageTop: function() {
                                    return $('.export-header').html();
                                },
                                title: ''
                            }
                        ]
                    });
                    
                    // Hide default buttons and reinitialize custom export buttons
                    $('.dt-buttons').hide();
                    initExportButtons();
                },
                error: function(xhr) {
                    console.error(xhr);
                    $('#table-stock').css('opacity', '1');
                    alert('Error loading data');
                }
            });
        });
    </script>
@endpush
