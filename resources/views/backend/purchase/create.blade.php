@extends('backend.layouts.master')
@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Create Order Receive Invoice</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.purchases.index') }}">Order Receive</a></div>
                <div class="breadcrumb-item">Create</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h4>New Order Receive</h4>
                            <div class="card-header-action">
                                <button class="btn btn-primary" type="button" data-toggle="modal" data-target="#importModal">
                                    <i class="fas fa-file-import"></i> Import from Order Place
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.purchases.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                
                                <!-- Section 1: General Information -->
                                <div class="section-title mt-0">General Information</div>
                                <div class="row mb-4">
                                    <div class="form-group col-md-4">
                                        <label>Vendor <span class="text-danger">*</span></label>
                                        <select class="form-control select2" name="vendor_id" id="vendor_select" required>
                                            <option value="">Select Vendor</option>
                                            @foreach ($vendors as $vendor)
                                                <option value="{{ $vendor->id }}">{{ $vendor->shop_name }}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted mt-1 d-block">System Rate: <strong id="current_rate_display">1.00</strong></small>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Shipping Method</label>
                                        <select class="form-control" name="shipping_method" id="shipping_method_select">
                                            <option value="">-- Select Shipping --</option>
                                            <option value="Air">Air</option>
                                            <option value="Train">Train</option>
                                            <option value="Ship">Ship</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Purchase Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="date" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Reference / Note</label>
                                        <input type="text" class="form-control" name="note" placeholder="Optional reference...">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Invoice Attachment <span class="text-muted">(PDF, Excel, Image)</span></label>
                                        <input type="file" class="form-control" name="invoice_attachment">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Pricing Rule (Multiplier)</label>
                                        <select class="form-control select2" name="pricing_rule_id" id="pricing_rule_select">
                                            <option value="">-- Manual Prices --</option>
                                            @if(isset($pricingRules))
                                                @foreach($pricingRules as $rule)
                                                    <option value="{{ $rule->id }}" {{ (isset($defaultPricingRuleId) && $defaultPricingRuleId == $rule->id) ? 'selected' : '' }}>
                                                        {{ $rule->name }} (Sale ×{{ $rule->sale_multiplier }}, Outlet ×{{ $rule->outlet_multiplier }})
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <small class="text-muted d-block mt-1">When selected, Sale/Outlet price auto-calculates from Local Unit Cost.</small>
                                    </div>
                                    <input type="hidden" name="booking_id" id="booking_id_hidden">
                                </div>

                                <!-- Section 2: Invoice Items -->
                                <div class="section-title">Invoice Items</div>
                                <div class="table-responsive mb-4">
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
                                            <!-- Dynamic Rows -->
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="11" class="p-0">
                                                    <button type="button" class="btn btn-block btn-outline-primary border-dashed py-3" id="add_row_btn">
                                                        <i class="fas fa-plus-circle"></i> Add Another Product Line
                                                    </button>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <!-- Section 3: Payment Summary & Fees -->
                                <div class="section-title">Payment Summary & Extra Fees</div>
                                <div class="row justify-content-end mb-4">
                                    <div class="col-md-6 col-lg-5">
                                        <div class="form-group row mb-2">
                                            <label class="col-sm-6 col-form-label text-right">Product Total (Vendor):</label>
                                            <div class="col-sm-6">
                                                <div class="form-control-plaintext font-weight-bold" id="vendor_grand_total">0.00</div>
                                            </div>
                                        </div>
                                        {{-- <div class="form-group row mb-2">
                                            <label class="col-sm-6 col-form-label text-right">Raw Material Cost:</label>
                                            <div class="col-sm-6">
                                                <input type="number" name="material_cost" class="form-control form-control-sm extra_cost_input" step="any" value="0.00">
                                            </div>
                                        </div>
                                        <div class="form-group row mb-2">
                                            <label class="col-sm-6 col-form-label text-right">Transport Cost:</label>
                                            <div class="col-sm-6">
                                                <input type="number" name="transport_cost" class="form-control form-control-sm extra_cost_input" step="any" value="0.00">
                                            </div>
                                        </div>
                                        <div class="form-group row mb-2">
                                            <label class="col-sm-6 col-form-label text-right">Tax / VAT:</label>
                                            <div class="col-sm-6">
                                                <input type="number" name="tax" class="form-control form-control-sm extra_cost_input" step="any" value="0.00">
                                            </div>
                                        </div> --}}
                                        <hr>
                                        <div class="form-group row">
                                            <label class="col-sm-6 col-form-label text-right h5 mb-0">Grand Total (System):</label>
                                            <div class="col-sm-6">
                                                <div class="h4 text-primary mb-0 font-weight-bold" id="grand_total_display">{{ $settings->currency_icon }}0.00</div>
                                                <input type="hidden" name="total_amount" id="total_amount_hidden">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-5">
                                    <div class="col-12 text-right">
                                        <a href="{{ route('admin.purchases.index') }}" class="btn btn-secondary btn-lg mr-2">Cancel</a>
                                        <button type="submit" class="btn btn-primary btn-lg px-5">
                                            <i class="fas fa-check-circle mr-2"></i> Confirm and Save Purchase
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

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import from Order Place</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Pending Order Place</label>
                        <select class="form-control select2" id="booking_select" style="width: 100%;">
                            <option value="">-- Manual / None --</option>
                            @foreach ($bookings as $booking)
                                <option value="{{ $booking->id }}">#{{ $booking->booking_no }} | {{ $booking->vendor->shop_name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted mt-2 d-block">Selecting a booking will auto-import items, vendor, and shipping method.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <style>
        .border-dashed { border-style: dashed !important; }
        .unit_cost_system { font-size: 11px; color: #98a6ad; }
        .product_select_col .select2-container { width: 100% !important; }

        /* Mobile Responsive Table Breakdown */
        /* @media (max-width: 991.98px) {
            #product_table thead { display: none; }
            #product_table, #product_table tbody, #product_table tr, #product_table td { 
                display: block; 
                width: 100%; 
            }
            #product_table tr.main-row { 
                margin-bottom: 25px; 
                border: 2px solid #e4e6fc !important; 
                border-radius: 12px; 
                padding: 15px; 
                background: #fff; 
                box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            }
            #product_table td { 
                border: none !important; 
                padding: 10px 0 !important; 
                display: flex; 
                align-items: center; 
                justify-content: space-between;
                text-align: right !important;
                min-height: 45px;
            }
            #product_table td:before { 
                content: attr(data-label); 
                font-weight: 800; 
                flex-basis: 45%; 
                text-align: left; 
                font-size: 11px; 
                color: #888;
                text-transform: uppercase;
                padding-right: 10px;
            }
            #product_table td:first-child { 
                justify-content: center; 
                border-bottom: 2px solid #f0f0f0 !important; 
                padding-bottom: 15px !important;
                margin-bottom: 15px;
            }
            #product_table td:first-child:before { display: none; }
            #product_table td.product_select_col { 
                display: block; 
                text-align: left !important;
                padding: 0 !important;
                border-bottom: 1px dashed #eee !important;
                padding-bottom: 15px !important;
                margin-bottom: 10px;
            }
            #product_table td.product_select_col:before { display: none; }
            #product_table td .form-control { width: 150px !important; max-width: 50%; height: 32px !important; font-size: 13px !important; }
            #product_table td .form-control-plaintext { font-size: 14px; font-weight: 700; }
        } */
    </style>
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

            // Auto-load products from low stock alert
            if (selectedIds && selectedIds.length > 0) {
                $('#product_table tbody').empty();
                rowCount = 0;
                
                selectedIds.forEach((productId, index) => {
                    let product = products.find(p => p.id == productId);
                    if (product) {
                        addRow();
                        // Set product in the row after a short delay to ensure DOM is ready
                        setTimeout(() => {
                            let rows = $('#product_table tbody tr');
                            let targetRow = rows.eq(index);
                            if (targetRow.length) {
                                targetRow.find('.product_select').val(product.id).trigger('change');
                            }
                        }, 200 * (index + 1)); // Stagger the delays
                    }
                });
                
                setTimeout(() => {
                    toastr.success(selectedIds.length + ' product(s) loaded from Low Stock Alert.');
                }, 500);
            }

            // Pricing rule events
            $('#pricing_rule_select').on('change', function() {
                const val = $(this).val();
                selectedPricingRuleId = val ? parseInt(val) : null;
                applyPricingToAllRows();
            });
            
            // Initialize Select2 for booking select in modal
            $('#importModal').on('shown.bs.modal', function () {
                $('#booking_select').select2({
                    dropdownParent: $('#importModal'),
                    width: '100%'
                });
            });
            
            // 1. Booking Selection Handler
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
                            
                            // Auto-fill shipping method if available
                            if(bookings.length > 0 && bookings[0].shipping_method) {
                                $('#shipping_method_select').val(bookings[0].shipping_method);
                            }
                            
                            // bookings is an array
                            bookings.forEach(booking => {
                                addBookingRow(booking);
                            });

                            toastr.success(bookings.length + ' item(s) imported from Order Place.', 'Loaded');
                            
                            // Close modal after successful import
                            $('#importModal').modal('hide');
                        }
                    });
                } else {
                     $('#booking_id_hidden').val('');
                     addRow();
                }
            });

            // 2. Vendor Change Handler
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

            // 3. Init (only add empty row if no products were pre-loaded)
            if (!selectedIds || selectedIds.length === 0) {
                addRow();
            }
            $('#add_row_btn').on('click', function() { addRow(); });
            
            // 4. Calculations & Events
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
                    
                    // Update Image
                    let imgContainer = row.find('td:first-child');
                    if(product.thumb_image) {
                        imgContainer.html(`<img src="{{ asset('storage') }}/${product.thumb_image}" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">`);
                    } else {
                        imgContainer.html(`<div class="bg-light rounded d-flex align-items-center justify-content-center text-muted small" style="width: 40px; height: 40px;">N/A</div>`);
                    }

                    // Display Product Info (name, number, category, sale price)
                    let productInfo = `<strong>${product.name}</strong>`;
                    if(product.product_number) productInfo += `<br><small class="text-muted">Item #: ${product.product_number}</small>`;
                    if(product.category && product.category.name) productInfo += `<br><small class="text-muted">Category: ${product.category.name}</small>`;
                    row.find('.product-info').html(productInfo);
                    
                  
                    
                    // Set cost fields from product
                    row.find('.raw_material_cost').val(product.raw_material_cost || 0);
                    row.find('.tax_cost').val(product.tax || 0);
                    row.find('.transport_cost').val(product.transport_cost || 0);
                      // Set sale price (editable)
                    row.find('.sale_price').val(product.price || 0);
                    row.find('.outlet_price').val(product.outlet_price || 0);
                    // Handle Variants
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
                    row.find('.variant_info_hidden').val(''); // Clear old info
                    
                    calculateLocalUnitCost(row);
                    calculateRowTotal(row);
                    applyPricingToRow(row);
                }
            });

            $(document).on('input', '.qty, .unit_cost', function() {
                let row = $(this).closest('tr');
                if ($(this).hasClass('unit_cost')) {
                    let vendorCost = parseFloat($(this).val()) || 0;
                    // Update only base raw material (converted vendor cost)
                    row.find('.raw_material_cost').val((vendorCost * currentVendorRate).toFixed(2));
                    
                    // If cost is set to exactly 0, and user hasn't manually added fees, clear them to avoid 0.01 remainder
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

            $(document).on('input', '.extra_cost_input', function() {
                distributeExtraCosts();
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

        // --- Helpers ---

        function updateCurrencyMetadata() {
            $('#vendor_unit_cost_header').text('Unit Cost (' + currentVendorName + ')');
            $('#vendor_subtotal_header').text('Total purchase price  (' + currentVendorName + ')');
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
                     let cleanKey = key.replace(/Color:\s*/gi, '')
                                       .replace(/Size:\s*/gi, '')
                                       .replace(/\s*-\s*/g, ' ')
                                       .trim();
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
                : `<div class="bg-light rounded d-flex align-items-center justify-content-center text-muted small" style="width: 40px; height: 40px;">N/A</div>`;

             // Build product info display
             let productInfo = `<strong>${product.name}</strong>`;
             if(product.product_number) productInfo += `<br><small class="text-muted">Item #: ${product.product_number}</small>`;
             // if(product.category && product.category.name) productInfo += `<br><small class="text-muted">Category: ${product.category.name}</small>`; // Simplified to reduce height

             if(booking.vendor && booking.vendor.currency_rate) {
                 currentVendorRate = booking.vendor.currency_rate;
                 currentVendorIcon = booking.vendor.currency_icon;
                 currentVendorName = booking.vendor.currency_name;
                 updateCurrencyMetadata();
             }
             
             let rowId = rowCount;
             let html = `
                <tr data-row-id="${rowId}" class="main-row">
                    <td class="align-middle text-center" data-label="Image">${imageHtml}</td>
                    <td class="align-middle product_select_col" data-label="Product">
                        <select class="form-control form-control-sm product_select select2" name="items[${rowCount}][product_id]" required>
                            <option value="${product.id}" selected>${product.name}</option>
                            ${products.map(p => p.id != product.id ? `<option value="${p.id}">${p.name}</option>` : '').join('')}
                        </select>
                        <div class="product-info mt-1" style="font-size: 12px; line-height: 1.3; color: #666;">${productInfo}</div>
                        <div class="variant-container">${variantHtml}</div>
                        <input type="hidden" class="variant_info_hidden" name="items[${rowCount}][variant_info]" value='${variantInput}'>
                    </td>
                    <td class="align-middle text-center" data-label="Cost (Vendor)">
                        <input type="number" class="form-control form-control-sm unit_cost text-center" name="items[${rowCount}][unit_cost]" step="any" value="${booking.unit_price}" required>
                    </td>
                    <td class="align-middle text-center" data-label="Qty">
                        <input type="number" class="form-control form-control-sm qty text-center" name="items[${rowCount}][qty]" value="${booking.qty}" min="1" required style="font-weight: bold;">
                    </td>
                    <td class="align-middle text-right" data-label="Total (Vendor)">
                        <input type="text" class="form-control-plaintext form-control-sm subtotal mb-0 text-dark text-right font-weight-bold pr-2" readonly value="0.00">
                    </td>
                    <td class="align-middle text-center" data-label="Raw Cost">
                        <input type="number" class="form-control form-control-sm raw_material_cost text-center" name="items[${rowCount}][raw_material_cost]" value="${product.raw_material_cost || 0}" step="any">
                    </td>
                    <td class="align-middle text-center" data-label="Tax">
                        <input type="number" class="form-control form-control-sm tax_cost text-center" name="items[${rowCount}][tax_cost]" value="${product.tax || 0}" step="any">
                    </td>
                    <td class="align-middle text-center" data-label="Transport">
                        <input type="number" class="form-control form-control-sm transport_cost text-center" name="items[${rowCount}][transport_cost]" value="${product.transport_cost || 0}" step="any">
                    </td>
                    <td class="align-middle text-center" data-label="Local Cost">
                        <div class="form-control-plaintext form-control-sm local_unit_cost mb-0 text-primary text-center font-weight-bold pr-2">0.00</div>
                    </td>
                    <td class="align-middle text-center" data-label="Sale Price">
                        <input type="number" class="form-control form-control-sm sale_price text-center" name="items[${rowCount}][sale_price]" value="${product.price || 0}" step="any">
                    </td>
                    <td class="align-middle text-center" data-label="Outlet Price">
                        <input type="number" class="form-control form-control-sm outlet_price text-center" name="items[${rowCount}][outlet_price]" value="${product.outlet_price || 0}" step="any">
                    </td>
                    <td class="align-middle text-center" data-label="Delete">
                        <button type="button" class="btn btn-outline-danger btn-sm remove_row" style="padding: 2px 7px;"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `;
            $('#product_table tbody').append(html);
            $('.select2').select2({
                width: '100%'
            });
            let newRow = $('#product_table tbody tr').last();
            calculateLocalUnitCost(newRow);
            calculateRowTotal(newRow);
            applyPricingToRow(newRow);
            rowCount++;
        }

        function addRow() {
             let html = `
                <tr class="main-row">
                    <td class="align-middle text-center" data-label="Image">
                        <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted small" style="width: 40px; height: 40px;"><i class="fas fa-box"></i></div>
                    </td>
                    <td class="align-middle product_select_col" data-label="Product">
                        <select class="form-control form-control-sm product_select select2" name="items[${rowCount}][product_id]" required>
                            <option value="">Select Product...</option>
                            ${products.map(p => `<option value="${p.id}">${p.name}</option>`).join('')}
                        </select>
                        <div class="product-info mt-2" style="font-size: 12px; line-height: 1.4; color: #666;"></div>
                        <div class="variant-container"></div>
                        <input type="hidden" class="variant_info_hidden" name="items[${rowCount}][variant_info]">
                    </td>
                    <td class="align-middle text-center" data-label="Cost (Vendor)">
                        <input type="number" class="form-control form-control-sm unit_cost text-center" name="items[${rowCount}][unit_cost]" step="any" required placeholder="0.00">
                    </td>
                    <td class="align-middle text-center" data-label="Qty">
                       <input type="number" class="form-control form-control-sm qty text-center" name="items[${rowCount}][qty]" value="1" min="1" required style="font-weight: bold;">
                    </td>
                    <td class="align-middle text-right" data-label="Total (Vendor)">
                        <input type="text" class="form-control-plaintext form-control-sm subtotal mb-0 text-dark text-right font-weight-bold pr-2" readonly value="0.00">
                    </td>
                    <td class="align-middle text-center" data-label="Raw Cost">
                        <input type="number" class="form-control form-control-sm raw_material_cost text-center" name="items[${rowCount}][raw_material_cost]" placeholder="0.00" step="any" value="0">
                    </td>
                    <td class="align-middle text-center" data-label="Tax">
                        <input type="number" class="form-control form-control-sm tax_cost text-center" name="items[${rowCount}][tax_cost]" placeholder="0.00" step="any" value="0">
                    </td>
                    <td class="align-middle text-center" data-label="Transport">
                        <input type="number" class="form-control form-control-sm transport_cost text-center" name="items[${rowCount}][transport_cost]" placeholder="0.00" step="any" value="0">
                    </td>
                    <td class="align-middle text-center" data-label="Local Cost">
                        <div class="form-control-plaintext form-control-sm local_unit_cost mb-0 text-primary text-center font-weight-bold pr-2">0.00</div>
                    </td>
                    <td class="align-middle text-center" data-label="Sale Price">
                        <input type="number" class="form-control form-control-sm sale_price text-center" name="items[${rowCount}][sale_price]" placeholder="0.00" step="any">
                    </td>
                    <td class="align-middle text-center" data-label="Outlet Price">
                        <input type="number" class="form-control form-control-sm outlet_price text-center" name="items[${rowCount}][outlet_price]" placeholder="0.00" step="any">
                    </td>
                    <td class="align-middle text-center" data-label="Delete">
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
            let qty = parseFloat(row.find('.qty').val()) || 1;
            let rawMaterial = parseFloat(row.find('.raw_material_cost').val()) || 0;
            let tax = parseFloat(row.find('.tax_cost').val()) || 0;
            let transport = parseFloat(row.find('.transport_cost').val()) || 0;
            
            // Local unit cost = raw material + tax + transport (all predefined amounts)
            let totalLocalCost = rawMaterial + tax + transport;
            
            row.find('.local_unit_cost').text(systemIcon + totalLocalCost.toFixed(2));
        }

        function calculateRowTotal(row) {
            let qty = parseFloat(row.find('.qty').val()) || 0;
            let vendorCost = parseFloat(row.find('.unit_cost').val()) || 0;
            
            // Get landed cost components
            let rawMaterial = parseFloat(row.find('.raw_material_cost').val()) || 0;
            let tax = parseFloat(row.find('.tax_cost').val()) || 0;
            let transport = parseFloat(row.find('.transport_cost').val()) || 0;
            
            // System cost = raw material + tax + transport (all predefined amounts)
            let systemCost = rawMaterial + tax + transport;

            // Precision fix: If after summing everything we have a tiny fraction but inputs were zero, clamp to 0.00
            if (systemCost < 0.009 && vendorCost === 0 && rawMaterial < 0.009) systemCost = 0;
            
            let vendorTotal = qty * vendorCost;
            row.find('.subtotal').val(vendorTotal.toFixed(2));
            
            row.find('.unit_cost_system').text(systemIcon + systemCost.toFixed(2));
            
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
                 
                 // Get per-unit costs
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

        function distributeExtraCosts() {
            let vendorTotal = 0;
            let rows = $('#product_table tbody tr');
            
            rows.each(function() {
                let qty = parseFloat($(this).find('.qty').val()) || 0;
                let cost = parseFloat($(this).find('.unit_cost').val()) || 0;
                vendorTotal += qty * cost;
            });

            let globalMaterial = parseFloat($('input[name="material_cost"]').val()) || 0;
            let globalTransport = parseFloat($('input[name="transport_cost"]').val()) || 0;
            let globalTax = parseFloat($('input[name="tax"]').val()) || 0;

            if (vendorTotal > 0) {
                rows.each(function() {
                    let row = $(this);
                    let qty = parseFloat(row.find('.qty').val()) || 1;
                    let cost = parseFloat(row.find('.unit_cost').val()) || 0;
                    let rowVendorSubtotal = qty * cost;
                    let ratio = rowVendorSubtotal / vendorTotal;

                    // Distribute proportionately
                    let rowMaterial = (globalMaterial * ratio);
                    let rowTransport = (globalTransport * ratio);
                    let rowTax = (globalTax * ratio);

                    // Base raw material cost = (vendor cost * rate) + (distributed material / qty)
                    let baseRaw = (cost * currentVendorRate) + (rowMaterial / qty);
                    let unitTransport = (rowTransport / qty);
                    let unitTax = (rowTax / qty);
                    
                    row.find('.raw_material_cost').val(baseRaw.toFixed(2));
                    row.find('.transport_cost').val(unitTransport.toFixed(2));
                    row.find('.tax_cost').val(unitTax.toFixed(2));
                    
                    calculateLocalUnitCost(row);
                    calculateRowTotal(row);
                });
            } else {
                // If no subtotal yet, just clear or set defaults
                rows.each(function() {
                    let row = $(this);
                    let cost = parseFloat(row.find('.unit_cost').val()) || 0;
                    row.find('.raw_material_cost').val((cost * currentVendorRate).toFixed(2));
                    calculateLocalUnitCost(row);
                });
            }
            calculateGrandTotal();
        }
    </script>
@endpush
