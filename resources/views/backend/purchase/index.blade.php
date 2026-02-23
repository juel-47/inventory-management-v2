@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Order Receive</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Order Receive</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>All Order Receive</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.purchases.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> New Order Receive</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-1">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Invoice No</th>
                                            <th>Vendor</th>
                                            <th>Shipping</th>
                                            <th>Created By</th>
                                            <th>local currency Total</th>
                                            <th>Vendor Total price</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($purchases as $purchase)
                                            <tr>
                                                <td>{{ $purchase->date }}</td>
                                                <td>{{ $purchase->invoice_no }}</td>
                                                <td>{{ $purchase->vendor->shop_name ?? 'N/A' }}</td>
                                                <td>{{ $purchase->shipping_method ?? 'N/A' }}</td>
                                                <td>{{ $purchase->user->name ?? 'System' }}</td>
                                                <td>{{ formatConverted($purchase->total_amount) }}</td>
                                                <td>
                                                    @if($purchase->vendor)
                                                        @php
                                                            $vendorSubtotal = $purchase->details->sum(function($d) { return $d->unit_cost_vendor * $d->qty; });
                                                        @endphp
                                                        {{ $purchase->vendor->currency_icon }}{{ number_format($vendorSubtotal, 2) }}
                                                    @else
                                                        {{ formatConverted($purchase->total_amount) }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($purchase->status == 1)
                                                        <div class="badge badge-success">Completed</div>
                                                    @else
                                                        <div class="badge badge-warning">Draft</div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.purchases.view-invoice', $purchase->id) }}" target="_blank" class="btn btn-warning btn-sm" title="View Invoice"><i class="fas fa-file-invoice"></i></a>
                                                    <a href="{{ route('admin.purchases.download-pdf', $purchase->id) }}" class="btn btn-secondary btn-sm ml-1" title="Download PDF"><i class="fas fa-download"></i></a>
                                                    @if($purchase->invoice_attachment)
                                                        <a href="{{ asset('storage/' . $purchase->invoice_attachment) }}" class="btn btn-info btn-sm ml-1" title="Download Attachment"><i class="fas fa-paperclip"></i></a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
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
        $("#table-1").dataTable({
            "order": [[0, "desc"]],
            "columnDefs": [
                { "sortable": false, "targets": [8] }
            ],
            "order": [[0, "desc"]]
        });
    </script>
@endpush
