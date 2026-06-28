<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\ProductAnnouncementDataTable;
use App\DataTables\ProductDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ProductCreateRequest;
use App\Http\Requests\Product\ProductUpdateRequest;
use App\Http\Requests\Product\ProductImportPreviewRequest;
use App\Http\Requests\Product\ProductImportStoreRequest;
use App\Http\Requests\Product\ProductSendAnnouncementRequest;
use App\Models\Brand;
use App\Models\ProductType;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ChildCategory;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use App\Models\Unit;
use App\Models\Vendor;
use App\Services\ProductService;
use App\Traits\ImageUploadTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProductController extends Controller implements HasMiddleware
{
    use ImageUploadTrait;

    public function __construct(
        private ProductService $productService
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('role:Admin', except: ['index']),
        ];
    }

    public function index(Request $request)
    {
        $query = Product::with(['category', 'variants.color', 'variants.size', 'inventoryStocks', 'vendor']);

        if (!Auth::user()->hasRole('Admin')) {
            $query->active()
                  ->whereHas('category', fn ($q) => $q->active())
                  ->where(fn ($q) => $q->whereNull('product_type_id')
                    ->orWhereHas('productType', fn ($sq) => $sq->active()));
        }

        $sort = $request->sort ?? 'latest';
        match ($sort) {
            'z-a' => $query->orderBy('name', 'desc'),
            'a-z' => $query->orderBy('name', 'asc'),
            'active' => $query->active()->latest(),
            'inactive' => $query->where('status', 0)->latest(),
            default => $query->latest(),
        };

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('product_number', 'like', "%{$search}%")
                ->orWhere('self_number', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%")
                ->orWhereHas('category', fn ($sq) => $sq->where('name', 'like', "%{$search}%")));
        }

        foreach (['category', 'sub_category', 'child_category', 'vendor'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter . '_id', $request->$filter);
            }
        }

        if ($request->filled('product_type')) {
            $type = $request->product_type;
            $query->where(is_numeric($type) ? 'product_type_id' : 'product_type', $type);
        }

        if ($request->filled('alphabet')) {
            $query->where('name', 'like', $request->alphabet . '%');
        }

        $products = $query->paginate(20)->withQueryString();
        $categories = Category::active()->get();
        $productTypes = ProductType::active()->get();
        $vendors = Vendor::active()->get();

        if ($request->ajax()) {
            return view('backend.product.product_grid', compact('products'))->render();
        }

        return view('backend.product.index', compact('products', 'categories', 'productTypes', 'vendors'));
    }

    public function create()
    {
        $categories = Category::active()->get();
        $brands = Brand::active()->get();
        $units = Unit::active()->get();
        $vendors = Vendor::active()->get();
        $colors = Color::active()->get();
        $sizes = Size::active()->get();
        $productTypes = ProductType::active()->get();

        return view('backend.product.create', compact('categories', 'brands', 'units', 'vendors', 'colors', 'sizes', 'productTypes'));
    }

    public function store(ProductCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $variantRows = $this->productService->extractVariantRows($request->input('variants', []), false);
            $imagePath = $this->upload_image($request, 'image', 'uploads/products');
            $this->productService->createProduct($request->validated(), $variantRows, $imagePath);
            DB::commit();
            Toastr::success('Product Created Successfully!');
            return redirect()->route('admin.products.index');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('Error: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function show(string $id) {}

    public function edit(string $id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::active()->get();
        $subCategories = SubCategory::where('category_id', $product->category_id)->get();
        $childCategories = ChildCategory::where('sub_category_id', $product->sub_category_id)->get();
        $brands = Brand::active()->get();
        $units = Unit::active()->get();
        $vendors = Vendor::active()->get();
        $colors = Color::active()->get();
        $sizes = Size::active()->get();
        $productTypes = ProductType::active()->get();

        return view('backend.product.edit', compact('product', 'categories', 'subCategories', 'childCategories', 'brands', 'units', 'vendors', 'colors', 'sizes', 'productTypes'));
    }

    public function update(ProductUpdateRequest $request, string $id)
    {
        DB::beginTransaction();
        try {
            $variantRows = $this->productService->extractVariantRows($request->input('variants', []), true);
            $product = Product::findOrFail($id);
            $imagePath = $this->update_image($request, 'image', 'uploads/products', $product->thumb_image);
            $this->productService->updateProduct($id, $request->validated(), $variantRows, $request->hasFile('image') ? $imagePath : null);
            DB::commit();
            Toastr::success('Product Updated Successfully!');

            if ($request->has('return_url')) {
                return redirect($request->return_url);
            }

            return redirect()->route('admin.products.index');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('Error: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $this->delete_image($product->thumb_image);
        $product->delete();

        return response(['status' => 'success', 'message' => 'Deleted Successfully!']);
    }

    public function changeStatus(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->status = $request->status == 'true' ? 1 : 0;
        $product->save();

        return response(['status' => 'success', 'message' => 'Status Updated Successfully!']);
    }

    public function importView()
    {
        return view('backend.product.import');
    }

    public function importPreview(ProductImportPreviewRequest $request)
    {
        try {
            $result = $this->productService->previewImport($request->file('import_file'));
            return response()->json(array_merge(['success' => true], $result));
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function importStore(ProductImportStoreRequest $request)
    {
        if ($request->has('temp_path')) {
            $fullPath = Storage::disk('public')->path($request->temp_path);
            $originalName = $request->original_name;
            $tempPath = $request->temp_path;
        } else {
            $file = $request->file('import_file');
            $fullPath = $file->getRealPath();
            $originalName = $file->getClientOriginalName();
            $tempPath = null;
        }

        try {
            $results = $this->productService->storeImport($fullPath, $originalName, $tempPath);
            $message = 'Import completed! Success: ' . $results['success'] . ', Skipped: ' . ($results['skipped'] ?? 0) . ', Failed: ' . $results['failed'];

            if (!empty($results['errors'])) {
                $message .= ' Errors found in some rows.';
            }

            Toastr::success($message);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('admin.products.index')]);
            }
            return redirect()->route('admin.products.index');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            Toastr::error('Import failed: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function announcementIndex(ProductAnnouncementDataTable $dataTable)
    {
        $categories = Category::active()->orderBy('name')->get(['id', 'name']);
        $productTypes = ProductType::active()->orderBy('name')->get(['id', 'name']);
        $vendors = Vendor::active()->orderBy('shop_name')->get(['id', 'shop_name']);

        return $dataTable->render('backend.product.announcement', compact('categories', 'productTypes', 'vendors'));
    }

    public function sendAnnouncement(ProductSendAnnouncementRequest $request)
    {
        $validated = $request->validated();

        $productIds = collect($validated['product_ids'] ?? [])
            ->map(static fn ($id): int => (int) $id)
            ->filter(static fn ($id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();

        $result = $this->productService->sendAnnouncement(
            $productIds,
            $validated['subject'] ?? '',
            $validated['message'] ?? ''
        );

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }
}
