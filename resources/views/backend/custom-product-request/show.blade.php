@extends('backend.layouts.master')

@section('title') Custom Product Request Details @endsection

@section('css')
<style>
    .status-badge {
        padding: 8px 16px;
        border-radius: 25px;
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-approved { background: #d4edda; color: #155724; }
    .status-rejected { background: #f8d7da; color: #721c24; }
    .detail-label {
        font-weight: 600;
        color: #6c757d;
        font-size: 13px;
        text-transform: uppercase;
        margin-bottom: 5px;
    }
    .detail-value {
        font-size: 16px;
        color: #212529;
    }
</style>
@endsection

@section('content')

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Request Details</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.custom-product-requests.index') }}">Custom Requests</a></li>
                            <li class="breadcrumb-item active">Details</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center items-end">
                            <h5 class="mb-0 mr-5">Request No: {{ $customProductRequest->request_no }}</h5>
                            @if($customProductRequest->status == 'pending')
                                <span class="status-badge status-pending"><i class="fas fa-clock"></i> Pending</span>
                            @elseif($customProductRequest->status == 'approved')
                                <span class="status-badge status-approved"><i class="fas fa-check"></i> Approved</span>
                            @else
                                <span class="status-badge status-rejected"><i class="fas fa-times"></i> Rejected</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="detail-label">Product Name</div>
                                    <div class="detail-value">{{ $customProductRequest->product_name ?? 'Not specified' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="detail-label">Quantity Needed</div>
                                    <div class="detail-value">{{ $customProductRequest->quantity_needed }} units</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="detail-label">Product Description</div>
                            <div class="detail-value">{{ $customProductRequest->product_description }}</div>
                        </div>

                        @if($customProductRequest->expected_price)
                        <div class="mb-3">
                            <div class="detail-label">Expected Price (per unit)</div>
                            <div class="detail-value">${{ number_format($customProductRequest->expected_price, 2) }}</div>
                        </div>
                        @endif

                        @if($customProductRequest->example_image)
                        <div class="mb-3">
                            <div class="detail-label">Example Photo</div>
                            <img src="{{ asset($customProductRequest->example_image) }}" alt="Product Image" style="max-width: 300px; border-radius: 8px; border: 2px solid #dee2e6;">
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Requester Info -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">Requested By</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="user-avatar" style="width: 50px; height: 50px; font-size: 20px;">
                                {{ substr($customProductRequest->user->name, 0, 1) }}
                            </div>
                            <div>
                                <div class="fw-bold">{{ $customProductRequest->user->name }}</div>
                                <small class="text-muted">{{ $customProductRequest->user->outlet_name ?? 'No Outlet' }}</small>
                            </div>
                        </div>
                        <hr>
                        <div class="detail-label">Request Date</div>
                        <div class="detail-value">{{ $customProductRequest->created_at->format('d M Y') }}</div>
                    </div>
                </div>

                <!-- Admin Status Update -->
                @if(Auth::user()->hasRole('Admin'))
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Update Status</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.custom-product-requests.update-status', $customProductRequest->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <select name="status" class="form-control" required>
                                    <option value="pending" {{ $customProductRequest->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ $customProductRequest->status == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ $customProductRequest->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <textarea name="admin_note" class="form-control" rows="2" placeholder="Add a note...">{{ $customProductRequest->admin_note }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-save"></i> Update Status
                            </button>
                        </form>
                    </div>
                </div>
                @endif

                <!-- Admin Note Display -->
                @if($customProductRequest->admin_note && !Auth::user()->can('Manage Custom Product Requests'))
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Admin Note</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $customProductRequest->admin_note }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
