<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display the frontend home page (empty products as requested).
     */
    public function index(Request $request)
    {
        $categories = Category::with(['subCategories' => function($q) {
                $q->where('status', 1);
            }, 'subCategories.childCategories' => function($q) {
                $q->where('status', 1);
            }])
            ->where('status', 1)
            ->get();

        return view('frontend.pages.home', compact('categories'));
    }

    /**
     * Display the dynamic shop page with filtering and sorting.
     */
    public function shop(Request $request)
    {
        $query = Product::with(['category', 'subCategory', 'childCategory', 'variants.color', 'variants.size', 'inventoryStocks'])
            ->where('status', 1)
            ->whereHas('category', function($q) {
                $q->where('status', 1);
            });

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

        return view('frontend.pages.shop', compact('products', 'categories', 'min_range', 'max_range'));
    }

    /**
     * Display product details.
     */
    public function productDetails($slug)
    {
        $product = Product::with(['category', 'brand', 'variants.color', 'variants.size', 'inventoryStocks'])
            ->where('slug', $slug)
            ->where('status', 1)
            ->firstOrFail();

        return view('frontend.pages.products.show', compact('product'));
    }
}
