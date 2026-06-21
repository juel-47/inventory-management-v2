@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Stock Returns</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Stock Returns</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h4>Filter Returns</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label for="filter-status">Status</label>
                                        <select id="filter-status" class="form-control">
                                            <option value="">All Statuses</option>
                                            <option value="pending">Pending</option>
                                            <option value="approved">Approved</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label for="filter-date-from">Date From</label>
                                        <input type="date" id="filter-date-from" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label for="filter-date-to">Date To</label>
                                        <input type="date" id="filter-date-to" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="button" class="btn btn-secondary mr-2" id="reset-filters">Reset</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h4>All Stock Returns</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.issue-returns.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Create Return Request
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-returns" style="width: 100%;">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Return No</th>
                                            <th>Issue No</th>
                                            <th>Outlet / User</th>
                                            <th>Refund Amount</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
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
        $(document).ready(function() {
            const table = $("#table-returns").DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.issue-returns.index') }}",
                    data: function (d) {
                        d.status = $('#filter-status').val();
                        d.date_from = $('#filter-date-from').val();
                        d.date_to = $('#filter-date-to').val();
                    }
                },
                columns: [
                    {data: 'date', name: 'created_at'},
                    {data: 'return_no', name: 'return_no'},
                    {data: 'issue_no', name: 'issue_no', orderable: false, searchable: false},
                    {data: 'outlet_name', name: 'outlet_name', orderable: false, searchable: false},
                    {data: 'refund_amount', name: 'refund_amount'},
                    {data: 'status', name: 'status'},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                order: [[0, "desc"]]
            });

            $('#filter-status, #filter-date-from, #filter-date-to').on('change', function () {
                table.ajax.reload();
            });

            $('#reset-filters').on('click', function () {
                $('#filter-status').val('');
                $('#filter-date-from').val('');
                $('#filter-date-to').val('');
                table.ajax.reload();
            });
        });
    </script>
@endpush
