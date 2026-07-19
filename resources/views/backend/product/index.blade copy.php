@extends('backend.layouts.master')

@push('css')
<style>
    /* Modal Initially Hidden - Fixed for proper modal functionality */
    #ratingModal {
        display: none;
        opacity: 0;
        visibility: hidden;
    }
    .navbar .nav-link {
        height: 26px !important;
    }
    #ratingModal.show {
        display: block;
        opacity: 1;
        visibility: visible;
    }
    
    /* Modal Backdrop - Fixed to show properly */
    .modal-backdrop {
        opacity: 0.5;
        z-index: 1040;
    }
    
    .modal-backdrop.fade {
        opacity: 0;
    }
    
    .modal-backdrop.fade.show {
        opacity: 0.5;
    }
    
    /* Critical CSS to prevent FOUC on product page load */
    #product-grid-container {
        min-height: 400px;
    }
</style>
@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Product</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Product</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0" style="border-radius: 15px;">
                        <div class="card-body p-3">
                            <form id="filter-form">
                                <div class="row align-items-center">
                                    <div class="col-12 col-md-3 mb-3">
                                        <div class="input-group shadow-sm" style="border-radius: 25px; overflow: hidden; background-color: #f4f6f9; border: 1px solid #e0e0e0;">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text border-0 pl-3 pr-2" style="background-color: transparent;">
                                                    <i class="fas fa-search text-secondary"></i>
                                                </span>
                                            </div>
                                            <input type="text" class="form-control search-input border-0 pl-1" name="search" placeholder="Search..." value="{{ request('search') }}" style="height: 40px; background-color: transparent;" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-3 mb-3">
                                        <select name="category" id="category" class="form-control select2" style="border-radius: 25px;">
                                            <option value="">All Categories</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-2 mb-3">
                                        <select name="sub_category" id="sub_category" class="form-control select2" style="border-radius: 25px;">
                                            <option value="">Sub Category</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-2 mb-3">
                                        <select name="child_category" id="child_category" class="form-control select2" style="border-radius: 25px;">
                                            <option value="">Child Category</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-2 mb-3 text-right">
                                        @can('Manage Products')
                                            <a href="{{ route('admin.products.import.view') }}" class="btn btn-success shadow-sm rounded-pill px-4 btn-block mb-2">
                                                <i class="fas fa-file-import"></i> Import
                                            </a>
                                            <a href="{{ route('admin.products.create') }}" class="btn btn-primary shadow-sm rounded-pill px-4 btn-block">
                                                <i class="fas fa-plus"></i> Create
                                            </a>
                                        @endcan
                                    </div>
                                </div>
                                <div class="row align-items-center">
                                    <div class="col-12 col-md-3 mb-3 mb-md-0">
                                        <select name="sort" id="sort" class="form-control select2">
                                            <option value="">Filter by</option>
                                            <option value="latest" {{ request('sort') == 'latest' || !request('sort') ? 'selected' : '' }}>Latest Products</option>
                                            <option value="a-z" {{ request('sort') == 'a-z' ? 'selected' : '' }}>A-Z way (Alphabetical)</option>
                                            <option value="z-a" {{ request('sort') == 'z-a' ? 'selected' : '' }}>Z-A way (Reverse)</option>
                                            <option value="active" {{ request('sort') == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ request('sort') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-3 mb-3 mb-md-0">
                                        <select name="alphabet" id="alphabet-dropdown" class="form-control select2">
                                            <option value="">Filter by Alphabet (All)</option>
                                            @foreach(range('A', 'Z') as $char)
                                                <option value="{{ $char }}" {{ request('alphabet') == $char ? 'selected' : '' }}>Starts with: {{ $char }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-2 mb-3 mb-md-0">
                                        <select name="product_type" id="product_type_filter" class="form-control select2">
                                            <option value=""> Occasion/ Types</option>
                                            {{-- Legacy Options --}}
                                            <option value="new_arrival" {{ request('product_type') == 'new_arrival' ? 'selected' : '' }}>New Arrival (Legacy)</option>
                                            <option value="upcoming" {{ request('product_type') == 'upcoming' ? 'selected' : '' }}>Upcoming (Legacy)</option>
                                            
                                            {{-- Dynamic Options --}}
                                            @foreach ($productTypes as $type)
                                                <option value="{{ $type->id }}" {{ request('product_type') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @if(isset($vendors) && $vendors->count() > 0)
                                    <div class="col-12 col-md-3 mb-3 mb-md-0">
                                        <select name="vendor" id="vendor_filter" class="form-control select2">
                                            <option value="">Select Vendor</option>
                                            @foreach ($vendors as $vendor)
                                                <option value="{{ $vendor->id }}" {{ request('vendor') == $vendor->id ? 'selected' : '' }}>{{ $vendor->shop_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif
                                    <div class="col-12 col-md-1">
                                        <button type="button" id="reset-filters" class="btn btn-danger btn-sm shadow-sm rounded-pill">
                                            <i class="fas fa-redo mr-1"></i> Reset
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div id="product-grid-container">
                @include('backend.product.product_grid')
            </div>
            
            <style>
                @keyframes pulse {
                    0%, 100% { opacity: 1; }
                    50% { opacity: 0.5; }
                }
            </style>
        </div>
    </section>

<!-- Vendor Conflict Confirmation Modal -->
<div class="modal fade" id="vendorConflictModal" tabindex="-1" role="dialog" aria-labelledby="vendorConflictModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning border-bottom">
                <h5 class="modal-title" id="vendorConflictModalLabel">
                    <i class="fas fa-exclamation-triangle text-dark mr-2"></i>Vendor Conflict
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle mr-2"></i>
                    <span id="vendorConflictMessage"></span>
                </div>
                <p class="text-muted">
                    Do you want to replace the current basket items with products from the new vendor?
                </p>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-warning" id="confirmVendorConflict">
                    <i class="fas fa-check mr-1"></i> Yes, Replace
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

<!-- Rating Modal -->
<div class="modal fade" id="ratingModal" tabindex="-1" role="dialog" aria-labelledby="ratingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title" id="ratingModalLabel">
                    <i class="fas fa-star text-warning mr-2"></i>Rate Product
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <h6 class="text-dark font-weight-bold" id="ratingProductName"></h6>
                </div>
                
                <form id="ratingForm">
                    <input type="hidden" id="ratingProductId" name="product_id">
                    
                    <!-- Star Rating Selector -->
                    <div class="mb-4">
                        <label class="form-label font-weight-bold">Rating <span class="text-danger">*</span></label>
                        <div class="star-rating d-flex" style="gap: 15px; font-size: 32px;">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="far fa-star rating-star cursor-pointer text-muted" data-rating="{{ $i }}" style="cursor: pointer; transition: color 0.2s;"></i>
                            @endfor
                        </div>
                        <input type="hidden" id="ratingValue" name="rating" value="0">
                        <small class="text-muted d-block mt-2">Selected: <span id="selectedRatingText">0</span> stars</small>
                    </div>

                    <!-- Comment -->
                    <div class="mb-4">
                        <label class="form-label font-weight-bold">Comment <span class="text-muted">(Optional)</span></label>
                        <textarea id="ratingComment" name="comment" class="form-control" rows="4" placeholder="Share your feedback about this product..." style="resize: vertical; border-radius: 8px;"></textarea>
                    </div>

                    <div class="alert alert-info alert-sm" id="existingRatingAlert" style="display: none;">
                        <i class="fas fa-info-circle mr-2"></i>You already have a rating for this product. Submitting will update your existing rating.
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light border-top">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="submitRatingBtn">
                    <i class="fas fa-check mr-2"></i>Submit Rating
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <!-- Floating Baskets Container -->
    <div id="floating-baskets-container" class="position-fixed d-flex align-items-center" style="bottom: 30px; right: 30px; z-index: 99999; gap: 20px;">
        <!-- Container and contents allow clicks -->
        <style>#floating-baskets-container, #floating-baskets-container > * { pointer-events: auto; }</style>
        <!-- Floating Basket Widget (Product Request) -->
        @can('Create Product Requests')
        <div id="floating-request-basket" style="display: none;">
            <div class="d-flex flex-column align-items-center">
                <div class="cursor-pointer bg-success text-white shadow-lg rounded-circle d-flex align-items-center justify-content-center position-relative mb-2 basket-fab" 
                     id="go-to-request" title="Product Request" style="width: 55px; height: 55px; transition: all 0.3s ease;">
                    <i class="fas fa-file-import fa-lg"></i>
                    <span id="request-basket-count" class="badge badge-warning position-absolute" style="top: -5px; right: -5px; border-radius: 50%; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; font-size: 11px; border: 2px solid #fff; color: #000;">0</span>
                </div>
                <button class="btn btn-sm btn-light shadow-sm rounded-circle d-flex align-items-center justify-content-center" 
                        id="clear-request-basket" title="Clear Request Basket" style="width: 25px; height: 25px; padding: 0; opacity: 0.8;">
                    <i class="fas fa-times text-danger" style="font-size: 10px;"></i>
                </button>
            </div>
        </div>
        @endcan

        <!-- Floating Basket Widget (Booking) -->
        @can('Manage Order Place')
        <div id="floating-basket" style="display: none;">
            <div class="d-flex flex-column align-items-center">
                <div class="cursor-pointer bg-primary text-white shadow-lg rounded-circle d-flex align-items-center justify-content-center position-relative mb-2 basket-fab" 
                     id="go-to-booking" title="Place Order" style="width: 55px; height: 55px; transition: all 0.3s ease;">
                    <i class="fas fa-shopping-basket fa-lg"></i>
                    <span id="basket-count" class="badge badge-danger position-absolute" style="top: -5px; right: -5px; border-radius: 999px; min-width: 22px; height: 22px; padding: 0 6px; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; border: 2px solid #fff; white-space: nowrap; line-height: 1;">0</span>
                </div>
                <button class="btn btn-sm btn-light shadow-sm rounded-circle d-flex align-items-center justify-content-center" 
                        id="clear-booking-basket" title="Clear Booking Basket" style="width: 25px; height: 25px; padding: 0; opacity: 0.8;">
                    <i class="fas fa-trash-alt text-danger" style="font-size: 10px;"></i>
                </button>
            </div>
        </div>
        @endcan
    </div>

    <style>
        .hover-white { transition: color 0.2s ease; }
        .hover-white:hover { color: #fff !important; }
        .cursor-pointer { cursor: pointer; }
        
        /* Animation Styles */
        @keyframes shake-basket {
            0% { transform: scale(1) rotate(0); }
            20% { transform: scale(1.2) rotate(-10deg); }
            40% { transform: scale(1.2) rotate(10deg); }
            60% { transform: scale(1.2) rotate(-10deg); }
            80% { transform: scale(1.2) rotate(10deg); }
            100% { transform: scale(1) rotate(0); }
        }
        .animate-shake {
            animation: shake-basket 0.5s ease-in-out;
        }

        .basket-fab:hover {
            transform: scale(1.1);
            filter: brightness(1.1);
        }
        .basket-fab {
            box-shadow: 0 4px 15px rgba(0,0,0,0.2) !important;
        }
        .add-to-basket.added, .add-to-request-basket.added {
            background-color: #28a745;
            border-color: #28a745;
            color: #fff;
        }
    </style>
    <script>
        let initialLoad = true; // Fix ReferenceError in pagination clicks
        $(document).ready(function() {
            // Ensure modal is properly initialized
            $('#ratingModal').modal({
                show: false,
                backdrop: 'static',
                keyboard: false
            });
            
            // Ensure grid is visible on page load - initial state should be opacity 1
            if ($('#product-grid-container').data('loaded')) {
                $('#product-grid-container').stop(true, true).css('opacity', '1');
            }
            $('#grid-loader').hide();

            // --- Basket Logic Start (Database Cart System) ---
            
            /**
             * Update basket UI with counts and button states
             */
            function updateBasketUI() {
                // Get current counts from database via AJAX
                $.ajax({
                    url: "{{ route('admin.cart.count') }}?cart_type=booking",
                    method: 'GET',
                    success: function(data) {
                        $('#basket-count').text(data.count);
                        if (data.count > 0) {
                            $('#floating-basket').fadeIn();
                        } else {
                            $('#floating-basket').fadeOut();
                        }
                    }
                });

                $.ajax({
                    url: "{{ route('admin.cart.count') }}?cart_type=request",
                    method: 'GET',
                    success: function(data) {
                        $('#request-basket-count').text(data.count);
                        if (data.count > 0) {
                            $('#floating-request-basket').fadeIn();
                        } else {
                            $('#floating-request-basket').fadeOut();
                        }
                    }
                });

                // Update button states
                updateButtonStates();
            }

            /**
             * Update button added/not-added states
             */
            function updateButtonStates() {
                $.ajax({
                    url: "{{ route('admin.cart.items') }}?cart_type=booking",
                    method: 'GET',
                    success: function(data) {
                        const bookingIds = data.product_ids;
                        $('.add-to-basket').each(function() {
                            const id = $(this).data('id').toString();
                            if (bookingIds.includes(parseInt(id))) {
                                $(this).addClass('added').html('<i class="fas fa-check mr-1"></i> Added');
                            } else {
                                $(this).removeClass('added').html('<i class="fas fa-shopping-basket mr-1"></i> Add to Basket');
                            }
                        });
                    }
                });

                $.ajax({
                    url: "{{ route('admin.cart.items') }}?cart_type=request",
                    method: 'GET',
                    success: function(data) {
                        const requestIds = data.product_ids;
                        $('.add-to-request-basket').each(function() {
                            const id = $(this).data('id').toString();
                            if (requestIds.includes(parseInt(id))) {
                                $(this).addClass('added').html('<i class="fas fa-check mr-1"></i> Added');
                            } else {
                                $(this).removeClass('added').html('<i class="fas fa-file-import mr-1"></i> Add to Request');
                            }
                        });
                    }
                });
            }

            // Initial UI Update on page load
            updateBasketUI();

            // Clear Request Basket
            $(document).on('click', '#clear-request-basket', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                Swal.fire({
                    title: 'Clear Request Basket?',
                    text: "You are about to remove all items from the request basket.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, clear it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.cart.clear') }}",
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: { cart_type: 'request' },
                            success: function(response) {
                                toastr.info(response.message);
                                updateBasketUI();
                            },
                            error: function() {
                                toastr.error('Error clearing request basket');
                            }
                        });
                    }
                });
            });

            // Clear Booking Basket
            $(document).on('click', '#clear-booking-basket', function(e) {
                e.preventDefault();
                e.stopPropagation();

                Swal.fire({
                    title: 'Clear Booking Basket?',
                    text: "You are about to remove all items from the booking basket.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#6777ef',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, clear it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.cart.clear') }}",
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: { cart_type: 'booking' },
                            success: function(response) {
                                toastr.info(response.message);
                                updateBasketUI();
                            },
                            error: function() {
                                toastr.error('Error clearing booking basket');
                            }
                        });
                    }
                });
            });

            // Add to Booking Basket Click
            $(document).on('click', '.add-to-basket', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const productId = $(this).data('id');
                if (!productId) return;

                $.ajax({
                    url: "{{ route('admin.cart.add') }}",
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        product_id: productId,
                        cart_type: 'booking'
                    },
                    success: function(response) {
                        if (response.vendor_conflict) {
                            // Show vendor conflict modal
                            $('#vendorConflictMessage').text(response.message);
                            $('#vendorConflictModal').modal('show');
                            
                            // Store the product ID for later use
                            $('#vendorConflictModal').data('product-id', productId);
                            $('#vendorConflictModal').data('cart-type', 'booking');
                        } else if (response.success) {
                            $('#go-to-booking').addClass('animate-shake');
                            setTimeout(function() { 
                                $('#go-to-booking').removeClass('animate-shake'); 
                            }, 500);
                            toastr.success(response.message);
                            updateBasketUI();
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Error adding to basket');
                        console.error(xhr);
                    }
                });
            });

            // Add to Request Basket Click
            $(document).on('click', '.add-to-request-basket', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const productId = $(this).data('id');
                if (!productId) return;

                $.ajax({
                    url: "{{ route('admin.cart.add') }}",
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        product_id: productId,
                        cart_type: 'request'
                    },
                    success: function(response) {
                        if (response.vendor_conflict) {
                            // Show vendor conflict modal
                            $('#vendorConflictMessage').text(response.message);
                            $('#vendorConflictModal').modal('show');
                            
                            // Store the product ID for later use
                            $('#vendorConflictModal').data('product-id', productId);
                            $('#vendorConflictModal').data('cart-type', 'request');
                        } else if (response.success) {
                            $('#go-to-request').addClass('animate-shake');
                            setTimeout(function() { 
                                $('#go-to-request').removeClass('animate-shake'); 
                            }, 500);
                            toastr.success(response.message);
                            updateBasketUI();
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Error adding to request basket');
                        console.error(xhr);
                    }
                });
            });

            // Navigation
            $(document).on('click', '#go-to-booking', function() {
                $.ajax({
                    url: "{{ route('admin.cart.product-ids') }}?cart_type=booking",
                    method: 'GET',
                    success: function(data) {
                        const ids = data.ids.join(',');
                        window.location.href = "{{ route('admin.bookings.create') }}?ids=" + ids;
                    }
                });
            });

            $(document).on('click', '#go-to-request', function() {
                $.ajax({
                    url: "{{ route('admin.cart.product-ids') }}?cart_type=request",
                    method: 'GET',
                    success: function(data) {
                        const ids = data.ids.join(',');
                        window.location.href = "{{ route('admin.product-requests.create') }}?ids=" + ids;
                    }
                });
            });

            // Re-apply UI state after AJAX load (pagination/search)
            // NOTE: Removed global ajaxComplete handler that was causing infinite loop
            // The updateBasketUI() is already called explicitly after cart operations
            // $(document).ajaxComplete(function() {
            //     updateBasketUI();
            // });
            // --- Basket Logic End ---

            // Prevent Enter key from submitting form
            $('#filter-form').on('submit', function(e) {
                e.preventDefault();
            });

            function fetchProducts(url = "{{ route('admin.products.index') }}", scrollToTop = false) {
                // Ensure search and filters are captured correctly
                let search = $('.search-input').val();
                let category = $('#category').val();
                let sub_category = $('#sub_category').val();
                let child_category = $('#child_category').val();
                let alphabet = $('#alphabet-dropdown').val();
                let product_type = $('#product_type_filter').val();
                let vendor = $('#vendor_filter').val();
                let sort = $('#sort').val();

                $.ajax({
                    url: url,
                    method: 'GET',
                    data: { 
                        search: search,
                        category: category,
                        sub_category: sub_category,
                        child_category: child_category,
                        alphabet: alphabet,
                        product_type: product_type,
                        vendor: vendor,
                        sort: sort
                    },
                    beforeSend: function() {
                        // Show skeleton loader only on subsequent loads, not on initial page load
                        if (!initialLoad) {
                            $('#grid-loader').show();
                            $('#product-grid-container').css('opacity', '0');
                        } else {
                            $('#product-grid-container').css('opacity', '0.5');
                        }
                    },
                    success: function(response) {
                        // Hide loader
                        $('#grid-loader').hide();
                        
                        // Update content
                        $('#product-grid-container').html(response);
                        $('#product-grid-container').attr('data-loaded', 'true');
                        
                        // Fade in with smooth transition
                        $('#product-grid-container').stop(true, true).css('opacity', '1');
                        
                        // Mark initial load as complete
                        initialLoad = false;
                        
                        // Update history API without reloading
                        let params = new URLSearchParams({
                            search: search,
                            category: category,
                            sub_category: sub_category,
                            child_category: child_category,
                            alphabet: alphabet,
                            product_type: product_type,
                            sort: sort
                        });
                        
                        // Handle pagination page if in URL
                        let pageMatch = url.match(/page=(\d+)/);
                        if (pageMatch) {
                            params.set('page', pageMatch[1]);
                        }

                        let newUrl = "{{ route('admin.products.index') }}" + '?' + params.toString();
                        window.history.replaceState({path: newUrl}, '', newUrl);

                        // Scroll to top only if requested (e.g., from pagination)
                        if (scrollToTop && $(window).scrollTop() > 200) {
                            $('html, body').stop().animate({ scrollTop: 0 }, 400);
                        }
                    },
                    error: function(xhr) {
                        console.log(xhr);
                        $('#grid-loader').hide();
                        $('#product-grid-container').stop(true, true).css('opacity', '1');
                    }
                });
            }

            // Status Change
            $('body').on('change', '.change-status', function() {
                let isChecked = $(this).is(':checked');
                let id = $(this).data('id');
                let $this = $(this); // Store reference

                $.ajax({
                    url: "{{ route('admin.products.change-status') }}",
                    method: 'PUT',
                    data: {
                        status: isChecked,
                        id: id
                    },
                    success: function(data) {
                         toastr.success(data.message); 
                    },
                    error: function(xhr, status, error) {
                        console.error("Status update error:", error);
                        console.log("Response:", xhr.responseText);
                        if(xhr.status !== 200) {
                             $this.prop('checked', !isChecked);
                             toastr.error('Failed to update status');
                        }
                    }
                })
            })

            // Auto Search
            let timeout = null;
            $('body').on('input', '.search-input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    fetchProducts();
                }, 300); 
            });

            // Cache for category data
            const categoryCache = {
                sub: {},
                child: {}
            };

            // Category Change
            $('body').on('change', '#category', function(e, isInitialLoad = false) {
                let id = $(this).val();
                
                // Clear and reset sub/child categories silently without triggering 'change' event
                 // We don't want to trigger child change events that fetch products again
                $('#sub_category').html('<option value="">--Sub Category--</option>');
                $('#child_category').html('<option value="">--Child Category--</option>');

                if (id) {
                    if (categoryCache.sub[id]) {
                        // Use cached data
                        $.each(categoryCache.sub[id], function(i, item) {
                            $('#sub_category').append(`<option value="${item.id}">${item.name}</option>`);
                        });
                    } else {
                        // Fetch from server and cache
                        $.ajax({
                            url: "{{ route('admin.get-subCategories') }}",
                            method: 'GET',
                            data: { id: id },
                            success: function(data) {
                                categoryCache.sub[id] = data; // Cache results
                                $.each(data, function(i, item) {
                                    $('#sub_category').append(`<option value="${item.id}">${item.name}</option>`);
                                });
                            }
                        });
                    }
                }
                
                // Only fetch products if this wasn't called during the initial page load setup
                if (!isInitialLoad) {
                    fetchProducts();
                }
            });

            // Sub Category Change
            $('body').on('change', '#sub_category', function(e, isInitialLoad = false) {
                let id = $(this).val();
                
                // Clear and reset child categories silently
                $('#child_category').html('<option value="">--Child Category--</option>');

                if (id) {
                    if (categoryCache.child[id]) {
                        // Use cached data
                        $.each(categoryCache.child[id], function(i, item) {
                            $('#child_category').append(`<option value="${item.id}">${item.name}</option>`);
                        });
                    } else {
                        // Fetch from server and cache
                        $.ajax({
                            url: "{{ route('admin.get-child-categories') }}",
                            method: 'GET',
                            data: { id: id },
                            success: function(data) {
                                categoryCache.child[id] = data; // Cache results
                                $.each(data, function(i, item) {
                                    $('#child_category').append(`<option value="${item.id}">${item.name}</option>`);
                                });
                            }
                        });
                    }
                }
                
                if (!isInitialLoad) {
                    fetchProducts();
                }
            });

            // Child Category Filter
            $('body').on('change', '#child_category', function() {
                fetchProducts();
            });

            // Sort Change
            $('body').on('change', '#sort', function() {
                fetchProducts();
            });

            // Alphabet Dropdown Change
            $('body').on('change', '#alphabet-dropdown', function() {
                fetchProducts();
            });

            // Product Type Filter Change
            $('body').on('change', '#product_type_filter', function() {
                fetchProducts();
            });

            // Vendor Conflict Modal Confirm Button
            $('#confirmVendorConflict').on('click', function() {
                const productId = $('#vendorConflictModal').data('product-id');
                const cartType = $('#vendorConflictModal').data('cart-type');
                
                $('#vendorConflictModal').modal('hide');
                
                // Add product with force_clear
                $.ajax({
                    url: "{{ route('admin.cart.add') }}",
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        product_id: productId,
                        cart_type: cartType,
                        force_clear: true
                    },
                    success: function(res) {
                        if (res.success) {
                            toastr.success(res.message);
                            updateBasketUI();
                        }
                    }
                });
            });

            // Vendor Filter Change
            $('body').on('change', '#vendor_filter', function() {
                fetchProducts();
            });

            // Reset Filters
            $('body').on('click', '#reset-filters', function() {
                $('.search-input').val('');
                
                // Reset select2 and triggers without calling fetchProducts multiple times
                $('#category').val('');
                $('#sub_category').html('<option value="">Sub Category</option>');
                $('#child_category').html('<option value="">Child Category</option>');
                $('#alphabet-dropdown').val('');
                $('#product_type_filter').val('');
                $('#vendor_filter').val('');
                $('#sort').val('latest');
                
                // Re-trigger select2 UI update without triggering 'change' listener
                $('.select2').trigger('change.select2'); 
                
                fetchProducts();
            });
            
            // Handle Pagination clicks via AJAX
             $('body').on('click', '.pagination a', function(e) {
                e.preventDefault();
                initialLoad = false; // Allow pagination to trigger
                let url = $(this).attr('href');
                fetchProducts(url, true); // Pass true to scroll to top
            });

            // Rating Button Click
            $('body').on('click', '.add-rating-btn', function() {
                let productId = $(this).data('product-id');
                let productName = $(this).data('product-name');
                
                // Reset form
                $('#ratingForm')[0].reset();
                $('#ratingValue').val('0');
                $('#selectedRatingText').text('0');
                $('.rating-star').removeClass('fas').addClass('far').css('color', '');
                $('#existingRatingAlert').hide();
                
                // Set product info
                $('#ratingProductId').val(productId);
                $('#ratingProductName').text(productName);
                
                // Fetch existing rating if any
                $.ajax({
                    url: "{{ route('admin.reviews.user-product', ['productId' => 'PRODUCT_ID']) }}".replace('PRODUCT_ID', productId),
                    method: 'GET',
                    success: function(response) {
                        if (response && response.rating) {
                            $('#ratingValue').val(response.rating);
                            $('#selectedRatingText').text(response.rating);
                            $('#ratingComment').val(response.comment || '');
                            
                            // Highlight stars
                            $('.rating-star').each(function() {
                                if ($(this).data('rating') <= response.rating) {
                                    $(this).removeClass('far').addClass('fas').css('color', '#ffc107');
                                }
                            });
                            
                            $('#existingRatingAlert').show();
                        }
                    }
                });
                
                $('#ratingModal').modal('show');
            });

            // Star Rating Interaction
            $('body').on('click', '.rating-star', function() {
                let rating = $(this).data('rating');
                $('#ratingValue').val(rating);
                $('#selectedRatingText').text(rating);
                
                $('.rating-star').each(function() {
                    if ($(this).data('rating') <= rating) {
                        $(this).removeClass('far').addClass('fas').css('color', '#ffc107');
                    } else {
                        $(this).removeClass('fas').addClass('far').css('color', '');
                    }
                });
            });

            // Star Hover Effect
            $('body').on('mouseenter', '.rating-star', function() {
                let rating = $(this).data('rating');
                $('.rating-star').each(function() {
                    if ($(this).data('rating') <= rating) {
                        $(this).css('color', '#ffc107');
                    } else {
                        $(this).css('color', '');
                    }
                });
            });

            $('body').on('mouseleave', '.rating-star', function() {
                let currentRating = $('#ratingValue').val();
                $('.rating-star').each(function() {
                    if ($(this).data('rating') <= currentRating) {
                        $(this).css('color', '#ffc107');
                    } else {
                        $(this).css('color', '');
                    }
                });
            });

            // Submit Rating
            $('#submitRatingBtn').click(function() {
                let productId = $('#ratingProductId').val();
                let rating = $('#ratingValue').val();
                let comment = $('#ratingComment').val();
                
                if (!rating || rating == 0) {
                    toastr.error('Please select a rating');
                    return;
                }
                
                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Submitting...');
                
                $.ajax({
                    url: "{{ route('admin.reviews.store') }}",
                    method: 'POST',
                    data: {
                        product_id: productId,
                        rating: rating,
                        comment: comment,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        toastr.success('Rating submitted successfully!');
                        $('#ratingModal').modal('hide');
                        // Refresh the grid to show updated ratings
                        fetchProducts();
                        
                        $('#submitRatingBtn').prop('disabled', false).html('<i class="fas fa-check mr-2"></i>Submit Rating');
                    },
                    error: function(response) {
                        let message = 'Error submitting rating';
                        if (response.responseJSON && response.responseJSON.message) {
                            message = response.responseJSON.message;
                        }
                        toastr.error(message);
                        $('#submitRatingBtn').prop('disabled', false).html('<i class="fas fa-check mr-2"></i>Submit Rating');
                    }
                });
            });

        })
    </script>
@endpush
