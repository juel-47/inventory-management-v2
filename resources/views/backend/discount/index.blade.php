@extends('backend.layouts.master')
@section('title', 'Discount Rules')
@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Discount Rules</h1>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>All Discount Rules</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.discounts.create') }}" class="btn btn-primary"><i
                                        class="fas fa-plus"></i> Create New</a>
                            </div>
                        </div>
                        <div class="table-responsive card-body">
                            {{ $dataTable->table(['class' => 'table table-striped table-bordered', 'id' => 'discount-table']) }}
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
            $('body').on('click', '.change-status', function() {
                let isChecked = $(this).is(':checked');
                let id = $(this).data('id');

                $.ajax({
                    url: "{{ route('admin.discounts.change-status') }}",
                    method: 'put',
                    data: {
                        id: id,
                        status: isChecked
                    },
                    success: function(data) {
                        toastr.success(data.message)
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || 'Status update failed';
                        toastr.error(message);
                        $('#discount-table').DataTable().ajax.reload(null, false);
                    }
                })
            });

            $('body').on('click', '.set-default', function() {
                let id = $(this).data('id');

                $.ajax({
                    url: "{{ route('admin.discounts.set-default') }}",
                    method: 'put',
                    data: {
                        id: id
                    },
                    success: function(data) {
                        toastr.success(data.message);
                        $('#discount-table').DataTable().ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || 'Default update failed';
                        toastr.error(message);
                    }
                });
            });
        })
    </script>
@endpush
