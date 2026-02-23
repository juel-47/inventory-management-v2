@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Low Stock Alert</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.reports.index') }}">Reports</a></div>
                <div class="breadcrumb-item">Low Stock Alert</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Products Below Minimum Inventory Level</h4>
                            <div class="card-header-action">
                                <button type="button" class="btn btn-success" id="add_to_booking_btn" style="display: none;">
                                    <i class="fas fa-shopping-cart"></i> Add to Booking (<span id="selected_count">0</span>)
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Search Form -->
                            <div class="mb-4">
                                <div class="row">
                                    {{-- <div class="col-md-10">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                            </div>
                                            <input type="text" class="form-control" id="search-input" 
                                                   placeholder="Type to search by product name, SKU, barcode, or category..." 
                                                   value="{{ request('search') }}" autocomplete="off">
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary d-none" type="button" id="clear-search-btn">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div> --}}
                                    <div class="col-md-2 text-center">
                                        <div class="spinner-border text-primary d-none" id="search-spinner" role="status" style="width: 2rem; height: 2rem;">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="products-container">
                            @if($products->count() > 0)
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i> 
                                    <strong>{{ $products->total() }}</strong> product(s) found
                                    @if(request('search'))
                                        matching "{{ request('search') }}"
                                    @else
                                        with stock levels at or below 100!
                                    @endif
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped" id="table-1">
                                        <thead>
                                            <tr>
                                                <th width="5%">
                                                    <input type="checkbox" id="select_all" title="Select All">
                                                </th>
                                                <th>Product</th>
                                                <th>SKU</th>
                                                <th>Category</th>
                                                <th>Current Stock</th>
                                                <th>Status</th>
                                                <th width="15%">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($products as $product)
                                                @php
                                                    $currentStock = $product->inventory_stocks_sum_quantity ?? 0;
                                                    $isCritical = $currentStock == 0;
                                                @endphp
                                                <tr class="{{ $isCritical ? 'table-danger' : '' }}">
                                                    <td>
                                                        <input type="checkbox" class="product-checkbox" value="{{ $product->id }}" data-product-name="{{ $product->name }}">
                                                    </td>
                                                    <td>{{ $product->name }}</td>
                                                    <td>{{ $product->sku }}</td>
                                                    <td>{{ $product->category->name ?? 'N/A' }}</td>
                                                    <td>
                                                        <span class="badge badge-{{ $isCritical ? 'danger' : 'warning' }}">
                                                            {{ $currentStock }} {{ $product->unit->name ?? '' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($isCritical)
                                                            <span class="badge badge-danger">OUT OF STOCK</span>
                                                        @else
                                                            <span class="badge badge-warning">LOW STOCK</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @can('Manage Order Place')
                                                        <button type="button" class="btn btn-outline-info btn-sm add-to-basket" data-id="{{ $product->id }}" title="Add to Booking Basket">
                                                            <i class="fas fa-shopping-basket"></i>
                                                        </button>
                                                        @endcan
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <p class="text-muted mb-1 mt-3 text-center" style="font-size: 14px; font-weight: 500;">
                                    Showing 
                                    <span class="text-dark font-weight-bold">
                                        {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }}
                                    </span> 
                                    of 
                                     <span class="text-dark font-weight-bold">
                                        {{ $products->total() }}
                                    </span> 
                                    products low stock
                                </p>
                                <div class="mt-4 d-flex justify-content-center flex-wrap custom-pagination" id="pagination-container">
                                    {{ $products->links() }}
                                </div>
                            @else
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i> 
                                    @if(request('search'))
                                        No products found matching "{{ request('search') }}"
                                    @else
                                        All products are adequately stocked!
                                    @endif
                                </div>
                            @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <!-- Floating Basket Container (Booking Only) -->
    @can('Manage Order Place')
    <div id="floating-baskets-container" class="position-fixed d-flex align-items-center" style="bottom: 30px; right: 30px; z-index: 99999; gap: 20px;">
        <style>#floating-baskets-container, #floating-baskets-container > * { pointer-events: auto; }</style>
        <!-- Floating Basket Widget (Booking) -->
        <div id="floating-basket" style="display: none;">
            <div class="d-flex flex-column align-items-center">
                <div class="cursor-pointer bg-primary text-white shadow-lg rounded-circle d-flex align-items-center justify-content-center position-relative mb-2 basket-fab" 
                     id="go-to-booking" title="Place Order" style="width: 55px; height: 55px; transition: all 0.3s ease;">
                    <i class="fas fa-shopping-basket fa-lg"></i>
                    <span id="basket-count" class="badge badge-danger position-absolute" style="top: -5px; right: -5px; border-radius: 50%; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; font-size: 11px; border: 2px solid #fff;">0</span>
                </div>
                <button class="btn btn-sm btn-light shadow-sm rounded-circle d-flex align-items-center justify-content-center" 
                        id="clear-booking-basket" title="Clear Booking Basket" style="width: 25px; height: 25px; padding: 0; opacity: 0.8;">
                    <i class="fas fa-trash-alt text-danger" style="font-size: 10px;"></i>
                </button>
            </div>
        </div>
    </div>
    @endcan

    <style>
        .cursor-pointer { cursor: pointer; }
        
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
        $("#table-1").dataTable({
            "order": [[4, "asc"]],
            paging: false,
            info: false,
            searching: true,
            "language": {
                "search": "Filter:",
                "searchPlaceholder": "Search in table..."
            }
        });

        $(document).ready(function() {
            // --- Auto Search Logic ---
            let searchTimeout = null;
            let currentSearch = "{{ request('search') }}";

            // Cache selectors (search input may be commented-out in some views)
            const $searchInput = $('#search-input');
            const $clearSearchBtn = $('#clear-search-btn');
            const $searchSpinner = $('#search-spinner');

            // Show/hide clear button (guard when elements are missing)
            function toggleClearButton() {
                if ($searchInput.length && $clearSearchBtn.length) {
                    const val = $searchInput.val();
                    if (typeof val !== 'undefined' && val !== null && val.toString().length > 0) {
                        $clearSearchBtn.removeClass('d-none');
                    } else {
                        $clearSearchBtn.addClass('d-none');
                    }
                }
            }

            // Initial state (only if button exists)
            if ($clearSearchBtn.length) toggleClearButton();

            // Auto-search on input (only if input exists)
            if ($searchInput.length) {
                $searchInput.on('input', function() {
                    let searchTerm = $(this).val();
                    toggleClearButton();

                    // Clear previous timeout
                    clearTimeout(searchTimeout);

                    // Show spinner (if exists)
                    if ($searchSpinner.length) $searchSpinner.removeClass('d-none');

                    // Debounce: Wait 500ms after user stops typing
                    searchTimeout = setTimeout(function() {
                        performSearch(searchTerm);
                    }, 500);
                });
            }

            // Clear search (only if clear button exists)
            if ($clearSearchBtn.length) {
                $clearSearchBtn.on('click', function() {
                    if ($searchInput.length) $searchInput.val('');
                    toggleClearButton();
                    performSearch('');
                });
            }

            // Perform search via AJAX
            function performSearch(searchTerm) {
                let url = "{{ route('admin.reports.low-stock') }}";
                if (searchTerm) {
                    url += '?search=' + encodeURIComponent(searchTerm);
                }

                $.ajax({
                    url: url,
                    method: 'GET',
                    beforeSend: function() {
                        $('#products-container').css('opacity', '0.5');
                    },
                    success: function(response) {
                        // Parse the full HTML response
                        let $response = $(response);
                        
                        // Extract the products container content
                        let newContent = $response.find('#products-container').html();
                        
                        if (!newContent) {
                            // Fallback: extract alert and table from card body
                            let cardBody = $response.find('.card-body');
                            let alertHtml = cardBody.find('.alert').first().parent().html();
                            let tableHtml = cardBody.find('.table-responsive').parent().html();
                            
                            if (alertHtml && tableHtml) {
                                newContent = alertHtml + tableHtml + cardBody.find('.custom-pagination').parent().html();
                            } else if (alertHtml) {
                                newContent = alertHtml;
                            } else if (tableHtml) {
                                newContent = tableHtml;
                            }
                        }

                        if (newContent) {
                            $('#products-container').html(newContent);
                            
                            // Re-initialize DataTable if table exists
                            if ($("#table-1").length) {
                                $("#table-1").dataTable().fnDestroy();
                                $("#table-1").dataTable({
                                    "order": [[4, "asc"]],
                                    paging: false,
                                    info: false,
                                    searching: true,
                                    "language": {
                                        "search": "Filter:",
                                        "searchPlaceholder": "Search in table..."
                                    }
                                });
                            }

                            // Re-bind checkbox events
                            $('#select_all').off('change').on('change', function() {
                                $('.product-checkbox').prop('checked', $(this).is(':checked'));
                                updateSelectedProducts();
                            });

                            $('.product-checkbox').off('change').on('change', function() {
                                updateSelectedProducts();
                                $('#select_all').prop('checked', $('.product-checkbox:checked').length === $('.product-checkbox').length);
                            });

                            // Update basket UI after content loads
                            setTimeout(function() {
                                updateBasketUI();
                            }, 100);
                        }

                        // Update URL without reload
                        window.history.pushState({path: url}, '', url);

                        $('#products-container').css('opacity', '1');
                        $('#search-spinner').addClass('d-none');
                    },
                    error: function(xhr) {
                        console.error('Search error:', xhr);
                        $('#search-spinner').addClass('d-none');
                        $('#products-container').css('opacity', '1');
                        toastr.error('Error performing search. Please try again.');
                    }
                });
            }

            // --- Basket Logic Start (Database Cart System) ---
            
            /**
             * Update basket UI with counts and button states
             */
            function updateBasketUI() {
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

                // Update button states
                $.ajax({
                    url: "{{ route('admin.cart.items') }}?cart_type=booking",
                    method: 'GET',
                    success: function(data) {
                        const bookingIds = data.product_ids;
                        $('.add-to-basket').each(function() {
                            const id = $(this).data('id').toString();
                            if (bookingIds.includes(parseInt(id))) {
                                $(this).addClass('added').html('<i class="fas fa-check"></i>');
                            } else {
                                $(this).removeClass('added').html('<i class="fas fa-shopping-basket"></i>');
                            }
                        });
                    }
                });
            }

            // Initial UI Update on page load
            updateBasketUI();

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
                        if (response.success) {
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

            // NOTE: Removed global ajaxComplete handler that was causing infinite loop
            // The updateBasketUI() is already called explicitly after cart operations
            // $(document).ajaxComplete(function() {
            //     updateBasketUI();
            // });
            // --- Basket Logic End ---

            // Keep existing checkbox functionality for bulk actions
            let selectedProducts = [];

            // Select All checkbox
            $('#select_all').on('change', function() {
                $('.product-checkbox').prop('checked', $(this).is(':checked'));
                updateSelectedProducts();
            });

            // Individual checkbox change
            $(document).on('change', '.product-checkbox', function() {
                updateSelectedProducts();
                $('#select_all').prop('checked', $('.product-checkbox:checked').length === $('.product-checkbox').length);
            });

            function updateSelectedProducts() {
                selectedProducts = [];
                $('.product-checkbox:checked').each(function() {
                    selectedProducts.push($(this).val());
                });

                const count = selectedProducts.length;
                $('#selected_count').text(count);

                if (count > 0) {
                    $('#add_to_booking_btn').show();
                } else {
                    $('#add_to_booking_btn').hide();
                }
            }

            // Add to Booking (bulk)
            $('#add_to_booking_btn').on('click', function() {
                if (selectedProducts.length === 0) {
                    toastr.warning('Please select at least one product');
                    return;
                }
                // Add all selected to localStorage basket
                selectedProducts.forEach(id => {
                    if (booking_basket.indexOf(id.toString()) === -1) {
                        booking_basket.push(id.toString());
                    }
                });
                localStorage.setItem('booking_basket', JSON.stringify(booking_basket));
                updateBasketUI();
                window.location.href = "{{ route('admin.bookings.create') }}?ids=" + selectedProducts.join(',');
            });
        });
    </script>
@endpush
