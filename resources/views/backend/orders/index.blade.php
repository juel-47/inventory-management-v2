@extends('backend.layouts.master')
@section('title', 'Frontend Orders')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Frontend Orders</h1>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>All Frontend Orders</h4>
                                <!-- Filter moved to DataTables via JS, hidden initially -->
                                <div id="custom-status-filter" style="display: none; align-items: center; gap: 10px;">
                                    <label for="filter_status" class="mb-0" style="white-space: nowrap;"><strong>Filter by Status:</strong></label>
                                    <select id="filter_status" class="form-control form-control-sm" style="width: auto;">
                                        <option value="">All Orders</option>
                                        <option value="pending">Pending</option>
                                        <option value="approved">Approved</option>
                                        {{-- <option value="processing">Processing</option> --}}
                                        {{-- <option value="shipped">Shipped</option> --}}
                                        <option value="completed">Completed</option>
                                        {{-- <option value="rejected">Rejected</option> --}}
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                    <button type="button" id="reset_filter" class="btn btn-danger btn-sm" style="display: none;">Reset</button>
                                </div>
                        </div>
                        <div class="table-responsive card-body">
                            {{ $dataTable->table(['class' => 'table table-striped table-bordered', 'id' => 'order-table']) }}
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
            // Inject custom filter into DataTables DOM once initialized
            $('#order-table').on('init.dt', function() {
                var filterWrapper = $('#custom-status-filter');
                filterWrapper.css('display', 'flex'); // Show it
                
                var dtFilter = $('.dataTables_filter');
                // Align the native search and custom filter side-by-side
                dtFilter.css({
                    'display': 'flex',
                    'align-items': 'center',
                    'justify-content': 'flex-end',
                    'gap': '15px'
                });
                // Prepend custom filter container before the search box label
                dtFilter.prepend(filterWrapper);
            });

            $('#filter_status').on('change', function() {
                if ($(this).val()) {
                    $('#reset_filter').show();
                } else {
                    $('#reset_filter').hide();
                }

                if(window.LaravelDataTables && window.LaravelDataTables['order-table']) {
                    window.LaravelDataTables['order-table'].ajax.reload();
                }
            });

            $('#reset_filter').on('click', function() {
                $('#filter_status').val('').trigger('change');
            });
        });
    </script>
@endpush

