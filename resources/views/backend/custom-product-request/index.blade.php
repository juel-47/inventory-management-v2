@extends('backend.layouts.master')

@section('title') Custom Product Requests @endsection

@section('css')
<style>
    .status-badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .status-pending {
        background: #fff3cd;
        color: #856404;
    }
    .status-approved {
        background: #d4edda;
        color: #155724;
    }
    .status-rejected {
        background: #f8d7da;
        color: #721c24;
    }
    .request-image {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #e9ecef;
    }
    .user-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .user-avatar {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background: #6777ef;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
    }
</style>
@endsection

@section('content')

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Custom Product Requests</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Custom Product Requests</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            {{-- <div class="col-md-6">
                                <h5 class="card-title mb-0">All Custom Product Requests</h5>
                            </div> --}}
                            <div class="col-md-6 text-md items-end">
                                @can('Create Custom Product Requests')
                                    <a href="{{ route('admin.custom-product-requests.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Create New
                                    </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-nowrap" id="customProductRequestTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Request No.</th>
                                        <th>Outlet/User</th>
                                        <th>Product Info</th>
                                        <th>Quantity</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($customProductRequests as $request)
                                        <tr>
                                            <td>
                                                <span class="text-primary fw-bold">{{ $request->request_no }}</span>
                                            </td>
                                            <td>
                                                <div class="user-info">
                                                    <div class="user-avatar">
                                                        {{ substr($request->user->name, 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold">{{ $request->user->name }}</div>
                                                        <small class="text-muted">{{ $request->user->outlet_name ?? 'No Outlet' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    @php $exampleImages = $request->example_image ?? []; @endphp
                                                    @if(!empty($exampleImages))
                                                        <img src="{{ asset($exampleImages[0]) }}" width="80px" alt="Product" class="request-image">
                                                    @else
                                                        <div class="request-image d-flex align-items-center justify-content-center bg-light">
                                                            <i class="fas fa-image text-muted"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="fw-bold">{{ $request->product_name ?? 'N/A' }}</div>
                                                        <small class="text-muted text-truncate" style="max-width: 150px;">
                                                            {{ Str::limit($request->product_description, 50) }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $request->quantity_needed }}</td>
                                            <td>
                                                @if(Auth::user()->hasRole('Admin'))
                                                    <form action="{{ route('admin.custom-product-requests.update-status', $request->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PUT')
                                                        <select name="status" class="form-control form-control-sm" onchange="this.form.submit()" style="width: auto; display: inline-block;">
                                                            <option value="pending" {{ $request->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                            <option value="approved" {{ $request->status == 'approved' ? 'selected' : '' }}>Approved</option>
                                                            <option value="rejected" {{ $request->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                                        </select>
                                                    </form>
                                                @else
                                                    @if($request->status == 'pending')
                                                        <span class="status-badge status-pending"><i class="fas fa-clock"></i> Pending</span>
                                                    @elseif($request->status == 'approved')
                                                        <span class="status-badge status-approved"><i class="fas fa-check"></i> Approved</span>
                                                    @else
                                                        <span class="status-badge status-rejected"><i class="fas fa-times"></i> Rejected</span>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                <div>{{ $request->created_at->format('d M Y') }}</div>
                                                <small class="text-muted">{{ $request->created_at->format('h:i A') }}</small>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.custom-product-requests.show', $request->id) }}" class="btn btn-primary btn-sm" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($request->status == 'pending' || Auth::user()->can('Manage Custom Product Requests'))
                                                    <a href="{{ route('admin.custom-product-requests.destroy', $request->id) }}" class="btn btn-danger btn-sm delete-item" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                                    <p>No custom product requests found</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable if needed
        // $('#customProductRequestTable').DataTable();
    });
</script>
@endpush
