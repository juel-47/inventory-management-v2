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
                            <h4>Filter Ledger</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="filter-product">Product</label>
                                        <select id="filter-product" class="form-control select2" data-placeholder="Select Product">
                                            <option value="">All Products</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="filter-variant">Variant</label>
                                        <select id="filter-variant" class="form-control select2" data-placeholder="Select Variant">
                                            <option value="">All Variants</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="filter-reference-type">Reference Type</label>
                                        <select id="filter-reference-type" class="form-control">
                                            <option value="">All References</option>
                                            @foreach ($referenceTypes as $referenceType)
                                                <option value="{{ $referenceType }}">{{ ucfirst($referenceType) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="filter-movement-type">Movement</label>
                                        <select id="filter-movement-type" class="form-control">
                                            <option value="">All</option>
                                            <option value="in">IN</option>
                                            <option value="out">OUT</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="filter-date-from">Date From</label>
                                        <input type="date" id="filter-date-from" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="filter-date-to">Date To</label>
                                        <input type="date" id="filter-date-to" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="filter-user">User / Outlet</label>
                                        <select id="filter-user" class="form-control select2" data-placeholder="All Users">
                                            <option value="">All Users</option>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}">
                                                    {{ $user->name }} {{ $user->outlet_name ? '(' . $user->outlet_name . ')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div>
                                            <button type="button" class="btn btn-danger border" id="reset-ledger-filters">Reset</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

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
                                            <th>Outlet</th>
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
        const ledgerProducts = @json($ledgerProducts);

        function renderVariantOptions(productId) {
            const $variant = $('#filter-variant');
            const variants = productId && Object.prototype.hasOwnProperty.call(ledgerProducts, String(productId))
                ? ledgerProducts[String(productId)]
                : [];

            $variant.empty().append('<option value="">All Variants</option>');

            variants.forEach(function (variant) {
                $variant.append(new Option(variant.label, variant.id));
            });

            $variant.trigger('change.select2');
        }

        $('.select2').select2({
            width: '100%',
            allowClear: true,
            placeholder: function () {
                return $(this).data('placeholder') || 'Select Option';
            }
        });

        const ledgerTable = $("#table-ledger").DataTable({
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
            ajax: {
                url: "{{ route('admin.stock-ledger.index') }}",
                data: function (d) {
                    d.product_id = $('#filter-product').val();
                    d.variant_id = $('#filter-variant').val();
                    d.reference_type = $('#filter-reference-type').val();
                    d.movement_type = $('#filter-movement-type').val();
                    d.date_from = $('#filter-date-from').val();
                    d.date_to = $('#filter-date-to').val();
                    d.user_id = $('#filter-user').val();
                }
            },
            columns: [
                {data: 'date', name: 'created_at'},
                {data: 'image', name: 'image', orderable: false, searchable: false},
                {data: 'product_name', name: 'product_name'},
                {data: 'variant_name', name: 'variant_name'},
                {data: 'reference', name: 'reference'},
                {data: 'outlet', name: 'outlet', orderable: false, searchable: false},
                {data: 'type', name: 'type', orderable: false, searchable: false},
                {data: 'in_qty', name: 'in_qty'},
                {data: 'out_qty', name: 'out_qty'},
                {data: 'balance_qty', name: 'balance_qty'}
            ],
            order: [[0, "desc"]]
        });

        $('#filter-product').on('change', function () {
            renderVariantOptions($(this).val());
            ledgerTable.ajax.reload();
        });

        $('#filter-reference-type, #filter-movement-type, #filter-date-from, #filter-date-to, #filter-variant, #filter-user').on('change', function () {
            ledgerTable.ajax.reload();
        });

        $('#reset-ledger-filters').on('click', function () {
            $('#filter-product').val('').trigger('change.select2');
            $('#filter-reference-type').val('');
            $('#filter-movement-type').val('');
            $('#filter-date-from').val('');
            $('#filter-date-to').val('');
            $('#filter-user').val('').trigger('change.select2');
            renderVariantOptions('');
            ledgerTable.ajax.reload();
        });

        renderVariantOptions($('#filter-product').val());
    </script>
@endpush
