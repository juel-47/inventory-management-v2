@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>General Settings</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12 col-md-8 col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h4><i class="fas fa-cog mr-2"></i> Configure Site & Currency</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.settings.update') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Site Name</label>
                                            <input type="text" name="site_name" class="form-control" value="{{ $setting->site_name ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Contact Email</label>
                                            <input type="email" name="contact_email" class="form-control" value="{{ $setting->contact_email ?? '' }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Address</label>
                                    <textarea name="address" class="form-control" rows="3">{{ $setting->address ?? '' }}</textarea>
                                </div>

                                <hr>
                                <h5 class="mb-3 text-primary">System Default Currency</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Select Your System Currency</label>
                                            <select name="system_currency_select" id="system_currency_select" class="form-control select2">
                                                @foreach(config('settings.currency_list') as $currency)
                                                     <option value="{{ $currency['code'] }}" data-icon="{{ $currency['symbol'] }}" 
                                                         {{ ($setting->currency_name ?? 'USD') == $currency['code'] ? 'selected' : '' }}>
                                                         {{ $currency['name'] }} ({{ $currency['code'] }})
                                                     </option>
                                                @endforeach
                                            </select>
                                             <input type="hidden" name="currency_name" id="currency_name" value="{{ $setting->currency_name ?? 'USD' }}">
                                             <input type="hidden" name="currency_icon" id="currency_icon" value="{{ $setting->currency_icon ?? '$' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-center">
                                        <div class="form-group">
                                            <label>Currency Icon</label>
                                             <div class="h3" id="system_icon_display">{{ $setting->currency_icon ?? '$' }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer text-right">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">Save Settings</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4 col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-info text-white">
                            <h4><i class="fas fa-info-circle mr-2"></i> Help Info</h4>
                        </div>
                        <div class="card-body">
                            <h6>How it works:</h6>
                            <p class="text-muted">The system uses the **System Default Currency** for all internal entries and default displays. When dealing with **Vendors**, you can define their specific currency rate relative to this System Currency.</p>
                            <div class="alert alert-light border text-center">
                                <strong>System Currency:</strong><br>
                                 <span class="h4 font-weight-bold system_code_label">{{ $setting->currency_name ?? 'USD' }}</span> (<span class="system_icon_label">{{ $setting->currency_icon ?? '$' }}</span>)
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
            // System Currency Selection
            $('#system_currency_select').on('change', function() {
                let code = $(this).val();
                let icon = $(this).find(':selected').data('icon');
                
                $('#currency_name').val(code);
                $('#currency_icon').val(icon);
                $('#system_icon_display').text(icon);
                $('.system_code_label').text(code);
                $('.system_icon_label').text(icon);
            });
        });
    </script>
@endpush
