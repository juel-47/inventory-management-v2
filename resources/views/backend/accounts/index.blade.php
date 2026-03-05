@extends('backend.layouts.master')

@section('title', 'Transaction History')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Accounts - Transaction History</h1>
        </div>

        <div class="section-body">
            <div class="card card-primary">
                <div class="card-header">
                    <h4>Transaction Ledger</h4>
                    <div class="card-header-action">
                        <form id="filter-form" class="form-inline">
                            <div class="form-group mr-2">
                                <label class="mr-1 d-none d-lg-block">From:</label>
                                <input type="date" id="start_date" class="form-control form-control-sm">
                            </div>
                            <div class="form-group mr-2">
                                <label class="mr-1 d-none d-lg-block">To:</label>
                                <input type="date" id="end_date" class="form-control form-control-sm">
                            </div>
                            <div class="form-group mr-2">
                                <select id="method" class="form-control form-control-sm">
                                    <option value="">All Methods</option>
                                    <option value="cash">Cash</option>
                                    <option value="bank">Bank Transfer</option>
                                    <option value="mobile_banking">Mobile Banking</option>
                                    <option value="cheque">Cheque</option>
                                </select>
                            </div>
                            <button type="button" id="btn-reset" class="btn btn-danger btn-sm" title="Reset Filters"><i class="fas fa-undo"></i></button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    {{ $dataTable->table() }}
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    <script>
        $(document).ready(function() {
            const table = window.LaravelDataTables["order-payment-table"];

            // Auto-filter on change
            $('#start_date, #end_date, #method').on('change', function() { table.draw(); });

            // Reset filters
            $('#btn-reset').on('click', function(e) {
                e.preventDefault();
                $('#filter-form')[0].reset();
                table.draw();
            });
            
            // Bind to DataTable query
            table.on('preXhr.dt', function(e, settings, data) {
                data.start_date = $('#start_date').val();
                data.end_date = $('#end_date').val();
                data.method = $('#method').val();
            });
        });
    </script>
@endpush
