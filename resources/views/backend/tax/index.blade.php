@extends('backend.layouts.master')
@section('title', 'Tax / VAT')
@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Tax / VAT</h1>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>All Tax / VAT Rules</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.taxes.create') }}" class="btn btn-primary"><i
                                        class="fas fa-plus"></i> Create New</a>
                            </div>
                        </div>
                        <div class="table-responsive card-body">
                            {{ $dataTable->table(['class' => 'table table-striped table-bordered', 'id' => 'tax-table']) }}
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
                    url: "{{ route('admin.taxes.change-status') }}",
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
                        $('#tax-table').DataTable().ajax.reload(null, false);
                    }
                })
            });

            $('body').on('click', '.set-default', function() {
                let id = $(this).data('id');

                $.ajax({
                    url: "{{ route('admin.taxes.set-default') }}",
                    method: 'put',
                    data: {
                        id: id
                    },
                    success: function(data) {
                        toastr.success(data.message);
                        $('#tax-table').DataTable().ajax.reload(null, false);
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

