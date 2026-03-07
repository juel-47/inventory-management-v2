<div class="row">
    @foreach ($products as $key => $product)
    <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
        <div class="card h-100 border shadow-sm rounded-lg overflow-hidden product-card hover-shadow transition-all" style="border-color: #e3e6f0; background-color: #fdfdfd;">
            <div class="position-relative bg-white d-flex align-items-center justify-content-center" style="height: 220px;">
                <img alt="{{ $product->name }}" 
                        src="{{ $product->thumb_image ? asset('storage/'.$product->thumb_image) : asset('uploads/default.jpg') }}" 
                        class="img-fluid" 
                        loading="{{ $key < 4 ? 'eager' : 'lazy' }}"
                        style="max-height: 100%; max-width: 100%; object-fit: contain;">

                <!-- Product Type Badges -->
                <div class="position-absolute d-flex flex-column" style="top: 10px; left: 10px; z-index: 11; gap: 4px;">
                    {{-- Custom Label (Admin defined) --}}
                    @if(Auth::user()->hasRole('Admin'))
                        @if($product->custom_label)
                            <span class="badge badge-info shadow-sm px-2 py-1 text-uppercase" style="font-size: 9px; font-weight: 700; border-radius: 8px; letter-spacing: 0.5px;">
                                {{ $product->custom_label }}
                            </span>
                        @endif
                    @endif

                    {{-- Dynamic Type --}}
                    @if($product->productType)
                        @php
                            $badgeClass = 'badge-info';
                            $textStyle = '';
                            if($product->productType->slug == 'new-arrival'){
                                $badgeClass = 'badge-success';
                            } elseif($product->productType->slug == 'upcoming'){
                                $badgeClass = 'badge-warning';
                                $textStyle = 'color: #000;';
                            }
                        @endphp
                        <span class="badge {{ $badgeClass }} shadow-sm px-2 py-1 text-uppercase" style="font-size: 9px; font-weight: 700; border-radius: 8px; letter-spacing: 0.5px; {{ $textStyle }}">
                            {{ $product->productType->name }}
                        </span>
                    @endif

                    {{-- Legacy Product Type --}}
                    @if($product->product_type)
                        <span class="badge {{ $product->product_type == 'new_arrival' ? 'badge-success' : ($product->product_type == 'upcoming' ? 'badge-warning' : 'badge-primary') }} shadow-sm px-2 py-1 text-uppercase" style="font-size: 9px; font-weight: 700; border-radius: 8px; letter-spacing: 0.5px; {{ $product->product_type == 'upcoming' ? 'color: #000;' : '' }}">
                            {{ ucfirst(str_replace('_', ' ', $product->product_type)) }} (Legacy)
                        </span>
                    @endif
                </div>
                
                <!-- Checkbox / Status Badge -->
                <div class="position-absolute d-flex flex-column align-items-end" style="top: 10px; right: 10px; z-index: 10;">
                    @if(Auth::user()->can('Manage Products'))
                        <label class="custom-switch m-0">
                            <input type="checkbox" name="custom-switch-checkbox" data-id="{{ $product->id }}" class="custom-switch-input change-status" {{ $product->status ? 'checked' : '' }}>
                            <span class="custom-switch-indicator shadow-sm"></span>
                        </label>
                        <span class="status-message badge badge-success shadow-sm mt-1" style="display: none; font-size: 10px; opacity: 0.9;">Saved</span>
                    @else
                        <span class="badge {{ $product->status ? 'badge-success' : 'badge-danger' }} shadow-sm px-2 py-1">{{ $product->status ? 'Active' : 'Inactive' }}</span>
                    @endif
                </div>
            </div>

            <div class="card-body p-3 d-flex flex-column">
                <div class="mb-2 d-flex justify-content-between align-items-center">
                    <span class="badge badge-pill badge-light text-muted" style="font-size: 10px; padding: 5px 10px;">{{ $product->category->name ?? 'Uncategorized' }}</span>
                    <!-- Stock Badge -->
                    @php
                        $stock = $product->inventory_stock;
                        $badgeClass = $stock > 0 ? 'badge-info' : 'badge-danger';
                    @endphp
                    <span class="badge {{ $badgeClass }} badge-pill px-2 py-1" style="font-size: 10px;">Stock: {{ (float)$stock }}</span>
                </div>
                
                <h6 class="card-title text-dark font-weight-bold mb-2 text-truncate" title="{{ $product->name }}" style="font-size: 1rem;">
                    {{ $product->name }}
                </h6>

                <!-- Rating Section -->
                @php
                    $avgRating = 0;
                    $reviewCount = 0;
                    $isOutletUserRole = Auth::user()->hasRole(['Outlet User', 'User']);
                    
                    // Check if reviews table exists before querying
                    try {
                        $avgRating = round($product->reviews()->avg('rating') ?? 0, 1);
                        $reviewCount = $product->reviews()->count();
                    } catch (\Exception $e) {
                        // Reviews table doesn't exist, set defaults
                        $avgRating = 0;
                        $reviewCount = 0;
                    }
                @endphp
                <div class="mb-2 d-flex align-items-center justify-content-between" style="gap: 8px;">
                    <div class="d-flex align-items-center">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= round($avgRating))
                                <i class="fas fa-star text-warning" style="font-size: 12px;"></i>
                            @else
                                <i class="fas fa-star text-muted" style="font-size: 12px; opacity: 0.3;"></i>
                            @endif
                        @endfor
                    </div>
                    @if($reviewCount > 0)
                        <span class="badge badge-light border text-muted" style="font-size: 10px; padding: 4px 8px;">
                            {{ number_format($avgRating, 1) }} ({{ $reviewCount }})
                        </span>
                    @else
                        <span class="text-muted" style="font-size: 10px;">No ratings</span>
                    @endif
                    @if($isOutletUserRole)
                        <button type="button" class="btn btn-sm btn-warning py-0 px-2 add-rating-btn" data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}" title="Add/Edit Rating">
                            <i class="fas fa-pen-square" style="font-size: 11px;"></i>
                        </button>
                    @endif
                </div>

                <!-- Variants Info -->
                @if($product->variants->count() > 0)
                <div class="mb-3">
                    <div class="d-flex flex-wrap" style="gap: 4px;">
                        @foreach($product->variants->take(3) as $variant)
                            @php
                                $variantLabel = trim((string) ($variant->name ?? ''));
                                if ($variantLabel === '') {
                                    $variantLabel = trim(implode(' ', array_filter([
                                        is_object($variant->getRelation('color') ?? null) ? optional($variant->getRelation('color'))->name : null,
                                        is_object($variant->getRelation('size') ?? null) ? optional($variant->getRelation('size'))->name : null,
                                        $variant->color ?? null,
                                        $variant->size ?? null,
                                    ])));
                                }
                                $variantLabel = $variantLabel !== '' ? $variantLabel : ('Variant #' . $variant->id);
                            @endphp
                            <span class="badge badge-light border text-muted shadow-none py-1 px-2" style="font-size: 9px; font-weight: 500;">
                                {{ $variantLabel }}
                            </span>
                        @endforeach
                        @if($product->variants->count() > 3)
                            <span class="badge badge-light border text-muted shadow-none py-1 px-2" style="font-size: 9px;">+{{ $product->variants->count() - 3 }} more</span>
                        @endif
                    </div>
                </div>
                @else
                <div class="mb-3" style="min-height: 23px;">
                     <!-- Spacer for alignment if no variants -->
                </div>
                @endif

                <div class="mt-auto bg-white border rounded p-3 shadow-sm">
                    @php
                        $hasVariants = $product->variants->count() > 0;
                        $userCanManage = Auth::user()->can('Manage Products');
                        $isOutletUser = Auth::user()->hasRole(['Outlet User', 'User']);
                    @endphp

                    @if($hasVariants)
                        {{-- Show variant-wise prices --}}
                        <div class="variant-prices-scroll" style="max-height: 200px; overflow-y: auto;">
                            @foreach($product->variants as $v)
                            @if($userCanManage)
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                <span class="text-muted" style="font-size: 13px; font-weight: 500;">Purchase:</span>
                                <span class="font-weight-bold text-dark" style="font-size: 15px;">{{ formatConverted($product->purchase_price) }}</span>
                            </div>
                            @endif
                                @php
                                    $variantLabel = trim((string) ($v->name ?? ''));
                                    if ($variantLabel === '') {
                                        $variantLabel = trim(implode(' ', array_filter([
                                            is_object($v->getRelation('color') ?? null) ? optional($v->getRelation('color'))->name : null,
                                            is_object($v->getRelation('size') ?? null) ? optional($v->getRelation('size'))->name : null,
                                            $v->color ?? null,
                                            $v->size ?? null,
                                        ])));
                                    }
                                    $variantLabel = $variantLabel !== '' ? $variantLabel : ('Variant #' . $v->id);
                                @endphp
                                <div class="mb-3 pb-2 border-bottom">
                                    <div class="mb-1">
                                        <span class="badge badge-secondary" style="font-size: 10px;">{{ $variantLabel }}</span>
                                    </div>
                                    @if($userCanManage)
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="text-muted" style="font-size: 11px;">Wholesale:</span>
                                            <span class="font-weight-bold text-success" style="font-size: 13px;">
                                                {{ formatConverted($v->outlet_price > 0 ? $v->outlet_price : $product->outlet_price) }}
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-muted" style="font-size: 11px;">Outlet/Customer:</span>
                                            <span class="font-weight-bold text-primary" style="font-size: 13px;">
                                                {{ formatConverted($v->price > 0 ? $v->price : $product->price) }}
                                            </span>
                                        </div>
                                    @elseif($isOutletUser)
                                        {{-- <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="text-muted" style="font-size: 11px;">Buying:</span>
                                            <span class="font-weight-bold text-success" style="font-size: 13px;">
                                                {{ formatConverted($v->outlet_price > 0 ? $v->outlet_price : $product->outlet_price) }}
                                            </span>
                                        </div> --}}
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-muted" style="font-size: 11px;">Selling Price:</span>
                                            <span class="font-weight-bold text-primary" style="font-size: 13px;">
                                                {{ formatConverted($v->price > 0 ? $v->price : $product->price) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        {{-- Show product-level prices when no variants --}}
                        @if($userCanManage)
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                <span class="text-muted" style="font-size: 13px; font-weight: 500;">Purchase:</span>
                                <span class="font-weight-bold text-dark" style="font-size: 15px;">{{ formatConverted($product->purchase_price) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                <span class="text-muted" style="font-size: 13px; font-weight: 500;">Whole Sale Price:</span>
                                <span class="font-weight-bold text-success" style="font-size: 16px;">{{ formatConverted($product->outlet_price) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted" style="font-size: 13px; font-weight: 500;">Outlet/Customer Price:</span>
                                <span class="font-weight-bold text-primary" style="font-size: 15px;">{{ formatConverted($product->price) }}</span>
                            </div>
                        @elseif($isOutletUser)
                            {{-- <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                <span class="text-muted" style="font-size: 13px; font-weight: 500;">Buying Price:</span>
                                <span class="font-weight-bold text-success" style="font-size: 16px;">{{ formatConverted($product->outlet_price) }}</span>
                            </div> --}}
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted" style="font-size: 13px; font-weight: 500;">Selling Price:</span>
                                <span class="font-weight-bold text-primary" style="font-size: 15px;">{{ formatConverted($product->price) }}</span>
                            </div>
                        @endif
                    @endif
                    
                    @can('Manage Order Place')
                    <!-- Add to Basket Button -->
                    <button type="button" class="btn btn-outline-info btn-sm btn-block mt-3 add-to-basket" data-id="{{ $product->id }}">
                        <i class="fas fa-shopping-basket mr-1"></i> Add to Basket
                    </button>
 @endcan

                    @can('Create Product Requests')
                    <!-- Add to Request Basket Button -->
                    <button type="button" class="btn btn-outline-primary btn-sm btn-block mt-2 add-to-request-basket" data-id="{{ $product->id }}">
                        <i class="fas fa-file-import mr-1"></i> Add to Request Basket
                    </button>
 @endcan
                </div>
                
                @can('Manage Products')
                <div class="mt-3 row no-gutters">
                    <div class="col-6 pr-1">
                        <a href="{{ route('admin.products.edit', ['product' => $product->id] + request()->query()) }}" class="btn btn-outline-primary btn-sm btn-block rounded-pill"><i class="fas fa-edit mr-1"></i> Edit</a>
                    </div>
                    <div class="col-6 pl-1">
                        <a href="{{ route('admin.products.destroy', $product->id) }}" class="btn btn-outline-danger btn-sm btn-block rounded-pill delete-item"><i class="fas fa-trash mr-1"></i> Delete</a>
                    </div>
                </div>
                @endcan
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row mt-4 mb-5">
    <div class="col-12 text-center">
        <p class="text-muted mb-3" style="font-size: 14px; font-weight: 500;">
            Showing <span class="text-dark font-weight-bold">{{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }}</span> 
            of <span class="text-dark font-weight-bold">{{ $products->total() }}</span> products
        </p>
        <div class="d-flex justify-content-center flex-wrap custom-pagination">
            {{ $products->links() }}
        </div>
    </div>
</div>
