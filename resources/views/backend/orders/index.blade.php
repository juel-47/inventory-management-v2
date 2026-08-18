@extends('backend.layouts.master')
@section('title', 'Frontend Orders')

@push('css')
<style>
    /* Filter Card Styling */
    .order-filter-card {
        background: #ffffff;
        border: 1px solid #e9ecef;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        margin-bottom: 25px;
    }

    .order-filter-card .card-body {
        padding: 1.25rem;
    }

    .order-filter-card .filter-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #6c757d;
        margin-bottom: 6px;
        display: block;
    }

    /* Select2 Customization */
    .order-filter-card .select2-container--default .select2-selection--single {
        height: 42px !important;
        border: 1px solid #dce1e7 !important;
        border-radius: 6px !important;
        display: flex !important;
        align-items: center !important;
        background-color: #fff !important;
        transition: all 0.2s ease-in-out;
    }

    .order-filter-card .select2-container--default.select2-container--focus .select2-selection--single,
    .order-filter-card .select2-container--default .select2-selection--single:hover {
        border-color: #6777ef !important;
    }

    .order-filter-card .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 40px !important;
        padding-left: 12px !important;
        padding-right: 25px !important;
        color: #495057 !important;
        font-size: 13.5px !important;
        font-weight: 500;
    }

    .order-filter-card .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
        right: 8px !important;
    }

    .order-filter-card .form-control {
        height: 42px;
        border-color: #dce1e7;
        border-radius: 6px;
        font-size: 13.5px;
        color: #495057;
        font-weight: 500;
        transition: all 0.2s ease-in-out;
    }

    .order-filter-card .form-control:focus {
        border-color: #6777ef;
        box-shadow: 0 0 0 0.2rem rgba(103, 119, 239, 0.15);
    }

    /* Reset Button Fix */
    .btn-reset-filter {
        height: 42px;
        font-weight: 600;
        border-radius: 6px;
        background-color: #fc544b !important;
        border: 1px solid #fc544b !important;
        color: #ffffff !important;
        transition: all 0.2s ease-in-out;
    }

    .btn-reset-filter:hover,
    .btn-reset-filter:focus,
    .btn-reset-filter:active {
        background-color: #e03b32 !important;
        border-color: #e03b32 !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(252, 84, 75, 0.35) !important;
    }

    .btn-reset-filter i {
        color: #ffffff !important;
    }

    /* Hide DataTables default search since we have a dedicated search bar */
    .dataTables_wrapper .dataTables_filter {
        display: none !important;
    }

    .dataTables_wrapper .dataTables_length {
        margin-bottom: 15px;
    }

    .dataTables_wrapper .dataTables_length select {
        height: 35px !important;
        padding: 4px 10px !important;
        border-radius: 6px !important;
        border: 1px solid #dce1e7 !important;
    }
</style>
@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Frontend Orders</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4>All Frontend Orders</h4>
                        </div>
                        <div class="card-body">
                            <!-- Enhanced Filter Panel -->
                            <div class="order-filter-card">
                                <div class="card-body">
                                    <!-- Row 1: Dropdowns (Full Width Split 6 + 6) -->
                                    <div class="row mb-3">
                                        <!-- User/Outlet Select (col-md-6) -->
                                        <div class="col-md-6 col-sm-12 mb-3 mb-md-0">
                                            <label for="filter_user" class="filter-label">
                                                <i class="fas fa-store text-primary mr-1"></i> User / Outlet
                                            </label>
                                            <select id="filter_user" class="form-control select2" style="width: 100%;">
                                                <option value="">All Users & Outlets</option>
                                                @foreach ($users as $user)
                                                    <option value="{{ $user->id }}">
                                                        {{ $user->outlet_name ? $user->outlet_name . ' (' . $user->name . ')' : $user->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Status Select (col-md-6) -->
                                        <div class="col-md-6 col-sm-12">
                                            <label for="filter_status" class="filter-label">
                                                <i class="fas fa-filter text-primary mr-1"></i> Status
                                            </label>
                                            <select id="filter_status" class="form-control custom-select">
                                                <option value="">All Statuses</option>
                                                <option value="pending">Pending</option>
                                                <option value="approved">Approved</option>
                                                <option value="processing">Processing</option>
                                                <option value="shipped">Shipped</option>
                                                <option value="completed">Completed</option>
                                                <option value="cancelled">Cancelled</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Row 2: Search (col-10) & Reset (col-2) -->
                                    <div class="row align-items-end">
                                        <!-- Custom Quick Search (col-md-10) -->
                                        <div class="col-md-10 col-sm-12 mb-3 mb-md-0">
                                            <label for="custom_search" class="filter-label">
                                                <i class="fas fa-search text-primary mr-1"></i> Quick Search
                                            </label>
                                            <div class="input-group">
                                                <input type="text" id="custom_search" class="form-control" placeholder="Search order no, customer...">
                                                <div class="input-group-append">
                                                    <span class="input-group-text bg-white" style="border-color: #dce1e7;">
                                                        <i class="fas fa-search text-muted"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Reset Button (col-md-2) -->
                                        <div class="col-md-2 col-sm-12">
                                            <button type="button" id="reset_filter" class="btn btn-danger btn-block d-flex align-items-center justify-content-center" style="height: 42px; font-weight: 600; border-radius: 6px;">
                                                <i class="fas fa-undo mr-1"></i> Reset
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Table Container -->
                            <div class="table-responsive">
                                {{ $dataTable->table(['class' => 'table table-striped table-bordered', 'id' => 'order-table']) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}

    <script>
        $(document).ready(function() {
            // Initialize Select2 with full width
            $('#filter_user').select2({
                placeholder: 'All Users & Outlets',
                allowClear: true,
                width: '100%'
            });

            function reloadDataTable() {
                if (window.LaravelDataTables && window.LaravelDataTables['order-table']) {
                    window.LaravelDataTables['order-table'].ajax.reload();
                }
            }

            // Status dropdown change
            $('#filter_status').on('change', function() {
                reloadDataTable();
            });

            // User filter change
            $('#filter_user').on('change', function() {
                reloadDataTable();
            });

            // Custom search input connected to DataTables search
            $('#custom_search').on('keyup input search clear', function() {
                if (window.LaravelDataTables && window.LaravelDataTables['order-table']) {
                    window.LaravelDataTables['order-table'].search(this.value).draw();
                }
            });

            // Reset filters button
            $('#reset_filter').on('click', function() {
                $('#filter_status').val('');
                $('#filter_user').val('').trigger('change.select2');
                $('#custom_search').val('');
                
                if (window.LaravelDataTables && window.LaravelDataTables['order-table']) {
                    window.LaravelDataTables['order-table'].search('').ajax.reload();
                }
            });
        });
    </script>
@endpush
