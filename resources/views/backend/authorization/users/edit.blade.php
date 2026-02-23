@extends('backend.layouts.master')
@section('title', $settings->site_name . ' | Update User')
@section('content')

    <section class="section">
        <div class="section-header">
            <h1>User</h1>
        </div>
        <div class="section-body">

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Update User</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.users.index') }}" class="btn btn-primary">Back</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.users.update', $user->id) }}" method="post"
                                enctype="multipart/form-data">
                                @csrf
                                @method('put')
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Name</label>
                                        <input type="text" class="form-control" name="name"
                                            value="{{ old('name') ?? $user->name }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Email</label>
                                        <input type="text" class="form-control" name="email"
                                            value="{{ old('email') ?? $user->email }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Phone</label>
                                        <input type="text" class="form-control" name="phone"
                                            value="{{ old('phone') ?? $user->phone }}">
                                    </div>
                                    {{-- <div class="form-group col-md-6">
                                        <label>Password</label>
                                        <input type="password" class="form-control" name="password">
                                    </div> --}}
                                    <div class="form-group col-md-6">
                                        <label for="inputState">Role</label>
                                        <select id="inputState" class="form-control" name="user_role">
                                            <option value="">--select--</option>
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->id }}"
                                                    {{ $user->role_id == $role->id ? 'selected' : '' }}>{{ $role->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="inputState">Status</label>
                                        <select id="inputState" class="form-control" name="status">
                                            <option {{ $user->status == 1 ? 'selected' : '' }} value="1">Active
                                            </option>
                                            <option {{ $user->status == 0 ? 'selected' : '' }} value="0">Inactive
                                            </option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Image <code>(optional)</code> </label>
                                        <input type="file" class="form-control" name="image">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Image preview </label>
                                        <img src="{{ asset($user->image) }}" alt="" width="150px">
                                    </div>

                                </div>
                                <div class="text-right mt-3">
                                    <button type="submit" class="btn btn-primary px-4">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
    </section>
@endsection
