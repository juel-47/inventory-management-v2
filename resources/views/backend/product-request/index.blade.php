@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Product Requests</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>All Product Requests</h4>
                            <div class="card-header-action">
                                @can('Create Product Requests')
                                    <a href="{{ route('admin.product-requests.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Create New</a>
                                @endcan
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-1">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Request No</th>
                                            <th>Requester</th>
                                            <th>Outlet/Shop Name</th>
                                            <th>Total Qty</th>
                                            <th>Items</th>
                                            <th>Required Days</th>
                                             <th>Local Total Price</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($productRequests as $request)
                                            <tr>
                                                <td>{{ $request->id }}</td>
                                                <td>{{ $request->request_no }}</td>
                                                <td>{{ $request->user->name }}</td>
                                                <td>{{ $request->user->outlet_name ?? 'N/A' }}</td>
                                                <td>{{ $request->total_qty }}</td>
                                                <td><span class="badge badge-info">{{ $request->items->count() }} Items</span></td>
                                                <td>
                                                    @if($request->required_days)
                                                        @if($request->required_days <= 3)
                                                            <span class="badge badge-danger">{{ $request->required_days }} days</span>
                                                        @elseif($request->required_days <= 7)
                                                            <span class="badge badge-warning">{{ $request->required_days }} days</span>
                                                        @else
                                                            <span class="badge badge-success">{{ $request->required_days }} days</span>
                                                        @endif
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                                <td>{!! formatConverted($request->total_amount) !!}</td>
                                                <td>
                                                    @if($request->status == 'pending')
                                                        <span class="badge badge-warning">Pending</span>
                                                    @elseif($request->status == 'approved')
                                                        <span class="badge badge-info">Approved</span>
                                                    @elseif($request->status == 'rejected')
                                                        <span class="badge badge-danger">Rejected</span>
                                                    @elseif($request->status == 'shipped')
                                                        <span class="badge badge-primary">Shipped</span>
                                                    @elseif($request->status == 'completed')
                                                        <span class="badge badge-success">Completed</span>
                                                    @endif
                                                </td>
                                                <td>{{ $request->created_at->format('d M, Y') }}</td>
                                                <td>
                                                    <a href="{{ route('admin.product-requests.view-invoice', $request->id) }}" class="btn btn-warning btn-sm" title="View Invoice" target="_blank"><i class="fas fa-file-invoice"></i></a>
                                                    <a href="{{ route('admin.product-requests.download-invoice', $request->id) }}" class="btn btn-info btn-sm" title="Download PDF"><i class="fas fa-download"></i></a>
                                                    <a href="{{ route('admin.product-requests.show', $request->id) }}" class="btn btn-primary btn-sm" title="Control Panel"><i class="fas fa-eye"></i></a>
                                                    @if($request->status == 'pending' || Auth::user()->can('Manage Product Requests'))
                                                        <a href="{{ route('admin.product-requests.destroy', $request->id) }}" class="btn btn-danger btn-sm delete-item" title="Delete"><i class="fas fa-trash"></i></a>
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
    @if(session()->has('clear_request_basket'))
    <script>
        localStorage.removeItem('request_basket');
    </script>
    @endif

    <script>
        $("#table-1").dataTable({
            "order": [[0, "desc"]],
            "columnDefs": [
                { "sortable": false, "targets": [8, 9] }
            ]
        });
    </script>
@endpush
