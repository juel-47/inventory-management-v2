@extends('backend.layouts.master')
@section('title', 'Frontend Order Details')

@section('content')
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('admin.orders.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>Order #{{ $order->order_no }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Frontend Orders</a></div>
                <div class="breadcrumb-item">Details</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12 col-lg-8">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h4><i class="fas fa-list mr-2"></i>Order Items</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.orders.pi-invoice', $order->id) }}" class="btn btn-success" target="_blank"><i class="fas fa-file-signature mr-1"></i> PI Invoice</a>
                                <a href="{{ route('admin.orders.view-invoice', $order->id) }}" class="btn btn-warning" target="_blank"><i class="fas fa-file-invoice mr-1"></i> View Invoice</a>
                                <a href="{{ route('admin.orders.download-invoice', $order->id) }}" class="btn btn-info ml-2"><i class="fas fa-download mr-1"></i> Download PDF</a>
                                {{-- <a href="{{ route('admin.orders.destroy', $order->id) }}" class="btn btn-danger ml-2 delete-item"><i class="fas fa-trash mr-1"></i> Delete</a> --}}
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="text-center" width="5%">#</th>
                                            <th class="text-center" width="12%">Image</th>
                                            <th>Product</th>
                                            <th class="text-center">Variant</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-right">Unit Price</th>
                                            <th class="text-right">Line Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($order->items as $index => $item)
                                            @php
                                                $imagePath = (string) ($item->product_image ?? '');
                                                $imageUrl = null;
                                                if ($imagePath !== '') {
                                                    if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://')) {
                                                        $imageUrl = $imagePath;
                                                    } elseif (is_file(public_path(ltrim($imagePath, '/')))) {
                                                        $imageUrl = asset(ltrim($imagePath, '/'));
                                                    } elseif (str_starts_with($imagePath, 'storage/')) {
                                                        $imageUrl = asset($imagePath);
                                                    } else {
                                                        $imageUrl = asset('storage/' . ltrim($imagePath, '/'));
                                                    }
                                                }
                                            @endphp
                                            <tr>
                                                <td class="text-center font-weight-bold">{{ $index + 1 }}</td>
                                                <td class="text-center">
                                                    @if($imageUrl)
                                                        <img src="{{ $imageUrl }}" alt="{{ $item->product_name }}" style="width:44px;height:44px;object-fit:cover;border-radius:4px;border:1px solid #e5e7eb;">
                                                    @else
                                                        <span class="text-muted small">No Image</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="font-weight-600">{{ $item->product_name }}</div>
                                                    <small class="text-muted">{{ $item->category_name ?? 'General' }}</small>
                                                </td>
                                                <td class="text-center">
                                                    @if($item->variant_label)
                                                        <span class="badge badge-primary">{{ $item->variant_label }}</span>
                                                    @else
                                                        <span class="text-muted">Standard</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-info px-3">{{ $item->quantity }}</span>
                                                </td>
                                                <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                                                <td class="text-right font-weight-bold text-primary">{{ number_format($item->line_total, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4 text-muted">No items found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot class="bg-whitesmoke">
                                        <tr>
                                            <td colspan="6" class="text-right font-weight-bold text-muted text-uppercase small">Grand Total</td>
                                            <td class="text-right font-weight-bold h6 text-primary mb-0">{{ number_format($order->total_amount, 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    @include('backend.pi._editor', [
                        'title' => 'Manual PI / CTN Info',
                        'subtitle' => 'Save carton and packing details first, then open the PI invoice for review or sharing.',
                        'formAction' => route('admin.orders.pi-info.save', $order->id),
                        'piInvoiceUrl' => route('admin.orders.pi-invoice', $order->id),
                        'items' => $order->items,
                        'piInfo' => $piInfo,
                        'piTotals' => $piTotals,
                    ])

                    <div class="card">
                        <div class="card-header border-bottom">
                            <h4>Billing & Shipping</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-muted text-uppercase small mb-3">Billing</h6>
                                    <p class="mb-1"><strong>Name:</strong> {{ $order->billing_name }}</p>
                                    <p class="mb-1"><strong>Phone:</strong> {{ $order->billing_phone }}</p>
                                    <p class="mb-1"><strong>Email:</strong> {{ $order->billing_email }}</p>
                                    <p class="mb-1"><strong>Address:</strong> {{ $order->billing_address }}</p>
                                    <p class="mb-0"><strong>Outlet/Shop:</strong> {{ $order->billing_outlet_name ?: ($order->user->outlet_name ?? 'N/A') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted text-uppercase small mb-3">Shipping</h6>
                                    @if($order->ship_different)
                                        <p class="mb-1"><strong>Name:</strong> {{ $order->shipping_name ?: 'N/A' }}</p>
                                        <p class="mb-1"><strong>Phone:</strong> {{ $order->shipping_phone ?: 'N/A' }}</p>
                                        <p class="mb-1"><strong>Email:</strong> {{ $order->shipping_email ?: 'N/A' }}</p>
                                        <p class="mb-1"><strong>Address:</strong> {{ $order->shipping_address ?: 'N/A' }}</p>
                                        <p class="mb-1"><strong>City/State:</strong> {{ trim(($order->shipping_city ?: '') . ' ' . ($order->shipping_state ?: '')) ?: 'N/A' }}</p>
                                        <p class="mb-1"><strong>Zip/Country:</strong> {{ trim(($order->shipping_zip_code ?: '') . ' ' . ($order->shipping_country ?: '')) ?: 'N/A' }}</p>
                                        <p class="mb-0"><strong>Outlet/Shop:</strong> {{ $order->shipping_outlet_name ?: 'N/A' }}</p>
                                    @else
                                        <div class="alert alert-light border mb-0">Same as billing information.</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Payment History (Admin Only) --}}
                    @role('Admin')
                    <div class="card">
                        <div class="card-header border-bottom">
                            <h4><i class="fas fa-history mr-2"></i>Payment History</h4>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Method</th>
                                            <th>Transaction ID</th>
                                            <th class="text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($order->payments as $payment)
                                            <tr>
                                                <td>{{ $payment->created_at->format('d M, Y h:i A') }}</td>
                                                <td><span class="badge badge-info">{{ strtoupper($payment->payment_method) }}</span></td>
                                                <td>{{ $payment->transaction_id ?? 'N/A' }}</td>
                                                <td class="text-right font-weight-bold">{{ number_format($payment->amount, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-3 text-muted">No payments recorded yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    @if($order->payments->count() > 0)
                                        <tfoot class="bg-light">
                                            <tr>
                                                <td colspan="3" class="text-right font-weight-bold">Total Paid</td>
                                                <td class="text-right font-weight-bold text-success">{{ number_format($order->paid_amount, 2) }}</td>
                                            </tr>
                                        </tfoot>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                    @endrole
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card card-statistic-1 mb-3">
                        <div class="card-icon bg-primary">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Current Status</h4>
                            </div>
                            <div class="card-body">
                                @php
                                    $status = strtolower((string) $order->status);
                                    $statusColor = match($status) {
                                        'pending' => 'warning',
                                        'approved' => 'info',
                                        'processing' => 'primary',
                                        'shipped' => 'primary',
                                        'completed' => 'success',
                                        'rejected', 'cancelled' => 'danger',
                                        default => 'secondary',
                                    };
                                @endphp
                                <div class="text-{{ $statusColor }} text-uppercase">{{ ucfirst($order->status) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header border-bottom">
                            <h4>Order Summary</h4>
                        </div>
                        <div class="card-body">
                            <p class="mb-1"><strong>Order No:</strong> {{ $order->order_no }}</p>
                            <p class="mb-1"><strong>Date:</strong> {{ $order->created_at?->format('d M, Y h:i A') }}</p>
                            <p class="mb-1"><strong>Customer:</strong> {{ $order->user->name ?? $order->billing_name }}</p>
                            <p class="mb-1"><strong>Outlet/Shop:</strong> {{ $order->billing_outlet_name ?: ($order->user->outlet_name ?? 'N/A') }}</p>
                            <p class="mb-3"><strong>Source:</strong> {{ $order->shipping_method ?: 'frontend_checkout' }}</p>

                            <hr>
                            <p class="mb-1 d-flex justify-content-between"><span>Subtotal</span><strong>{{ number_format($order->subtotal_amount, 2) }}</strong></p>
                            <p class="mb-1 d-flex justify-content-between"><span>{{ $order->tax_label ?: 'VAT / Tax' }}</span><strong>{{ number_format($order->tax_amount, 2) }}</strong></p>
                            <p class="mb-1 d-flex justify-content-between"><span>Discount</span><strong>-{{ number_format($order->discount_amount, 2) }}</strong></p>
                            <p class="mb-0 d-flex justify-content-between"><span class="font-weight-bold">Total</span><strong class="text-primary">{{ number_format($order->total_amount, 2) }}</strong></p>
                        </div>
                    </div>

                    {{-- Payment Summary & History (Admin Only) --}}
                    @role('Admin')
                    <div class="card card-success mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4><i class="fas fa-money-bill-wave mr-2"></i>Payment Summary</h4>
                            @if($order->due_amount > 0)
                                <a href="{{ route('admin.accounts.record-payment', ['order_no' => $order->order_no]) }}" class="btn btn-sm btn-outline-white">
                                    <i class="fas fa-plus mr-1"></i> Record via Account Module
                                </a>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6 border-right">
                                    <div class="text-muted small text-uppercase font-weight-bold">Paid</div>
                                    <div class="h5 font-weight-bold text-success mb-0">{{ number_format($order->paid_amount, 2) }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small text-uppercase font-weight-bold">Due</div>
                                    <div class="h5 font-weight-bold text-danger mb-0">{{ number_format($order->due_amount, 2) }}</div>
                                </div>
                            </div>
                            
                            @if($order->due_amount <= 0 && $order->total_amount > 0)
                                <div class="alert alert-success text-center py-2 mb-0 mt-3">
                                    <i class="fas fa-check-circle mr-1"></i> Full Paid
                                </div>
                            @elseif($order->due_amount > 0)
                                <div class="text-center mt-3">
                                    <span class="badge badge-warning">Partial Payment Pending</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endrole

                    <div class="card card-warning">
                        <div class="card-header">
                            <h4><i class="fas fa-user-cog mr-2"></i>Actions</h4>
                        </div>
                        <div class="card-body">
                            @php
                                $currentStatus = strtolower((string) $order->status);
                                $isLockedStatus = in_array($currentStatus, ['completed', 'rejected'], true);
                            @endphp
                            <form method="POST" action="{{ route('admin.orders.update-status', $order->id) }}">
                                @csrf
                                @method('PUT')
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold small text-muted text-uppercase">Change Status</label>
                                    <select name="status" class="form-control" {{ $isLockedStatus ? 'disabled' : '' }}>
                                        <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="approved" {{ $order->status === 'approved' ? 'selected' : '' }}>Approve (Create Issue)</option>
                                        <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        @if(!in_array($order->status, ['pending', 'approved', 'cancelled'], true))
                                            <option value="{{ $order->status }}" selected>{{ ucfirst($order->status) }}</option>
                                        @endif
                                    </select>
                                </div>

                                <div class="text-right">
                                    <button type="submit" class="btn btn-primary shadow-sm px-4" {{ $isLockedStatus ? 'disabled' : '' }}>Update Order</button>
                                </div>
                                @if($isLockedStatus)
                                    <p class="text-muted small mt-2 mb-0">This order is already {{ ucfirst($order->status) }} and cannot be changed from here.</p>
                                @endif
                            </form>

                            @can('Manage Inventory')
                            @if(strtolower((string) $order->status) === 'approved')
                                <div class="border-top pt-4 mt-3">
                                    <a href="{{ route('admin.issues.create', ['order_id' => $order->id]) }}" class="btn btn-success btn-lg btn-block shadow-sm py-3 font-weight-bold">
                                        <i class="fas fa-box-open mr-2"></i> Create Stock Issue
                                    </a>
                                    <p class="text-center text-muted small mt-2 mb-0">Import order items to issue and confirm stock delivery.</p>
                                </div>
                            @endif
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
