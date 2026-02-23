@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Stock Ledger</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Stock Ledger</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Inventory Movement History</h4>
                        </div>
                        <div class="card-body">
                            <div>
                                <table class="table table-striped" id="table-ledger">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th width="80">Image</th>
                                            <th>Product</th>
                                            <th>Variant</th>
                                            <th>Reference</th>
                                            <th>Type</th>
                                            <th>In Qty</th>
                                            <th>Out Qty</th>
                                            <th>Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Data loaded via Ajax --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        $("#table-ledger").dataTable({
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'copy',
                    className: 'btn btn-primary'
                },
                {
                    extend: 'csv',
                    className: 'btn btn-primary'
                },
                {
                    extend: 'excel',
                    className: 'btn btn-primary',
                    title: '{{ \App\Models\GeneralSetting::first()->site_name ?? "Inventory System" }} - Stock Ledger Report'
                },
                {
                    extend: 'pdf',
                    className: 'btn btn-primary',
                    title: '{{ \App\Models\GeneralSetting::first()->site_name ?? "Inventory System" }} - Stock Ledger Report'
                },
                {
                    extend: 'print',
                    className: 'btn btn-primary',
                    title: '{{ \App\Models\GeneralSetting::first()->site_name ?? "Inventory System" }} - Stock Ledger Report'
                }
            ],
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.stock-ledger.index') }}",
            columns: [
                {data: 'date', name: 'created_at'},
                {data: 'image', name: 'image', orderable: false, searchable: false},
                {data: 'product_name', name: 'product_name'},
                {data: 'variant_name', name: 'variant_name'},
                {data: 'reference', name: 'reference'},
                {data: 'type', name: 'type', orderable: false, searchable: false},
                {data: 'in_qty', name: 'in_qty'},
                {data: 'out_qty', name: 'out_qty'},
                {data: 'balance_qty', name: 'balance_qty'}
            ],
            order: [[0, "desc"]]
        });
    </script>
@endpush
