@extends('backend.layouts.master')

@section('title', 'Create Brand')

@section('content')
    <section class="section">
        {{-- Header --}}
        <div class="section-header">
            <div class="d-flex align-items-center flex-wrap w-100">
                <h1 class="mb-2 mb-sm-0 d-flex align-items-center" style="font-size: 1.25rem;">
                    <i class="fas fa-tag mr-2" style="color: #2563eb;"></i>
                    Create Brand
                </h1>
                <div class="ml-auto d-flex align-items-center flex-wrap">
                    <div class="section-header-breadcrumb">
                        <div class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                            </a>
                        </div>
                        <div class="breadcrumb-item">
                            <a href="{{ route('admin.brand.index') }}">Brands</a>
                        </div>
                        <div class="breadcrumb-item active">Create</div>
                    </div>
                    <a href="{{ route('admin.brand.index') }}" class="btn btn-primary btn-sm ml-2 shadow-sm">
                        <i class="fas fa-arrow-left mr-1"></i>
                        <span class="d-none d-sm-inline">Back to List</span>
                        <span class="d-sm-none">Back</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        {{-- Card Header --}}
                        <div class="card-header bg-white py-3 border-0 d-flex flex-wrap align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="mr-3 p-2 rounded-circle text-white d-none d-sm-flex" style="background: #2563eb;">
                                    <i class="fas fa-plus-circle"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 font-weight-bold text-dark">
                                        <i class="fas fa-building" style="color: #2563eb;"></i>
                                        <span class="ml-1 d-sm-none">Add New Brand</span>
                                        <span class="d-none d-sm-inline">Add New Brand</span>
                                    </h5>
                                    <small class="text-muted d-none d-sm-block">Create a new brand for your products</small>
                                </div>
                            </div>
                            <div class="ml-auto d-sm-block d-none">
                                <span class="badge p-2" style="background: #2563eb; color: #ffffff;">
                                    <i class="fas fa-info-circle mr-1"></i> Required fields are marked with *
                                </span>
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="card-body p-3 p-sm-4">
                            <form action="{{ route('admin.brand.store') }}" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                                @csrf

                                {{-- Form Row 1: Brand Name & Status --}}
                                <div class="row">
                                    <div class="form-group col-12 col-md-6">
                                        <label for="inputName" class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                                            <i class="fas fa-tag" style="color: #2563eb; width: 18px;"></i>
                                            <span class="ml-1">Brand Name</span>
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('name') is-invalid @enderror" 
                                               id="inputName"
                                               name="name" 
                                               value="{{ old('name') }}"
                                               placeholder="Enter brand name"
                                               style="height: 44px; font-size: 0.95rem; border-radius: 10px; border: 2px solid #e2e8f0; width: 100%;"
                                               required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted" style="font-size: 0.75rem;">
                                            <i class="fas fa-info-circle" style="color: #2563eb;"></i>
                                            <span class="ml-1">Example: Nike, Adidas, Samsung, etc.</span>
                                        </small>
                                    </div>

                                    <div class="form-group col-12 col-md-6">
                                        <label for="inputState" class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                                            <i class="fas fa-toggle-on" style="color: #2563eb; width: 18px;"></i>
                                            <span class="ml-1">Status</span>
                                            <span class="text-danger">*</span>
                                        </label>
                                        <select id="inputState" 
                                                class="form-control @error('status') is-invalid @enderror" 
                                                name="status"
                                                style="height: 44px; font-size: 0.95rem; border-radius: 10px; border: 2px solid #e2e8f0; width: 100%;"
                                                required>
                                            <option value="1" {{ old('status') == 1 ? 'selected' : '' }}>
                                                Active
                                            </option>
                                            <option value="0" {{ old('status') == 0 ? 'selected' : '' }}>
                                                Inactive
                                            </option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted" style="font-size: 0.75rem;">
                                            <i class="fas fa-info-circle" style="color: #2563eb;"></i>
                                            <span class="ml-1">Active brands will be visible to customers</span>
                                        </small>
                                    </div>
                                </div>

                                {{-- Preview Card --}}
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="bg-light p-3 rounded border border-dashed" style="border-color: #d1d3e2 !important; border-style: dashed !important;">
                                            <div class="d-flex align-items-center flex-wrap">
                                                <i class="fas fa-eye" style="color: #2563eb;"></i>
                                                <span class="font-weight-bold ml-2 mr-2">Preview:</span>
                                                <span id="brand-preview" class="text-muted" style="font-size: 0.95rem; padding: 8px 15px; background: white; border-radius: 6px; border: 1px solid #e9ecef; display: inline-block;">
                                                    <i class="fas fa-tag mr-1" style="color: #2563eb;"></i>
                                                    <span id="brand-name-preview" class="font-weight-bold text-dark">New Brand</span>
                                                    <span id="brand-status-preview" class="badge ml-2" style="background: #1cc88a; color: #ffffff;">Active</span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Submit Buttons --}}
                                <div class="row mt-4 pt-3 border-top">
                                    <div class="col-12">
                                        <div class="d-flex flex-column flex-sm-row justify-content-sm-end" style="gap: 12px;">
                                            <button type="reset" class="btn btn-secondary px-4 order-2 order-sm-1" 
                                                    style="border-radius: 10px; min-height: 44px; font-weight: 600; transition: all 0.3s ease; color: #ffffff; background: #6c757d; border: none;">
                                                <i class="fas fa-undo mr-1"></i> Reset
                                            </button>
                                            <button type="submit" class="btn px-5 shadow-sm order-1 order-sm-2" 
                                                    style="background: #2563eb; color: #ffffff; border: none; border-radius: 10px; min-height: 44px; font-weight: 600; transition: all 0.3s ease;">
                                                <i class="fas fa-save mr-2"></i> Create Brand
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
<style>
    /* =============================================
       BRAND CREATE - PREMIUM DESIGN
       ============================================= */
    
    .border-dashed {
        border-style: dashed !important;
        border-color: #d1d3e2 !important;
    }
    
    .form-group label {
        font-size: 0.85rem !important;
        margin-bottom: 0.5rem !important;
        letter-spacing: 0.3px;
    }
    
    .form-group .form-text {
        font-size: 0.75rem !important;
        margin-top: 0.25rem;
    }
    
    .form-control {
        border-radius: 10px !important;
        border: 2px solid #e2e8f0;
        padding: 0.7rem 1rem;
        transition: all 0.3s ease;
        font-size: 0.95rem;
        background: #fafbfc;
        height: 44px !important;
        width: 100% !important;
    }

    .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.15);
        background: #ffffff;
    }
    
    .form-control.is-invalid:focus {
        border-color: #e74a3b;
        box-shadow: 0 0 0 0.2rem rgba(231, 74, 59, 0.15);
    }

    .form-control:hover {
        border-color: #2563eb;
        background: #ffffff;
    }

    .form-control:focus {
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
    }

    ::placeholder {
        color: #adb5bd !important;
        font-size: 0.9rem;
        opacity: 0.7;
    }
    
    /* Preview Section */
    #brand-preview {
        font-size: 0.95rem;
        padding: 8px 15px;
        background: white;
        border-radius: 6px;
        border: 1px solid #e9ecef;
        display: inline-block;
    }

    #brand-preview .badge {
        font-weight: 600 !important;
        padding: 0.3rem 0.7rem !important;
        border-radius: 50rem !important;
        font-size: 0.7rem !important;
    }

    /* Buttons */
    .btn {
        border-radius: 10px !important;
        font-weight: 600 !important;
        transition: all 0.3s ease !important;
        min-height: 44px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 0 1.5rem !important;
        font-size: 0.9rem !important;
        letter-spacing: 0.3px;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    }

    .btn:active {
        transform: scale(0.97);
    }

    .btn-primary {
        background: #2563eb !important;
        border: none !important;
        color: #ffffff !important;
    }

    .btn-primary:hover {
        background: #1d4ed8 !important;
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.35) !important;
    }

    .btn-secondary {
        background: #6c757d !important;
        border: none !important;
        color: #ffffff !important;
    }

    .btn-secondary:hover {
        background: #5a6268 !important;
        box-shadow: 0 6px 20px rgba(108, 117, 125, 0.35) !important;
        transform: translateY(-2px);
    }

    /* Card */
    .card {
        border-radius: 16px !important;
        overflow: hidden !important;
    }

    .card-header:first-child {
        border-radius: 16px 16px 0 0 !important;
    }

    .card-footer:last-child {
        border-radius: 0 0 16px 16px !important;
    }

    /* =============================================
       RESPONSIVE BREAKPOINTS
       ============================================= */

    @media (max-width: 575.98px) {
        .section-header {
            padding: 12px 15px !important;
        }
        
        .section-header h1 {
            font-size: 1rem !important;
        }
        
        .card-body {
            padding: 15px !important;
        }
        
        .card-header {
            padding: 10px 15px !important;
        }

        .form-group {
            margin-bottom: 1.25rem !important;
        }

        .form-control {
            height: 40px !important;
            font-size: 0.85rem !important;
        }
        
        .text-right {
            text-align: center !important;
        }
        
        #brand-preview {
            font-size: 0.8rem !important;
            padding: 5px 10px !important;
            display: block !important;
            text-align: center !important;
        }
        
        .border-dashed {
            padding: 12px !important;
        }
        
        .bg-light .d-flex {
            flex-direction: column !important;
            align-items: flex-start !important;
        }
        
        .bg-light .d-flex .font-weight-bold {
            margin-bottom: 5px !important;
        }

        .section-header .ml-auto {
            width: 100% !important;
            justify-content: space-between !important;
            flex-wrap: wrap !important;
            gap: 8px !important;
        }

        .section-header .breadcrumb {
            font-size: 0.7rem !important;
        }
        
        .section-header .btn {
            width: auto !important;
        }

        .card-header .d-flex {
            flex-direction: column !important;
            align-items: flex-start !important;
        }

        .card-header .ml-auto {
            margin-left: 0 !important;
            margin-top: 8px !important;
            width: 100% !important;
        }

        .border-top {
            margin-top: 15px !important;
            padding-top: 15px !important;
        }

        /* Mobile Button Fix - Gap */
        .d-flex.flex-column.flex-sm-row {
            gap: 12px !important;
        }
        .d-flex.flex-column.flex-sm-row .btn {
            width: 100% !important;
            justify-content: center !important;
        }

        .section-header .ml-auto .section-header-breadcrumb {
            width: 100% !important;
        }
        .section-header .ml-auto .btn {
            width: 100% !important;
            margin-left: 0 !important;
        }

        /* Button full width on mobile */
        .d-flex.flex-column.flex-sm-row .btn {
            width: 100% !important;
            min-height: 44px !important;
        }
    }
    
    @media (min-width: 576px) and (max-width: 767.98px) {
        .card-body {
            padding: 20px !important;
        }
        
        #brand-preview {
            font-size: 0.85rem !important;
        }

        .form-control {
            height: 42px !important;
        }

        .btn {
            min-height: 40px !important;
            font-size: 0.85rem !important;
        }

        .d-flex.flex-column.flex-sm-row {
            gap: 12px !important;
        }

        .section-header .ml-auto {
            flex-wrap: wrap !important;
            gap: 5px !important;
        }

        .card-header .d-flex {
            flex-wrap: wrap !important;
        }
    }
    
    @media (max-width: 767.98px) {
        .section-header .ml-auto {
            width: 100% !important;
            justify-content: space-between !important;
            flex-wrap: wrap !important;
            gap: 8px !important;
        }

        .section-header .breadcrumb {
            font-size: 0.75rem !important;
        }
        
        .section-header .btn {
            width: auto !important;
        }

        .card-header .d-flex {
            flex-direction: column !important;
            align-items: flex-start !important;
        }

        .card-header .ml-auto {
            margin-left: 0 !important;
            margin-top: 8px !important;
            width: 100% !important;
        }
    }

    @media (min-width: 768px) and (max-width: 991.98px) {
        .card-body {
            padding: 25px !important;
        }
        .form-control {
            height: 44px !important;
        }
        .btn {
            min-height: 44px !important;
        }
        .d-flex.flex-column.flex-sm-row {
            gap: 16px !important;
        }
    }

    @media (min-width: 992px) {
        .card-body {
            padding: 30px !important;
        }
        .form-control {
            height: 44px !important;
        }
        .d-flex.flex-column.flex-sm-row {
            gap: 16px !important;
        }
    }
    
    /* Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .card {
        animation: fadeInUp 0.4s ease-out;
    }

    /* Scrollbar */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
        background: #2563eb;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #1d4ed8;
    }

    /* Validation */
    .invalid-feedback {
        font-size: 0.8rem;
        font-weight: 500;
        margin-top: 0.3rem;
    }

    .was-validated .form-control:valid,
    .form-control.is-valid {
        border-color: #1cc88a !important;
    }

    .was-validated .form-control:invalid,
    .form-control.is-invalid {
        border-color: #e74a3b !important;
    }

    .text-danger {
        font-weight: 700 !important;
        font-size: 1.1rem;
    }

    .form-group label i {
        width: 18px !important;
        text-align: center !important;
    }

    /* Focus glow effect */
    .form-control:focus {
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
    }

    /* Button hover effects */
    .btn-secondary:hover {
        background: #5a6268 !important;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(108, 117, 125, 0.35) !important;
    }

    .btn-primary:hover {
        background: #1d4ed8 !important;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.35) !important;
    }

    /* Border Top */
    .border-top {
        border-top: 2px solid #e2e8f0 !important;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Real-time preview update
        function updatePreview() {
            let brandName = $('#inputName').val() || 'New Brand';
            let status = $('#inputState').val();
            let statusText = status == '1' ? 'Active' : 'Inactive';
            let statusClass = status == '1' ? 'badge-success' : 'badge-danger';
            
            $('#brand-name-preview').text(brandName);
            $('#brand-status-preview')
                .text(statusText)
                .removeClass('badge-success badge-danger')
                .addClass(statusClass);
        }
        
        // Trigger preview on input change
        $('#inputName, #inputState').on('change keyup', function() {
            updatePreview();
        });
        
        // Form validation
        $('form.needs-validation').on('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            $(this).addClass('was-validated');
        });
        
        // Reset button
        $('button[type="reset"]').on('click', function(e) {
            e.preventDefault();
            $('form')[0].reset();
            $('form').removeClass('was-validated');
            $('.is-invalid').removeClass('is-invalid');
            updatePreview();
        });
        
        // Initial preview
        updatePreview();
    });
</script>
@endpush