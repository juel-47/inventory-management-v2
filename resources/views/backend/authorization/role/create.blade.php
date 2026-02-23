@extends('backend.layouts.master')
@section('title', $settings->site_name . ' | Role Create')
@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Create Role</h1>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header">
                            <h4>Create Role</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.role.index') }}" class="btn btn-primary">Back</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.role.store') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <!-- Role Name -->
                                    <div class="col-md-4 mb-3">
                                        <label for="role_name" class="form-label">Role Name</label>
                                        <input type="text" class="form-control" id="role_name" name="name"
                                            value="{{ old('name') }}" placeholder="Enter role name">
                                    </div>

                                    <!-- Permissions -->
                                    <div class="col-md-8">
                                        <h5 class="mb-3">Permissions</h5>
                                        <div class="row g-3">
                                            @foreach ($permissions as $item)
                                                <div class="col-md-6 col-lg-4 space-y-1">
                                                    <div
                                                        class="card p-3 border shadow-sm h-100 d-flex flex-column justify-content-between">
                                                        <span class="fw-bold mb-2">{{ $item->name }}</span>
                                                        <label class="custom-switch mt-auto">
                                                            <input type="checkbox" name="permissions[]"
                                                                value="{{ $item->id }}" class="custom-switch-input">
                                                            <span class="custom-switch-indicator"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                {{-- <button type="submit" class="btn btn-primary mt-4 justify-content-end">Create Role</button> --}}
                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="btn btn-primary">Create Role</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
    </section>
@endsection
