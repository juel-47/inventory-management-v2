@extends('backend.layouts.master')

@section('title', 'Create Order Receive Invoice')

@section('content')
    <section class="section">
        {{-- Header --}}
        <div class="section-header">
            <div class="d-flex align-items-center flex-wrap w-100">
                <h1 class="mb-2 mb-sm-0 d-flex align-items-center">
                    <i class="fas fa-file-invoice mr-2 text-primary"></i>
                    Create Order Receive Invoice
                </h1>
                <div class="ml-auto d-flex align-items-center flex-wrap">
                    <div class="section-header-breadcrumb">
                        <div class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                            </a>
                        </div>
                        <div class="breadcrumb-item">
                            <a href="{{ route('admin.purchases.index') }}">Order Receive</a>
                        </div>
                        <div class="breadcrumb-item active">Create</div>
                    </div>
                    <button class="btn btn-primary btn-sm ml-2 shadow-sm" type="button" data-toggle="modal" data-target="#importModal">
                        <i class="fas fa-file-import mr-1"></i>
                        <span class="d-none d-sm-inline">Import from Order Place</span>
                        <span class="d-sm-none">Import</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        {{-- Card Header --}}
                        <div class="card-header bg-white py-3 border-0">
                            <div class="d-flex align-items-center">
                                <div class="mr-3 p-2 bg-primary rounded-circle text-white d-none d-sm-flex">
                                    <i class="fas fa-plus-circle"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 font-weight-bold text-dark">New Order Receive</h5>
                                    <small class="text-muted">Create a new purchase invoice</small>
                                </div>
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="card-body p-4">
                            <form action="{{ route('admin.purchases.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                
                                {{-- Section 1: General Information --}}
                                <div class="section-title mb-4">
                                    <span class="font-weight-bold text-primary">
                                        <i class="fas fa-info-circle mr-2"></i>General Information
                                    </span>
                                    <hr>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label class="font-weight-bold text-dark">
                                            Vendor <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control form-control-lg select2 @error('vendor_id') is-invalid @enderror" 
                                                name="vendor_id" id="vendor_select" required>
                                            <option value="">Select Vendor</option>
                                            @foreach ($vendors as $vendor)
                                                <option value="{{ $vendor->id }}">{{ $vendor->shop_name }}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">System Rate: <strong id="current_rate_display">1.00</strong></small>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label class="font-weight-bold text-dark">Shipping Method</label>
                                        <select class="form-control form-control-lg @error('shipping_method') is-invalid @enderror" 
                                                name="shipping_method" id="shipping_method_select">
                                            <option value="">Select Shipping</option>
                                            <option value="Air">Air</option>
                                            <option value="Train">Train</option>
                                            <option value="Ship">Ship</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label class="font-weight-bold text-dark">
                                            Purchase Date <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" class="form-control form-control-lg @error('date') is-invalid @enderror" 
                                               name="date" value="{{ date('Y-m-d') }}" required>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label class="font-weight-bold text-dark">Reference / Note</label>
                                        <input type="text" class="form-control form-control-lg @error('note') is-invalid @enderror" 
                                               name="note" placeholder="Optional reference...">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label class="font-weight-bold text-dark">Invoice Attachment <span class="text-muted">(PDF, Excel, Image)</span></label>
                                        <input type="file" class="form-control form-control-lg @error('invoice_attachment') is-invalid @enderror" 
                                               name="invoice_attachment">
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label class="font-weight-bold text-dark">Pricing Rule (Multiplier)</label>
                                        <select class="form-control form-control-lg select2 @error('pricing_rule_id') is-invalid @enderror" 
                                                name="pricing_rule_id" id="pricing_rule_select">
                                            <option value="">Manual Prices</option>
                                            @if(isset($pricingRules))
                                                @foreach($pricingRules as $rule)
                                                    <option value="{{ $rule->id }}" {{ (isset($defaultPricingRuleId) && $defaultPricingRuleId == $rule->id) ? 'selected' : '' }}>
                                                        {{ $rule->name }} (Sale ×{{ $rule->sale_multiplier }}, Outlet ×{{ $rule->outlet_multiplier }})
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <small class="text-muted">When selected, Sale/Outlet price auto-calculates from Local Unit Cost.</small>
                                    </div>

                                    <input type="hidden" name="booking_id" id="booking_id_hidden">
                                </div>

                                {{-- Section 2: Invoice Items --}}
                                <div class="section-title mb-4 mt-4">
                                    <span class="font-weight-bold text-primary">
                                        <i class="fas fa-list-ul mr-2"></i>Invoice Items
                                    </span>
                                    <hr>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm" id="product_table">
                                        <thead class="bg-light text-center">
                                            <tr>
                                                <th width="4%">Image</th>
                                                <th width="22%">Product Details</th>
                                                <th width="8%" id="vendor_unit_cost_header">Cost (Vendor)</th>
                                                <th width="6%">Qty</th>
                                                <th width="10%" id="vendor_subtotal_header">Total (Vendor)</th>
                                                <th width="8%">Raw Cost</th>
                                                <th width="8%">Tax</th>
                                                <th width="8%">Transport</th>
                                                <th width="9%">Local Unit Cost</th>
                                                <th width="7%">Sale Price</th>
                                                <th width="7%">Outlet Price</th>
                                                <th width="3%"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="12" class="p-0">
                                                    <button type="button" class="btn btn-block btn-outline-primary border-dashed py-3" id="add_row_btn">
                                                        <i class="fas fa-plus-circle mr-2"></i> Add Another Product Line
                                                    </button>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                {{-- Section 3: Payment Summary --}}
                                <div class="section-title mb-4 mt-4">
                                    <span class="font-weight-bold text-primary">
                                        <i class="fas fa-calculator mr-2"></i>Payment Summary
                                    </span>
                                    <hr>
                                </div>

                                <div class="row justify-content-end">
                                    <div class="col-md-6 col-lg-5">
                                        <div class="form-group row mb-3">
                                            <label class="col-sm-6 col-form-label text-right font-weight-bold">Product Total (Vendor):</label>
                                            <div class="col-sm-6">
                                                <div class="form-control-plaintext font-weight-bold text-primary" id="vendor_grand_total">0.00</div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="form-group row">
                                            <label class="col-sm-6 col-form-label text-right h5 mb-0 font-weight-bold">Grand Total (System):</label>
                                            <div class="col-sm-6">
                                                <div class="h4 text-primary mb-0 font-weight-bold" id="grand_total_display">{{ $settings->currency_icon }}0.00</div>
                                                <input type="hidden" name="total_amount" id="total_amount_hidden">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Submit Buttons --}}
                                <div class="row mt-4 pt-3 border-top">
                                    <div class="col-12 text-right">
                                        <a href="{{ route('admin.purchases.index') }}" class="btn btn-outline-secondary px-4 mr-2">
                                            <i class="fas fa-times mr-1"></i> Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary px-5 shadow-sm">
                                            <i class="fas fa-check-circle mr-2"></i> Confirm & Save
                                        </button>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Import Modal --}}
    <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">
                        <i class="fas fa-file-import text-primary mr-2"></i>
                        Import from Order Place
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold text-dark">Select Pending Order Place</label>
                        <select class="form-control form-control-lg select2" id="booking_select" style="width: 100%;">
                            <option value="">Manual / None</option>
                            @foreach ($bookings as $booking)
                                <option value="{{ $booking->id }}">#{{ $booking->booking_no }} | {{ $booking->vendor->shop_name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted mt-2 d-block">Selecting a booking will auto-import items, vendor, and shipping method.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .border-dashed {
        border-style: dashed !important;
    }

    .border-dashed:hover {
        background: #f8f9fc !important;
        border-color: #4e73df !important;
        color: #4e73df !important;
    }

    .form-group label {
        font-size: 0.85rem;
        margin-bottom: 0.5rem;
    }

    .form-control {
        border-radius: 10px !important;
        border: 2px solid #e2e8f0;
        padding: 0.7rem 1rem;
        transition: all 0.3s ease;
        font-size: 0.95rem;
        background: #fafbfc;
    }

    .form-control:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.15);
        background: #ffffff;
    }

    .form-control-lg {
        height: 50px !important;
        font-size: 0.95rem !important;
    }

    .form-control-sm {
        height: 36px !important;
        font-size: 0.85rem !important;
        padding: 0.3rem 0.5rem !important;
    }

    .section-title span {
        font-size: 1.05rem;
        letter-spacing: 0.5px;
    }

    .section-title hr {
        border-top: 2px solid #e2e8f0;
        opacity: 0.5;
        margin-top: 0.5rem;
    }

    .table th {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background: #f8f9fc !important;
        color: #1a1a2e !important;
        font-weight: 700 !important;
        border-bottom: 2px solid #e2e8f0 !important;
        padding: 0.6rem 0.5rem !important;
    }

    .table td {
        vertical-align: middle !important;
        font-size: 0.85rem;
        padding: 0.5rem !important;
    }

    .btn {
        border-radius: 10px !important;
        font-weight: 600 !important;
        transition: all 0.3s ease !important;
        min-height: 44px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 0 1.5rem !important;
        font-size: 0.9rem !important;
    }

    .btn-sm {
        min-height: 36px !important;
        font-size: 0.8rem !important;
        padding: 0 1rem !important;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    }

    .btn-primary {
        background: #4e73df !important;
        border: none !important;
        color: #ffffff !important;
    }

    .btn-primary:hover {
        background: #224abe !important;
        box-shadow: 0 6px 20px rgba(78, 115, 223, 0.35) !important;
    }

    .btn-outline-primary {
        color: #4e73df !important;
        border-color: #4e73df !important;
    }

    .btn-outline-primary:hover {
        background: #4e73df !important;
        color: #fff !important;
    }

    .btn-outline-secondary {
        color: #858796 !important;
        border-color: #858796 !important;
    }

    .btn-outline-secondary:hover {
        background: #858796 !important;
        color: #fff !important;
    }

    .btn-outline-danger {
        color: #e74a3b !important;
        border-color: #e74a3b !important;
    }

    .btn-outline-danger:hover {
        background: #e74a3b !important;
        color: #fff !important;
    }

    .card {
        border-radius: 16px !important;
    }

    .modal-content {
        border-radius: 16px !important;
        border: none !important;
    }

    .select2-container--default .select2-selection--single {
        border-radius: 10px !important;
        border: 2px solid #e2e8f0 !important;
        height: 50px !important;
        padding: 0 12px;
        background: #fafbfc !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 44px !important;
        font-size: 0.95rem;
        color: #495057;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 48px !important;
    }

    .select2-dropdown {
        border-radius: 10px !important;
        border-color: #e2e8f0 !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    @media (max-width: 575.98px) {
        .section-header {
            padding: 12px 15px !important;
        }
        .section-header h1 {
            font-size: 1rem !important;
        }
        .card-body {
            padding: 15px !important;
        }
        .form-control-lg {
            height: 44px !important;
            font-size: 0.85rem !important;
        }
        .btn {
            font-size: 0.8rem !important;
            min-height: 38px !important;
            padding: 0 1rem !important;
        }
        .btn-sm {
            min-height: 34px !important;
            font-size: 0.75rem !important;
            padding: 0 0.8rem !important;
        }
        .table td {
            font-size: 0.75rem !important;
            padding: 0.4rem !important;
        }
        .table th {
            font-size: 0.6rem !important;
            padding: 0.4rem !important;
        }
        .text-right {
            text-align: center !important;
        }
        .text-right .btn {
            width: 100% !important;
            margin-bottom: 8px !important;
        }
        .select2-container--default .select2-selection--single {
            height: 44px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px !important;
        }
        .modal-dialog {
            margin: 10px !important;
        }
    }

    @media (max-width: 767.98px) {
        .section-header .ml-auto {
            width: 100% !important;
            justify-content: space-between !important;
        }
        .card-header .d-flex {
            flex-direction: column !important;
            align-items: flex-start !important;
        }
        .card-header .card-header-action {
            margin-top: 10px !important;
            width: 100% !important;
        }
        .card-header .card-header-action .btn {
            width: 100% !important;
        }
        .card-header .ml-auto {
            margin-left: 0 !important;
            margin-top: 8px !important;
            width: 100% !important;
        }
    }

    .card {
        animation: fadeInUp 0.4s ease-out;
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
        background: #4e73df;
        border-radius: 10px;
    }

    .form-control-plaintext {
        font-size: 1rem;
        font-weight: 700;
        padding: 0.3rem 0;
    }
</style>
@endpush

@push('scripts')
<script>
    const products = @json($products);
    const pricingRules = @json($pricingRules ?? []);
    const selectedIds = @json($selectedIds ?? []);
    let selectedPricingRuleId = {{ $defaultPricingRuleId ?? 'null' }};
    let rowCount = 0;
    let currentVendorRate = 1;
    let currentVendorIcon = '{{ $settings->currency_icon }}';
    let currentVendorName = '{{ $settings->currency_name }}';
    const systemIcon = '{{ $settings->currency_icon }}';

    $(document).ready(function() {
        if (selectedIds && selectedIds.length > 0) {
            $('#product_table tbody').empty();
            rowCount = 0;
            
            selectedIds.forEach((productId, index) => {
                let product = products.find(p => p.id == productId);
                if (product) {
                    addRow();
                    setTimeout(() => {
                        let rows = $('#product_table tbody tr');
                        let targetRow = rows.eq(index);
                        if (targetRow.length) {
                            targetRow.find('.product_select').val(product.id).trigger('change');
                        }
                    }, 200 * (index + 1));
                }
            });
            
            setTimeout(() => {
                toastr.success(selectedIds.length + ' product(s) loaded from Low Stock Alert.');
            }, 500);
        }

        $('#pricing_rule_select').on('change', function() {
            const val = $(this).val();
            selectedPricingRuleId = val ? parseInt(val) : null;
            applyPricingToAllRows();
        });
        
        $('#importModal').on('shown.bs.modal', function () {
            $('#booking_select').select2({
                dropdownParent: $('#importModal'),
                width: '100%'
            });
        });
        
        $('#booking_select').on('change', function() {
            let bookingId = $(this).val();
            $('#product_table tbody').empty();
            rowCount = 0;
            
            if(bookingId) {
                $('#booking_id_hidden').val(bookingId); 
                $.ajax({
                    url: "{{ route('admin.purchases.get-booking-details') }}",
                    method: 'GET',
                    data: { id: bookingId },
                    success: function(bookings) {
                        if(bookings.length > 0 && bookings[0].vendor_id) {
                            $('#vendor_select').val(bookings[0].vendor_id).trigger('change');
                        }
                        if(bookings.length > 0 && bookings[0].shipping_method) {
                            $('#shipping_method_select').val(bookings[0].shipping_method);
                        }
                        bookings.forEach(booking => {
                            addBookingRow(booking);
                        });
                        toastr.success(bookings.length + ' item(s) imported from Order Place.', 'Loaded');
                        $('#importModal').modal('hide');
                    }
                });
            } else {
                $('#booking_id_hidden').val('');
                addRow();
            }
        });

        $('#vendor_select').on('change', function() {
            let vendorId = $(this).val();
            if (vendorId) {
                $.ajax({
                    url: "{{ route('admin.vendor.get-details') }}",
                    method: 'GET',
                    data: { id: vendorId },
                    success: function(data) {
                        currentVendorRate = data.currency_rate;
                        currentVendorIcon = data.currency_icon;
                        currentVendorName = data.currency_name;
                        updateCurrencyMetadata();
                        recalculateAllRows();
                    }
                });
            } else {
                currentVendorRate = 1;
                currentVendorIcon = '{{ $settings->currency_icon }}';
                currentVendorName = '{{ $settings->currency_name }}';
                updateCurrencyMetadata();
                recalculateAllRows();
            }
        });

        if (!selectedIds || selectedIds.length === 0) {
            addRow();
        }
        
        $('#add_row_btn').on('click', function() { addRow(); });
        
        $(document).on('click', '.remove_row', function() {
            $(this).closest('tr').remove();
            calculateGrandTotal();
        });

        $(document).on('change', '.product_select', function() {
            let id = $(this).val();
            let row = $(this).closest('tr');
            let product = products.find(p => p.id == id);
            if(product) {
                row.find('.unit_cost').val(product.purchase_price); 
                
                let imgContainer = row.find('td:first-child');
                if(product.thumb_image) {
                    imgContainer.html(`<img src="{{ asset('storage') }}/${product.thumb_image}" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">`);
                } else {
                    imgContainer.html(`<div class="bg-light rounded d-flex align-items-center justify-content-center text-muted small" style="width: 40px; height: 40px;"><i class="fas fa-box"></i></div>`);
                }

                let productInfo = `<strong>${product.name}</strong>`;
                if(product.product_number) productInfo += `<br><small class="text-muted">Item #: ${product.product_number}</small>`;
                if(product.category && product.category.name) productInfo += `<br><small class="text-muted">Category: ${product.category.name}</small>`;
                row.find('.product-info').html(productInfo);
                
                row.find('.raw_material_cost').val(product.raw_material_cost || 0);
                row.find('.tax_cost').val(product.tax || 0);
                row.find('.transport_cost').val(product.transport_cost || 0);
                row.find('.sale_price').val(product.price || 0);
                row.find('.outlet_price').val(product.outlet_price || 0);
                
                let variantHtml = '';
                if(product.variants && product.variants.length > 0) {
                    variantHtml += '<div class="mt-2"><table class="table table-sm table-bordered mb-0" style="font-size: 12px; background: white;"><tbody>';
                    product.variants.forEach(v => {
                        let colorName = v.color ? v.color.name : '';
                        let sizeName = v.size ? v.size.name : '';
                        let name = (colorName + ' ' + sizeName).trim() || 'Default';
                        variantHtml += `<tr><td class="p-1">${name}</td><td class="p-1" width="60"><input type="number" class="form-control form-control-sm variant-qty-input p-0 text-center" data-key="${name}" value="0" min="0" style="height: 22px; font-size: 11px;"></td></tr>`;
                    });
                    variantHtml += '</tbody></table></div>';
                }
                row.find('.variant-container').html(variantHtml);
                row.find('.variant_info_hidden').val('');
                
                calculateLocalUnitCost(row);
                calculateRowTotal(row);
                applyPricingToRow(row);
            }
        });

        $(document).on('input', '.qty, .unit_cost', function() {
            let row = $(this).closest('tr');
            if ($(this).hasClass('unit_cost')) {
                let vendorCost = parseFloat($(this).val()) || 0;
                row.find('.raw_material_cost').val((vendorCost * currentVendorRate).toFixed(2));
                if (vendorCost === 0) {
                    let existingTax = parseFloat(row.find('.tax_cost').val()) || 0;
                    let existingTransport = parseFloat(row.find('.transport_cost').val()) || 0;
                    if (existingTax < 0.01) row.find('.tax_cost').val('0.00');
                    if (existingTransport < 0.01) row.find('.transport_cost').val('0.00');
                }
            }
            calculateRowTotal(row);
            calculateLocalUnitCost(row);
            applyPricingToRow(row);
        });

        $(document).on('input', '.raw_material_cost, .tax_cost, .transport_cost', function() {
            let row = $(this).closest('tr');
            calculateLocalUnitCost(row);
            calculateRowTotal(row);
            applyPricingToRow(row);
        });
        
        $(document).on('input', '.variant-qty-input', function() {
            let row = $(this).closest('.main-row');
            let container = row.find('.variant-container');
            let totalQty = 0;
            let newVariantInfo = {};
            
            container.find('.variant-qty-input').each(function() {
                let val = parseInt($(this).val()) || 0;
                let key = $(this).data('key');
                totalQty += val;
                if(val > 0) newVariantInfo[key] = val;
            });
            
            row.find('.qty').val(totalQty);
            row.find('.variant_info_hidden').val(JSON.stringify(newVariantInfo));
            calculateRowTotal(row);
        });
    });

    function updateCurrencyMetadata() {
        $('#vendor_unit_cost_header').text('Unit Cost (' + currentVendorName + ')');
        $('#vendor_subtotal_header').text('Total (' + currentVendorName + ')');
        $('#current_rate_display').text(currentVendorRate);
    }

    function addBookingRow(booking) {
        let product = booking.product;
        let variantHtml = '';
        let variantInput = '';
        let hasVariants = false;
        
        if(booking.variant_info) {
            variantInput = JSON.stringify(booking.variant_info);
            let variants = booking.variant_info['variant'] ? {[booking.variant_info['variant']]: booking.qty} : booking.variant_info;
            variantHtml += '<div class="mt-2 bg-light rounded p-2" style="font-size: 11px;">';
            for (const [key, qty] of Object.entries(variants)) {
                hasVariants = true;
                let cleanKey = key.replace(/Color:\s*/gi, '').replace(/Size:\s*/gi, '').replace(/\s*-\s*/g, ' ').trim();
                variantHtml += `
                    <div class="d-flex justify-content-between align-items-center mb-1 last:mb-0">
                        <span class="text-dark font-weight-500">${cleanKey}</span>
                        <input type="number" class="form-control form-control-sm variant-qty-input p-0 text-center" 
                               data-key="${cleanKey}" value="${qty}" min="0" 
                               style="height: 20px; width: 50px; font-size: 11px; border: 1px solid #ced4da;">
                    </div>`;
            }
            variantHtml += '</div>';
        }
        if(!hasVariants) { variantHtml = ''; variantInput = ''; }

        let imageHtml = product.thumb_image 
            ? `<img src="{{ asset('storage') }}/${product.thumb_image}" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">`
            : `<div class="bg-light rounded d-flex align-items-center justify-content-center text-muted small" style="width: 40px; height: 40px;"><i class="fas fa-box"></i></div>`;

        let productInfo = `<strong>${product.name}</strong>`;
        if(product.product_number) productInfo += `<br><small class="text-muted">Item #: ${product.product_number}</small>`;

        if(booking.vendor && booking.vendor.currency_rate) {
            currentVendorRate = booking.vendor.currency_rate;
            currentVendorIcon = booking.vendor.currency_icon;
            currentVendorName = booking.vendor.currency_name;
            updateCurrencyMetadata();
        }
        
        let html = `
            <tr class="main-row">
                <td class="align-middle text-center">${imageHtml}</td>
                <td class="align-middle product_select_col">
                    <select class="form-control form-control-sm product_select select2" name="items[${rowCount}][product_id]" required>
                        <option value="${product.id}" selected>${product.name}</option>
                        ${products.map(p => p.id != product.id ? `<option value="${p.id}">${p.name}</option>` : '').join('')}
                    </select>
                    <div class="product-info mt-1" style="font-size: 12px; line-height: 1.3; color: #666;">${productInfo}</div>
                    <div class="variant-container">${variantHtml}</div>
                    <input type="hidden" class="variant_info_hidden" name="items[${rowCount}][variant_info]" value='${variantInput}'>
                </td>
                <td class="align-middle text-center">
                    <input type="number" class="form-control form-control-sm unit_cost text-center" name="items[${rowCount}][unit_cost]" step="any" value="${booking.unit_price}" required>
                </td>
                <td class="align-middle text-center">
                    <input type="number" class="form-control form-control-sm qty text-center" name="items[${rowCount}][qty]" value="${booking.qty}" min="1" required style="font-weight: bold;">
                </td>
                <td class="align-middle text-right">
                    <input type="text" class="form-control-plaintext form-control-sm subtotal mb-0 text-dark text-right font-weight-bold pr-2" readonly value="0.00">
                </td>
                <td class="align-middle text-center">
                    <input type="number" class="form-control form-control-sm raw_material_cost text-center" name="items[${rowCount}][raw_material_cost]" value="${product.raw_material_cost || 0}" step="any">
                </td>
                <td class="align-middle text-center">
                    <input type="number" class="form-control form-control-sm tax_cost text-center" name="items[${rowCount}][tax_cost]" value="${product.tax || 0}" step="any">
                </td>
                <td class="align-middle text-center">
                    <input type="number" class="form-control form-control-sm transport_cost text-center" name="items[${rowCount}][transport_cost]" value="${product.transport_cost || 0}" step="any">
                </td>
                <td class="align-middle text-center">
                    <div class="form-control-plaintext form-control-sm local_unit_cost mb-0 text-primary text-center font-weight-bold pr-2">0.00</div>
                </td>
                <td class="align-middle text-center">
                    <input type="number" class="form-control form-control-sm sale_price text-center" name="items[${rowCount}][sale_price]" value="${product.price || 0}" step="any">
                </td>
                <td class="align-middle text-center">
                    <input type="number" class="form-control form-control-sm outlet_price text-center" name="items[${rowCount}][outlet_price]" value="${product.outlet_price || 0}" step="any">
                </td>
                <td class="align-middle text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm remove_row" style="padding: 2px 7px;"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
        $('#product_table tbody').append(html);
        $('.select2').select2({ width: '100%' });
        let newRow = $('#product_table tbody tr').last();
        calculateLocalUnitCost(newRow);
        calculateRowTotal(newRow);
        applyPricingToRow(newRow);
        rowCount++;
    }

    function addRow() {
        let html = `
            <tr class="main-row">
                <td class="align-middle text-center">
                    <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted small" style="width: 40px; height: 40px;"><i class="fas fa-box"></i></div>
                </td>
                <td class="align-middle product_select_col">
                    <select class="form-control form-control-sm product_select select2" name="items[${rowCount}][product_id]" required>
                        <option value="">Select Product...</option>
                        ${products.map(p => `<option value="${p.id}">${p.name}</option>`).join('')}
                    </select>
                    <div class="product-info mt-2" style="font-size: 12px; line-height: 1.4; color: #666;"></div>
                    <div class="variant-container"></div>
                    <input type="hidden" class="variant_info_hidden" name="items[${rowCount}][variant_info]">
                </td>
                <td class="align-middle text-center">
                    <input type="number" class="form-control form-control-sm unit_cost text-center" name="items[${rowCount}][unit_cost]" step="any" required placeholder="0.00">
                </td>
                <td class="align-middle text-center">
                    <input type="number" class="form-control form-control-sm qty text-center" name="items[${rowCount}][qty]" value="1" min="1" required style="font-weight: bold;">
                </td>
                <td class="align-middle text-right">
                    <input type="text" class="form-control-plaintext form-control-sm subtotal mb-0 text-dark text-right font-weight-bold pr-2" readonly value="0.00">
                </td>
                <td class="align-middle text-center">
                    <input type="number" class="form-control form-control-sm raw_material_cost text-center" name="items[${rowCount}][raw_material_cost]" placeholder="0.00" step="any" value="0">
                </td>
                <td class="align-middle text-center">
                    <input type="number" class="form-control form-control-sm tax_cost text-center" name="items[${rowCount}][tax_cost]" placeholder="0.00" step="any" value="0">
                </td>
                <td class="align-middle text-center">
                    <input type="number" class="form-control form-control-sm transport_cost text-center" name="items[${rowCount}][transport_cost]" placeholder="0.00" step="any" value="0">
                </td>
                <td class="align-middle text-center">
                    <div class="form-control-plaintext form-control-sm local_unit_cost mb-0 text-primary text-center font-weight-bold pr-2">0.00</div>
                </td>
                <td class="align-middle text-center">
                    <input type="number" class="form-control form-control-sm sale_price text-center" name="items[${rowCount}][sale_price]" placeholder="0.00" step="any">
                </td>
                <td class="align-middle text-center">
                    <input type="number" class="form-control form-control-sm outlet_price text-center" name="items[${rowCount}][outlet_price]" placeholder="0.00" step="any">
                </td>
                <td class="align-middle text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm remove_row" style="padding: 2px 7px;"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
        $('#product_table tbody').append(html);
        $('.select2').select2(); 
        let newRow = $('#product_table tbody tr').last();
        calculateLocalUnitCost(newRow);
        calculateRowTotal(newRow);
        applyPricingToRow(newRow);
        rowCount++;
    }

    function getSelectedPricingRule() {
        if (!selectedPricingRuleId) return null;
        return pricingRules.find(r => parseInt(r.id) === parseInt(selectedPricingRuleId)) || null;
    }

    function getLocalUnitCostNumber(row) {
        let rawMaterial = parseFloat(row.find('.raw_material_cost').val()) || 0;
        let tax = parseFloat(row.find('.tax_cost').val()) || 0;
        let transport = parseFloat(row.find('.transport_cost').val()) || 0;
        return rawMaterial + tax + transport;
    }

    function applyPricingToRow(row) {
        const rule = getSelectedPricingRule();
        if (!rule) return;
        const unitCost = getLocalUnitCostNumber(row);
        const sale = unitCost * (parseFloat(rule.sale_multiplier) || 0);
        const outlet = unitCost * (parseFloat(rule.outlet_multiplier) || 0);
        row.find('.sale_price').val(sale.toFixed(2));
        row.find('.outlet_price').val(outlet.toFixed(2));
    }

    function applyPricingToAllRows() {
        $('#product_table tbody tr').each(function() {
            applyPricingToRow($(this));
        });
    }

    function calculateLocalUnitCost(row) {
        let rawMaterial = parseFloat(row.find('.raw_material_cost').val()) || 0;
        let tax = parseFloat(row.find('.tax_cost').val()) || 0;
        let transport = parseFloat(row.find('.transport_cost').val()) || 0;
        let totalLocalCost = rawMaterial + tax + transport;
        row.find('.local_unit_cost').text(systemIcon + totalLocalCost.toFixed(2));
    }

    function calculateRowTotal(row) {
        let qty = parseFloat(row.find('.qty').val()) || 0;
        let vendorCost = parseFloat(row.find('.unit_cost').val()) || 0;
        let vendorTotal = qty * vendorCost;
        row.find('.subtotal').val(vendorTotal.toFixed(2));
        calculateLocalUnitCost(row);
        calculateGrandTotal();
    }

    function recalculateAllRows() {
        $('#product_table tbody tr').each(function() { calculateRowTotal($(this)); });
    }

    function calculateGrandTotal() {
        let vendorTotal = 0;
        let systemTotal = 0;
        
        $('#product_table tbody tr').each(function() {
            let row = $(this);
            let qty = parseFloat(row.find('.qty').val()) || 0;
            let vendorCost = parseFloat(row.find('.unit_cost').val()) || 0;
            let raw = parseFloat(row.find('.raw_material_cost').val()) || 0;
            let tax = parseFloat(row.find('.tax_cost').val()) || 0;
            let transport = parseFloat(row.find('.transport_cost').val()) || 0;
            let localUnitCost = raw + tax + transport;
            vendorTotal += qty * vendorCost;
            systemTotal += localUnitCost * qty;
        });

        $('#vendor_grand_total').text(currentVendorIcon + vendorTotal.toFixed(2));
        $('#grand_total_display').text(systemIcon + systemTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#total_amount_hidden').val(systemTotal.toFixed(2)); 
    }
</script>
@endpush