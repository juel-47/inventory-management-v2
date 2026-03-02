<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    /**
     * Display the frontend home page (empty products as requested).
     */
    public function index(Request $request)
    {
        $roleContext = $this->resolveFrontendRoleContext($request);
        $isOutletCustomer = $roleContext['isOutletCustomer'];
        $outletId = $roleContext['outletId'] ?? $this->resolveRequestOutletId($request);

        $categories = Category::with(['subCategories' => function($q) {
                $q->where('status', 1);
            }, 'subCategories.childCategories' => function($q) {
                $q->where('status', 1);
            }])
            ->withCount([
                'products as active_products_count' => function ($query) {
                    $query->where('status', 1);
                }
            ])
            ->where('status', 1)
            ->orderBy('name')
            ->paginate(12, ['*'], 'category_page')
            ->withQueryString();

        $featuredQuery = Product::query()
            ->with([
                'category:id,name',
                'variants' => function ($query) use ($roleContext) {
                    $this->configureVariantQuery($query, $roleContext);
                },
            ])
            ->where('status', 1)
            ->whereHas('category', function ($query) {
                $query->where('status', 1);
            });

        if ($isOutletCustomer) {
            $featuredQuery->withSum([
                'inventoryStocks as scoped_stock_qty' => function ($query) use ($outletId) {
                    $query->where('outlet_id', $outletId);
                }
            ], 'quantity');
        }

        $featuredProducts = $featuredQuery->latest()->take(12)->get();
        $featuredCards = $featuredProducts
            ->map(fn (Product $product) => $this->transformProductForCard($product, $roleContext))
            ->values();

        $activeProductCount = Product::query()
            ->where('status', 1)
            ->whereHas('category', function ($query) {
                $query->where('status', 1);
            })
            ->count();

        $activeBrandCount = Brand::query()
            ->where('status', 1)
            ->count();

        $inStockProductCount = Product::query()
            ->where('status', 1)
            ->whereHas('category', function ($query) {
                $query->where('status', 1);
            })
            ->whereHas('inventoryStocks', function ($query) use ($outletId, $isOutletCustomer) {
                if ($isOutletCustomer) {
                    $query->where('outlet_id', $outletId);
                }
                $query->where('quantity', '>', 0);
            })
            ->count();

        $topBrands = Brand::query()
            ->where('status', 1)
            ->withCount([
                'products as active_products_count' => function ($query) {
                    $query->where('status', 1);
                }
            ])
            ->orderByDesc('active_products_count')
            ->orderBy('name')
            ->take(8)
            ->get();

        $inStockFeaturedCount = $isOutletCustomer
            ? $featuredProducts->filter(fn ($product) => (int) ($product->scoped_stock_qty ?? 0) > 0)->count()
            : 0;

        return view('frontend.pages.home', [
            'categories' => $categories,
            'featuredCards' => $featuredCards,
            'roleContext' => $roleContext,
            'isOutletCustomer' => $isOutletCustomer,
            'outletId' => $outletId,
            'inStockFeaturedCount' => $inStockFeaturedCount,
            'activeProductCount' => $activeProductCount,
            'activeBrandCount' => $activeBrandCount,
            'inStockProductCount' => $inStockProductCount,
            'topBrands' => $topBrands,
        ]);
    }

    /**
     * Display the dynamic shop page with filtering and sorting.
     */
    public function shop(Request $request)
    {
        $roleContext = $this->resolveFrontendRoleContext($request);
        $isOutletCustomer = $roleContext['isOutletCustomer'];
        $outletId = $roleContext['outletId'] ?? $this->resolveRequestOutletId($request);

        $query = Product::query()
            ->with([
                'category:id,name',
                'variants' => function ($query) use ($roleContext) {
                    $this->configureVariantQuery($query, $roleContext);
                },
            ])
            ->where('status', 1)
            ->whereHas('category', function($q) {
                $q->where('status', 1);
            });

        if ($isOutletCustomer) {
            $query->withSum([
                'inventoryStocks as scoped_stock_qty' => function ($query) use ($outletId) {
                    $query->where('outlet_id', $outletId);
                }
            ], 'quantity');
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('product_number', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Category Filters
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        if ($request->filled('subcategory')) {
            $query->where('sub_category_id', $request->subcategory);
        }
        if ($request->filled('childcategory')) {
            $query->where('child_category_id', $request->childcategory);
        }

        // Price Filters
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Sorting
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'stock_first':
                if ($isOutletCustomer) {
                    $query->orderByRaw('CASE WHEN COALESCE(scoped_stock_qty, 0) > 0 THEN 0 ELSE 1 END')
                        ->orderByDesc('scoped_stock_qty')
                        ->latest();
                } else {
                    $query->withSum('inventoryStocks as total_stock_qty', 'quantity')
                        ->orderByRaw('CASE WHEN COALESCE(total_stock_qty, 0) > 0 THEN 0 ELSE 1 END')
                        ->orderByDesc('total_stock_qty')
                        ->latest();
                }
                break;
            case 'price_low_high':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high_low':
                $query->orderBy('price', 'desc');
                break;
            default:
                $query->latest();
                break;
        }

        $products = $query->paginate(24)->withQueryString();
        $shopCards = collect($products->items())
            ->map(fn (Product $product) => $this->transformProductForCard($product, $roleContext))
            ->values();
        
        $categories = Category::with(['subCategories' => function($q) {
                $q->where('status', 1);
            }, 'subCategories.childCategories' => function($q) {
                $q->where('status', 1);
            }])
            ->where('status', 1)
            ->get();

        // Get absolute price range for slider
        $min_range = Product::where('status', 1)
            ->whereHas('category', function($q) { $q->where('status', 1); })
            ->min('price') ?? 0;
        $max_range = Product::where('status', 1)
            ->whereHas('category', function($q) { $q->where('status', 1); })
            ->max('price') ?? 1000;

        return view('frontend.pages.shop', [
            'products' => $products,
            'shopCards' => $shopCards,
            'categories' => $categories,
            'min_range' => $min_range,
            'max_range' => $max_range,
            'isOutletCustomer' => $isOutletCustomer,
            'outletId' => $outletId,
            'roleContext' => $roleContext,
        ]);
    }

    /**
     * Live product search for navbar quick search dropdown.
     */
    public function liveSearch(Request $request)
    {
        $query = trim((string) $request->query('q', ''));
        if (mb_strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $roleContext = $this->resolveFrontendRoleContext($request);
        $canSeeWholesalePrice = (bool) data_get($roleContext, 'isOutletUser', false)
            || (bool) data_get($roleContext, 'isStandardUser', false);

        $products = Product::query()
            ->select(['id', 'name', 'slug', 'thumb_image', 'price', 'outlet_price', 'product_number', 'sku', 'category_id'])
            ->with('category:id,name')
            ->where('status', 1)
            ->whereHas('category', function ($query) {
                $query->where('status', 1);
            })
            ->where(function ($builder) use ($query) {
                $builder->where('name', 'like', "%{$query}%")
                    ->orWhere('product_number', 'like', "%{$query}%")
                    ->orWhere('sku', 'like', "%{$query}%");
            })
            ->latest('id')
            ->limit(8)
            ->get();

        $results = $products->map(function (Product $product) use ($canSeeWholesalePrice): array {
            $image = $this->resolveImageUrl((string) ($product->thumb_image ?? ''));
            $displayPrice = $canSeeWholesalePrice
                ? (float) (($product->outlet_price ?? 0) > 0 ? $product->outlet_price : $product->price)
                : (float) ($product->price ?? 0);

            return [
                'id' => (int) $product->id,
                'name' => (string) ($product->name ?? ''),
                'category' => (string) ($product->category?->name ?? 'General'),
                'sku' => (string) ($product->sku ?? ''),
                'product_number' => (string) ($product->product_number ?? ''),
                'price' => round($displayPrice, 2),
                'url' => route('product.details', $product->slug),
                'image' => (string) ($image ?? ''),
            ];
        })->values();

        return response()->json(['results' => $results]);
    }

    /**
     * Display product details.
     */
    public function productDetails($slug)
    {
        $request = request();
        $roleContext = $this->resolveFrontendRoleContext($request);
        $canViewInventory = $roleContext['canViewInventory'];
        $outletId = $roleContext['outletId'] ?? $this->resolveRequestOutletId($request);

        $productQuery = Product::query()
            ->with([
                'category:id,name',
                'brand:id,name',
                'variants' => function ($query) use ($roleContext) {
                    $this->configureVariantQuery($query, $roleContext);
                },
            ])
            ->where('slug', $slug)
            ->where('status', 1);

        if ($canViewInventory) {
            $productQuery->withSum([
                'inventoryStocks as scoped_stock_qty' => function ($query) use ($outletId) {
                    $query->where('outlet_id', $outletId);
                },
            ], 'quantity');
        }

        $product = $productQuery->firstOrFail();

        $relatedQuery = Product::query()
            ->with([
                'category:id,name',
                'brand:id,name',
                'variants' => function ($query) use ($roleContext) {
                    $this->configureVariantQuery($query, $roleContext);
                },
            ])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 1);

        if ($canViewInventory) {
            $relatedQuery->withSum([
                'inventoryStocks as scoped_stock_qty' => function ($query) use ($outletId) {
                    $query->where('outlet_id', $outletId);
                },
            ], 'quantity');
        }

        $relatedProducts = $relatedQuery
            ->latest()
            ->take(5)
            ->get();

        $displayPath = $this->resolveImageUrl((string) ($product->thumb_image ?? ''));
        $productCategoryName = trim((string) optional($product->category)->name);
        $productBrandName = trim((string) optional($product->brand)->name);
        $productSku = trim((string) ($product->sku ?? ''));
        $productNumber = trim((string) ($product->product_number ?? ''));
        $summaryText = trim(strip_tags((string) ($product->short_description ?? $product->long_description ?? '')));
        $summaryText = $summaryText !== '' ? Str::limit($summaryText, 190) : null;
        $hasLongDescription = trim(strip_tags((string) ($product->long_description ?? ''))) !== '';
        $isWishlisted = $request->user()
            ? $request->user()->wishlist()->where('product_id', $product->id)->exists()
            : false;

        $detailProductData = [
            'id' => (int) $product->id,
            'slug' => (string) $product->slug,
            'name' => (string) $product->name,
            'thumb_image' => (string) ($displayPath ?? ''),
            'price' => (float) ($product->price ?? 0),
            'outlet_price' => (float) ($product->outlet_price ?? 0),
            'discount_type' => (string) ($product->discount_type ?? ''),
            'discount' => (float) ($product->discount ?? 0),
            'category' => $productCategoryName !== '' ? $productCategoryName : 'Category not set',
            'minimum_order_qty' => max(1, (int) ($product->minimum_order_qty ?? 1)),
            'stock' => $canViewInventory ? (int) ($product->scoped_stock_qty ?? 0) : 0,
            'inventory_visible' => $canViewInventory,
        ];

        $detailVariantData = $product->variants
            ->map(fn ($variant) => $this->mapVariantForCard($variant, $product, $canViewInventory))
            ->values();

        $relatedCards = $relatedProducts
            ->map(fn (Product $relatedProduct) => $this->transformProductForCard($relatedProduct, $roleContext))
            ->values();

        return view('frontend.pages.products.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'relatedCards' => $relatedCards,
            'roleContext' => $roleContext,
            'displayPath' => $displayPath,
            'productCategoryName' => $productCategoryName,
            'productBrandName' => $productBrandName,
            'productSku' => $productSku,
            'productNumber' => $productNumber,
            'summaryText' => $summaryText,
            'hasLongDescription' => $hasLongDescription,
            'detailProductData' => $detailProductData,
            'detailVariantData' => $detailVariantData,
            'isWishlisted' => $isWishlisted,
        ]);
    }

    private function resolveFrontendRoleContext(Request $request): array
    {
        $user = $request->user();
        $roleNames = $this->resolveRoleNames($user);
        $isOutletUser = in_array('outlet user', $roleNames, true);
        $isStandardUser = in_array('user', $roleNames, true);
        $isOutletCustomer = $isOutletUser || $isStandardUser;

        return [
            'roleNames' => $roleNames,
            'isOutletUser' => $isOutletUser,
            'isStandardUser' => $isStandardUser,
            'isOutletCustomer' => $isOutletCustomer,
            'canViewInventory' => $isOutletCustomer,
            'outletId' => $isOutletCustomer ? $this->resolveRequestOutletId($request) : null,
        ];
    }

    private function resolveRoleNames($user): array
    {
        if (!$user) {
            return [];
        }

        $roleNames = $user->roles->pluck('name')
            ->map(fn ($name) => strtolower((string) $name))
            ->filter()
            ->values()
            ->all();

        $roleFromColumn = strtolower((string) optional($user->userRole)->name);
        if ($roleFromColumn !== '' && !in_array($roleFromColumn, $roleNames, true)) {
            $roleNames[] = $roleFromColumn;
        }

        return $roleNames;
    }

    private function resolveRequestOutletId(Request $request): int
    {
        $userOutletId = $request->user()?->outlet_id ?? null;
        if (!empty($userOutletId)) {
            return (int) $userOutletId;
        }

        return 1;
    }

    private function configureVariantQuery($query, array $roleContext): void
    {
        $query
            ->where('status', 1)
            ->with(['color:id,name', 'size:id,name']);

        if (!empty($roleContext['canViewInventory']) && !empty($roleContext['outletId'])) {
            $outletId = (int) $roleContext['outletId'];
            $query->withSum([
                'inventoryStocks as scoped_stock_qty' => function ($stockQuery) use ($outletId) {
                    $stockQuery->where('outlet_id', $outletId);
                },
            ], 'quantity');
        }
    }

    private function transformProductForCard(Product $product, array $roleContext): array
    {
        $canViewInventory = (bool) ($roleContext['canViewInventory'] ?? false);
        $displayPath = $this->resolveImageUrl((string) ($product->thumb_image ?? ''));
        $categoryName = trim((string) optional($product->category)->name);

        $productPayload = [
            'id' => (int) $product->id,
            'slug' => (string) $product->slug,
            'name' => (string) $product->name,
            'thumb_image' => (string) ($displayPath ?? ''),
            'price' => (float) ($product->price ?? 0),
            'outlet_price' => (float) ($product->outlet_price ?? 0),
            'discount_type' => (string) ($product->discount_type ?? ''),
            'discount' => (float) ($product->discount ?? 0),
            'category' => $categoryName !== '' ? $categoryName : 'Category not set',
            'minimum_order_qty' => max(1, (int) ($product->minimum_order_qty ?? 1)),
            'stock' => $canViewInventory ? (int) ($product->scoped_stock_qty ?? 0) : 0,
            'inventory_visible' => $canViewInventory,
        ];

        $variantPayload = $product->variants
            ->map(fn ($variant) => $this->mapVariantForCard($variant, $product, $canViewInventory))
            ->values();

        return [
            'product' => $productPayload,
            'variants' => $variantPayload,
            'display_path' => $displayPath,
            'category_name' => $categoryName !== '' ? $categoryName : 'Category not set',
            'details_url' => route('product.details', $product->slug),
        ];
    }

    private function mapVariantForCard($variant, Product $product, bool $canViewInventory): array
    {
        return [
            'id' => (int) $variant->id,
            'name' => (string) ($variant->name ?? ''),
            'price' => $variant->price > 0 ? (float) $variant->price : (float) ($product->price ?? 0),
            'outlet_price' => $variant->outlet_price > 0 ? (float) $variant->outlet_price : (float) ($product->outlet_price ?? 0),
            'color' => is_object($variant->color) ? (string) $variant->color->name : (string) ($variant->color ?? ''),
            'size' => is_object($variant->size) ? (string) $variant->size->name : (string) ($variant->size ?? ''),
            'stock' => $canViewInventory ? (int) ($variant->scoped_stock_qty ?? 0) : null,
        ];
    }

    private function resolveImageUrl(?string $path): ?string
    {
        $cleanPath = trim((string) $path);
        if ($cleanPath === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $cleanPath)) {
            return $cleanPath;
        }

        if (file_exists(public_path($cleanPath))) {
            return asset($cleanPath);
        }

        $storageRelative = ltrim($cleanPath, '/');
        if (file_exists(storage_path('app/public/' . $storageRelative))) {
            return asset('storage/' . $storageRelative);
        }

        return null;
    }
}
