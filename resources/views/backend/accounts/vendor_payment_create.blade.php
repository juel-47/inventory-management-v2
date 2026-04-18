@extends('backend.layouts.master')

@section('title', 'Pay Vendor Invoice')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Accounts - Pay Vendor Invoice</h1>
    </div>

    <div class="section-body">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Search Purchase Invoice & Pay Vendor</h4>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Purchase Invoice No</label>
                            <div class="input-group">
                                <input type="text" id="invoice_no_input" class="form-control" placeholder="Enter Purchase Invoice No (e.g. INV-123456)">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" id="search_btn" type="button">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div id="purchase_details_section" style="display: none;">
                            <hr>
                            <div class="row mb-4">
                                <div class="col-sm-6">
                                    <h6 class="text-muted">Vendor Name:</h6>
                                    <p id="vendor_name" class="font-weight-bold h6 mb-2"></p>
                                    <h6 class="text-muted">Purchase Date:</h6>
                                    <p id="purchase_date" class="font-weight-bold h6 mb-0"></p>
                                </div>
                                <div class="col-sm-6 text-sm-right">
                                    <h6 class="text-muted">Payment Status:</h6>
                                    <span id="payment_status" class="badge badge-secondary"></span>
                                </div>
                            </div>

                            <div class="row text-center mb-4">
                                <div class="col-4 border-right">
                                    <div class="text-muted small text-uppercase">Total</div>
                                    <div class="h5 font-weight-bold text-primary" id="total_amount"></div>
                                </div>
                                <div class="col-4 border-right">
                                    <div class="text-muted small text-uppercase">Paid</div>
                                    <div class="h5 font-weight-bold text-success" id="paid_amount"></div>
                                </div>
                                <div class="col-4">
                                    <div class="text-muted small text-uppercase">Due</div>
                                    <div class="h5 font-weight-bold text-danger" id="due_amount"></div>
                                </div>
                            </div>

                            <div id="payment_form_wrapper">
                                <form id="payment_form" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="source" value="central_entry">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Amount to Pay</label>
                                                <input type="number" name="amount" id="pay_amount_input" class="form-control" step="0.01" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Payment Method</label>
                                                <select name="payment_method" class="form-control selectric" required>
                                                    <option value="cash">Cash</option>
                                                    <option value="bank">Bank Transfer</option>
                                                    <option value="mobile_banking">Mobile Pay</option>
                                                    <option value="cheque">Cheque</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="font-weight-bold">Transaction ID / Reference</label>
                                        <input type="text" name="transaction_id" class="form-control" placeholder="Optional">
                                    </div>
                                    <div class="form-group">
                                        <label class="font-weight-bold">Note</label>
                                        <textarea name="note" class="form-control" rows="3"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label class="font-weight-bold">Receipt(s)</label>
                                        <input type="file" name="receipts[]" class="form-control" multiple accept=".jpg,.jpeg,.png,.webp,.pdf">
                                        <small class="text-muted">You can upload multiple files (JPG/PNG/WEBP/PDF).</small>
                                    </div>
                                    <button type="submit" class="btn btn-success btn-lg btn-block shadow-sm">
                                        <i class="fas fa-check-circle mr-1"></i> Confirm Vendor Payment
                                    </button>
                                </form>
                            </div>

                            <div id="full_paid_msg" style="display: none;" class="alert alert-success text-center">
                                <i class="fas fa-check-circle mr-1"></i> This purchase invoice is already fully paid.
                            </div>
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
        const statusClasses = {
            pending: 'badge-secondary',
            partial: 'badge-warning',
            paid: 'badge-success'
        };

        const urlParams = new URLSearchParams(window.location.search);
        const invoiceNoParam = urlParams.get('invoice_no');
        if (invoiceNoParam) {
            $('#invoice_no_input').val(invoiceNoParam);
            setTimeout(() => {
                $('#search_btn').click();
            }, 500);
        }

        $('#search_btn').on('click', function() {
            const invoiceNo = $('#invoice_no_input').val();
            if (!invoiceNo) {
                toastr.warning('Please enter a purchase invoice number');
                return;
            }

            $.ajax({
                url: "{{ route('admin.accounts.vendor-payments.search-purchase') }}",
                method: "GET",
                data: { invoice_no: invoiceNo },
                beforeSend: function() {
                    $('#search_btn').addClass('btn-progress disabled');
                },
                success: function(response) {
                    $('#search_btn').removeClass('btn-progress disabled');

                    if (response.success) {
                        const purchase = response.purchase;
                        const normalizedStatus = (purchase.payment_status || 'Pending').toLowerCase();

                        $('#purchase_details_section').show();
                        $('#vendor_name').text(purchase.vendor_name);
                        $('#purchase_date').text(purchase.purchase_date);
                        $('#total_amount').text(purchase.total_amount);
                        $('#paid_amount').text(purchase.paid_amount);
                        $('#due_amount').text(purchase.due_amount);
                        $('#payment_status')
                            .text(purchase.payment_status)
                            .removeClass('badge-secondary badge-warning badge-success')
                            .addClass(statusClasses[normalizedStatus] || 'badge-secondary');

                        $('#payment_form').attr('action', `/admin/accounts/purchases/${purchase.id}/payment`);
                        $('#pay_amount_input').val(purchase.due_raw).attr('max', purchase.due_raw);

                        if (parseFloat(purchase.due_raw) <= 0) {
                            $('#payment_form_wrapper').hide();
                            $('#full_paid_msg').show();
                        } else {
                            $('#payment_form_wrapper').show();
                            $('#full_paid_msg').hide();
                        }
                    }
                },
                error: function(xhr) {
                    $('#search_btn').removeClass('btn-progress disabled');
                    $('#purchase_details_section').hide();
                    toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Error occurred');
                }
            });
        });

        $('#invoice_no_input').on('keypress', function(e) {
            if (e.which === 13) {
                $('#search_btn').click();
            }
        });
    });
</script>
@endpush
