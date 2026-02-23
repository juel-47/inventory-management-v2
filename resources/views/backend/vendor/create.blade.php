@extends('backend.layouts.master')

@section('title')
Vendor
@endsection

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Vendor</h1>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Create Vendor</h4>
                        <div class="card-header-action">
                            <a href="{{ route('admin.vendor.index') }}" class="btn btn-primary">Back</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.vendor.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="form-group col-md-6">
                                <label>Company Name</label>
                                <input type="text" class="form-control" name="shop_name" value="">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Phone</label>
                                <input type="text" class="form-control" name="phone" value="">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Email</label>
                                <input type="email" class="form-control" name="email" value="">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Address</label>
                                <input type="text" class="form-control" name="address" value="">
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label>Country</label>
                                <select name="country" class="form-control select2">
                                <option value="">Select</option>
                                @foreach (config('settings.country_list') as $country)
                                <option value="{{ $country }}">{{ $country }}</option>
                                @endforeach
                            </select>
                            </div>
                             <div class="form-group col-md-6">
                                 <label>Currency</label>
                                 <select name="currency_select" id="currency_select" class="form-control select2">
                                     <option value="">Select Currency</option>
                                     <option value="">Select Currency</option>
                                     @foreach (config('settings.currency_list') as $currency)
                                         <option value="{{ $currency['code'] }}" data-icon="{{ $currency['symbol'] }}" 
                                             {{ old('currency_name') == $currency['code'] ? 'selected' : '' }}>
                                             {{ $currency['name'] }} ({{ $currency['code'] }})
                                         </option>
                                     @endforeach
                                 </select>
                                 <input type="hidden" name="currency_name" id="currency_name" value="{{ old('currency_name') }}">
                                 <input type="hidden" name="currency_icon" id="currency_icon" value="{{ old('currency_icon') }}">
                             </div>
 
                             <div class="form-group col-md-3">
                                 <label>Currency Icon</label>
                                 <div class="h4" id="currency_icon_display">{{ old('currency_icon', '-') }}</div>
                             </div>
 
                             <div class="form-group col-md-3">
                                 <label>Local Currency Rate</label>
                                 <input type="number" step="0.0001" class="form-control" name="currency_rate" value="{{ old('currency_rate', 1.0000) }}">
                             </div>

                            <div class="form-group col-md-6">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>

                            <div class="form-group col-md-12">
                                <label>Description</label>
                                <textarea class="form-control" name="description"></textarea>
                            </div>

                            
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn-primary px-4">Create</button>
                            </div>
                        </form>
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
            $('#currency_select').on('change', function() {
                let code = $(this).val();
                let icon = $(this).find(':selected').data('icon');
                
                $('#currency_name').val(code);
                $('#currency_icon').val(icon);
                $('#currency_icon_display').text(icon || '-');
            });
        });
    </script>
@endpush
