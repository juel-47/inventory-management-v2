@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Currency Settings</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">Settings</a></div>
                <div class="breadcrumb-item active">Currency</div>
            </div>
        </div>

        <div class="section-body">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h4>System Default Currency</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.currency.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Select System Currency</label>
                                    <select name="system_currency_select" id="system_currency_select" class="form-control select2">
                                        @foreach (config('settings.currency_list') as $currency)
                                            <option value="{{ $currency['code'] }}" data-icon="{{ $currency['symbol'] }}" {{ old('currency_name', $setting->currency_name ?? 'USD') == $currency['code'] ? 'selected' : '' }}>
                                                {{ $currency['name'] }} ({{ $currency['code'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="currency_name" id="currency_name" value="{{ old('currency_name', $setting->currency_name ?? 'USD') }}">
                                    <input type="hidden" name="currency_icon" id="currency_icon" value="{{ old('currency_icon', $setting->currency_icon ?? '$') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group text-center">
                                    <label>Currency Icon</label>
                                    <div class="h3 mt-2" id="system_icon_display">{{ old('currency_icon', $setting->currency_icon ?? '$') }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-light border mb-0">
                            This currency will be used as the default currency for pricing and reports.
                        </div>

                        <div class="text-right mt-4">
                            <a href="{{ route('admin.settings.index') }}" class="btn btn-light border mr-2">Back</a>
                            <button type="submit" class="btn btn-primary">Save Currency Settings</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#system_currency_select').on('change', function () {
                const code = $(this).val();
                const icon = $(this).find(':selected').data('icon');

                $('#currency_name').val(code);
                $('#currency_icon').val(icon);
                $('#system_icon_display').text(icon);
            });
        });
    </script>
@endpush

