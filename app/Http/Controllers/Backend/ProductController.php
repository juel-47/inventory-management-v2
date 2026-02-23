<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\ProductDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ProductCreateRequest;
use App\Http\Requests\Product\ProductUpdateRequest;
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
use App\Traits\ImageUploadTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\InventoryStock;
use App\Models\StockLedger;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductsImport;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProductController extends Controller implements HasMiddleware
{
    use ImageUploadTrait;

    public static function middleware(): array
    {
        return [
            new Middleware('role:Admin', except: ['index']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'variants.color', 'variants.size', 'inventoryStocks', 'vendor']);
        
        // Visibility Constraints for non-admins
        if (!Auth::user()->hasRole('Admin')) {
            $query->where('status', 1)
                  ->whereHas('category', function($q) {
                      $q->where('status', 1);
                  })
                  ->where(function($q) {
                      $q->whereNull('product_type_id')
                        ->orWhereHas('productType', function($sq) {
                            $sq->where('status', 1);
                        });
                  });
        }

        // Handle Sorting
        $sort = $request->sort ?? 'latest';
        if ($sort == 'z-a') {
            $query->orderBy('name', 'desc');
        } elseif ($sort == 'a-z') {
            $query->orderBy('name', 'asc');
        }elseif ($sort == 'active') {
            $query->where('status', 1)->latest();

        } elseif ($sort == 'inactive') {
            $query->where('status', 0)->latest();

        } 
        else {
            $query->latest();
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('product_number', 'like', "%{$search}%")
                    ->orWhere('self_number', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($subQ) use ($search) {
                        $subQ->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('category') && $request->category != '') {
            $query->where('category_id', $request->category);
        }

        if ($request->has('sub_category') && $request->sub_category != '') {
            $query->where('sub_category_id', $request->sub_category);
        }

        if ($request->has('child_category') && $request->child_category != '') {
            $query->where('child_category_id', $request->child_category);
        }

        if ($request->has('product_type') && $request->product_type != '') {
            $type = $request->product_type;
            if (is_numeric($type)) {
                $query->where('product_type_id', $type);
            } else {
                $query->where('product_type', $type);
            }
        }

        if ($request->has('alphabet') && $request->alphabet != '') {
            $query->where('name', 'like', $request->alphabet . '%');
        }

        if ($request->has('vendor') && $request->vendor != '') {
            $query->where('vendor_id', $request->vendor);
        }

        $products = $query->paginate(20)->withQueryString();
        $categories = Category::where('status', 1)->get();
        $productTypes = ProductType::where('status', 1)->get();
        $vendors = Vendor::where('status', 1)->get();
        if ($request->ajax()) {
            return view('backend.product.product_grid', compact('products'))->render();
        }

        return view('backend.product.index', compact('products', 'categories', 'productTypes', 'vendors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::where('status', 1)->get();
        $brands = Brand::where('status', 1)->get();
        $units = Unit::where('status', 1)->get();
        $vendors = Vendor::where('status', 1)->get();
        $colors = Color::where('status', 1)->get();
        $sizes = Size::where('status', 1)->get();
        $productTypes = ProductType::where('status', 1)->get();
        return view('backend.product.create', compact('categories', 'brands', 'units', 'vendors', 'colors', 'sizes', 'productTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $imagePath = $this->upload_image($request, 'image', 'uploads/products');

            $product = new Product();
            $product->thumb_image = $imagePath;
            $product->name = $request->name;
            $product->slug = Str::slug($request->name);
            $product->category_id = $request->category_id;
            $product->sub_category_id = $request->sub_category_id;
            $product->child_category_id = $request->child_category_id;
            $product->brand_id = $request->brand_id;
            $product->vendor_id = $request->vendor_id;
            $product->unit_id = $request->unit_id;
            $product->product_number = $request->product_number;
            $product->long_description = $request->long_description;
            $product->purchase_price = $request->purchase_price ?? 0;
            $product->price = $request->price ?? 0;
            $product->outlet_price = $request->outlet_price ?? 0;
            $product->barcode = $request->barcode;
            $product->status = $request->status;
            $product->product_type = $request->product_type;
            $product->product_type_id = $request->product_type_id;
            $product->custom_label = $request->custom_label;
            $product->self_number = $request->self_number;
            $product->raw_material_cost = $request->raw_material_cost;
            $product->transport_cost = $request->transport_cost;
            $product->tax = $request->tax;
            
            // Set qty for backward compatibility if needed, but we reflect in InventoryStock
            $product->qty = $request->qty ?? 0;
            $product->save();

            // Handle Product Opening Stock
            if ($product->qty > 0 && !$request->has('variants')) {
                $stock = InventoryStock::firstOrCreate([
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'outlet_id' => 1 // Default
                ]);
                $stock->increment('quantity', $product->qty);

                StockLedger::create([
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'outlet_id' => 1,
                    'reference_type' => 'opening',
                    'reference_id' => $product->id,
                    'in_qty' => $product->qty,
                    'out_qty' => 0,
                    'balance_qty' => $stock->quantity,
                    'date' => date('Y-m-d')
                ]);
            }

            // Handle Variants
            if ($request->has('variants')) {
                foreach ($request->variants as $variant) {
                    if (!empty($variant['color_id']) || !empty($variant['size_id'])) {
                        $productVariant = new ProductVariant();
                        $productVariant->product_id = $product->id;
                        $productVariant->color_id = $variant['color_id'] ?? null;
                        $productVariant->size_id = $variant['size_id'] ?? null;
                        $productVariant->qty = $variant['qty'] ?? 0;

                        // Generate name for backward compatibility
                        $colorName = $productVariant->color_id ? Color::find($productVariant->color_id)->name : '';
                        $sizeName = $productVariant->size_id ? Size::find($productVariant->size_id)->name : '';
                        $productVariant->name = trim($colorName . ' ' . $sizeName);
                        
                        // Save variant prices
                        $productVariant->price = $variant['price'] ?? 0;
                        $productVariant->outlet_price = $variant['outlet_price'] ?? 0;

                        $productVariant->save();

                        // Variant Opening Stock
                        if ($productVariant->qty > 0) {
                            $stock = InventoryStock::firstOrCreate([
                                'product_id' => $product->id,
                                'variant_id' => $productVariant->id,
                                'outlet_id' => 1 // Default
                            ]);
                            $stock->increment('quantity', $productVariant->qty);

                            StockLedger::create([
                                'product_id' => $product->id,
                                'variant_id' => $productVariant->id,
                                'outlet_id' => 1,
                                'reference_type' => 'opening',
                                'reference_id' => $productVariant->id,
                                'in_qty' => $productVariant->qty,
                                'out_qty' => 0,
                                'balance_qty' => $stock->quantity,
                                'date' => date('Y-m-d')
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            Toastr::success('Product Created Successfully!');
            return redirect()->route('admin.products.index');

        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('Error: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::where('status', 1)->get();
        $subCategories = SubCategory::where('category_id', $product->category_id)->get();
        $childCategories = ChildCategory::where('sub_category_id', $product->sub_category_id)->get();
        $brands = Brand::where('status', 1)->get();
        $units = Unit::where('status', 1)->get();
        $vendors = Vendor::where('status', 1)->get();
        $colors = Color::where('status', 1)->get();
        $sizes = Size::where('status', 1)->get();
        $productTypes = ProductType::where('status', 1)->get();
        return view('backend.product.edit', compact('product', 'categories', 'subCategories', 'childCategories', 'brands', 'units', 'vendors', 'colors', 'sizes', 'productTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductUpdateRequest $request, string $id)
    {
        DB::beginTransaction();
        try {
            $product = Product::findOrFail($id);
            $imagePath = $this->update_image($request, 'image', 'uploads/products', $product->thumb_image);

            if ($request->hasFile('image')) {
                $product->thumb_image = $imagePath;
            }

            $product->name = $request->name;
            $product->slug = Str::slug($request->name);
            $product->category_id = $request->category_id;
            $product->sub_category_id = $request->sub_category_id;
            $product->child_category_id = $request->child_category_id;
            $product->brand_id = $request->brand_id;
            $product->vendor_id = $request->vendor_id;
            $product->unit_id = $request->unit_id;
            $product->product_number = $request->product_number;
            $product->long_description = $request->long_description;
            $product->purchase_price = $request->purchase_price ?? 0;
            $product->price = $request->price ?? 0;
            $product->outlet_price = $request->outlet_price ?? 0;
            $product->barcode = $request->barcode;
            $product->status = $request->status;
            $product->product_type = $request->product_type;
            $product->product_type_id = $request->product_type_id;
            $product->custom_label = $request->custom_label;
            $product->self_number = $request->self_number;
            $product->raw_material_cost = $request->raw_material_cost;
            $product->transport_cost = $request->transport_cost;
            $product->tax = $request->tax;
            $product->save();

            // Handle Product Manual Stock Adjustment
            $adjustment = 0;
            if ($request->has('current_stock')) {
                $currentDbStock = $product->inventory_stock;
                $submittedStock = (float)$request->current_stock;
                
                if ($submittedStock != $currentDbStock) {
                    $adjustment = $submittedStock - $currentDbStock;
                }
            }
            
            if ($adjustment != 0) {
                $stock = InventoryStock::firstOrCreate([
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'outlet_id' => 1
                ]);
                $stock->increment('quantity', $adjustment);

                StockLedger::create([
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'outlet_id' => 1,
                    'reference_type' => 'adjustment',
                    'reference_id' => $product->id,
                    'in_qty' => $adjustment > 0 ? $adjustment : 0,
                    'out_qty' => $adjustment < 0 ? abs($adjustment) : 0,
                    'balance_qty' => $stock->quantity,
                    'date' => date('Y-m-d')
                ]);
                
                $product->increment('qty', $adjustment);
            }

            // Non-destructive Variant Update
            $keepVariantIds = [];
            if ($request->has('variants')) {
                foreach ($request->variants as $vData) {
                    if (!empty($vData['color_id']) || !empty($vData['size_id'])) {
                        
                        $variant = null;
                        if (isset($vData['id'])) {
                            $variant = ProductVariant::find($vData['id']);
                        }

                        if (!$variant) {
                            $variant = new ProductVariant();
                            $variant->product_id = $product->id;
                        }

                        $variant->color_id = $vData['color_id'] ?? null;
                        $variant->size_id = $vData['size_id'] ?? null;
                        
                        // Generate name
                        $colorName = $variant->color_id ? Color::find($variant->color_id)->name : '';
                        $sizeName = $variant->size_id ? Size::find($variant->size_id)->name : '';
                        $variant->name = trim($colorName . ' ' . $sizeName);
                        
                        // Save variant prices
                        $variant->price = $vData['price'] ?? 0;
                        $variant->outlet_price = $vData['outlet_price'] ?? 0;
                        
                        $variant->save();
                        
                        $keepVariantIds[] = $variant->id;

                        // Variant Manual Stock Adjustment
                        $vAdjustment = 0;
                        $vCurrentDbStock = $variant->inventory_stock ?? 0; // New variant starts at 0

                        if (isset($vData['current_stock'])) {
                             $vSubmittedVal = (float)$vData['current_stock'];
                             if ($vSubmittedVal != $vCurrentDbStock) {
                                  $vAdjustment = $vSubmittedVal - $vCurrentDbStock;
                             }
                        }
                        
                        if ($vAdjustment != 0) {
                            $vStock = InventoryStock::firstOrCreate([
                                'product_id' => $product->id,
                                'variant_id' => $variant->id,
                                'outlet_id' => 1
                            ]);
                            $vStock->increment('quantity', $vAdjustment);

                            StockLedger::create([
                                'product_id' => $product->id,
                                'variant_id' => $variant->id,
                                'outlet_id' => 1,
                                'reference_type' => 'adjustment',
                                'reference_id' => $variant->id,
                                'in_qty' => $vAdjustment > 0 ? $vAdjustment : 0,
                                'out_qty' => $vAdjustment < 0 ? abs($vAdjustment) : 0,
                                'balance_qty' => $vStock->quantity,
                                'date' => date('Y-m-d')
                            ]);
                            
                            $variant->increment('qty', $vAdjustment);
                        }
                    }
                }
            }

            // Delete variants not in request
            ProductVariant::where('product_id', $product->id)->whereNotIn('id', $keepVariantIds)->delete();

            DB::commit();
            Toastr::success('Product Updated Successfully!');
            
            if ($request->has('return_url')) {
                return redirect($request->return_url);
            }
            
            return redirect()->route('admin.products.index');

        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('Error: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);

        // Allowing deletion even if related records exist because the database has cascade delete enabled.
        // This allows users to recover from mistakes like duplication.
        /*
        if ($product->purchaseDetails()->count() > 0 || $product->bookings()->count() > 0 || $product->productRequestItems()->count() > 0) {
            return response(['status' => 'error', 'message' => 'Cannot delete product! It has related bookings, purchases, or requests.']);
        }
        */

        $this->delete_image($product->thumb_image);
        $product->delete(); // Cascade delete variants
        return response(['status' => 'success', 'message' => 'Deleted Successfully!']);
    }

    public function changeStatus(Request $request)
    {
        // dd($request->all());
        $product = Product::findOrFail($request->id);
        $product->status = $request->status == 'true' ? 1 : 0;
        $product->save();

        return response(['status' => 'success', 'message' => 'Status Updated Successfully!']);
    }

    /**
     * Display the import form
     */
    public function importView()
    {
        return view('backend.product.import');
    }

    /**
     * Preview the uploaded file
     */
    public function importPreview(Request $request)
    {
        // Log::info('ProductController@importPreview hit');
        // Log::info('Request data: ' . json_encode($request->except('import_file')));
        
        $request->validate([
            'import_file' => 'required|mimes:csv,xlsx,xls|max:204800'
        ]);

        try {
            $file = $request->file('import_file');
            $originalName = $file->getClientOriginalName();
            
            // Save file temporarily in public storage so it can be accessed in next step
            $tempName = 'temp_import_' . time() . '_' . $originalName;
            $path = $file->storeAs('temp', $tempName, 'public');
            $fullPath = Storage::disk('public')->path($path);

            $importer = new ProductsImport();
            $preview = $importer->getPreviewData($fullPath, $originalName);

            return response()->json([
                'success' => true,
                'preview' => $preview,
                'temp_path' => $path,
                'original_name' => $originalName
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Handle the Excel import
     */
    public function importStore(Request $request)
    {
        // Log::info('ProductController@importStore hit');
        // Log::info('Request data: ' . json_encode($request->except('import_file')));

        // Support both direct file upload and temp_path from preview
        if ($request->has('temp_path')) {
            $request->validate([
                'temp_path' => 'required',
                'original_name' => 'required'
            ]);
            $tempPath = $request->temp_path;
            $originalName = $request->original_name;
            $fullPath = Storage::disk('public')->path($tempPath);
        } else {
            $request->validate([
                'import_file' => 'required|mimes:csv,xlsx,xls|max:204800'
            ], [
                'import_file.required' => 'Please upload a file',
                'import_file.mimes' => 'Only CSV, xlsx, and xls files are allowed',
                'import_file.max' => 'File size must be less than 200MB'
            ]);
            $file = $request->file('import_file');
            $fullPath = $file->getRealPath();
            $originalName = $file->getClientOriginalName();
            $tempPath = null;
        }

        try {
            // Debug info
            // Log::info('File processing: ' . $originalName);
            // Log::info('Path: ' . $fullPath);
            
            if (!file_exists($fullPath)) {
                throw new \Exception('Could not access file');
            }
            
            // Import using CSV/Excel processor
            $importer = new ProductsImport();
            $results = $importer->import($fullPath, $originalName);
            
            // Delete temp file if it exists
            if ($tempPath) {
                Storage::disk('public')->delete($tempPath);
            }
            
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
}
