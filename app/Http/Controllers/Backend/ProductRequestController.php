<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\ProductRequestDataTable;
use App\Http\Controllers\Controller;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\ProductRequest;
use App\Models\ProductRequestItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Str;
use Illuminate\Routing\Controllers\HasMiddleware;


class ProductRequestController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            // No strict global middleware here because methods have internal checks
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index(ProductRequestDataTable $dataTable)
    {
        return $dataTable->render('backend.product-request.index');
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Only admin can create requests for outlet/users from backend
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('Admin')) {
             abort(403, 'Only admin can create product requests for outlets/users.');
        }

        $products = Product::where('status', 1)
            ->with(['variants.inventoryStocks', 'inventoryStocks'])
            ->get();
        
        $selectedIds = [];
        if ($request->has('ids')) {
            $selectedIds = explode(',', $request->ids);
        }

        $users = User::role(['Outlet User', 'User'])->where('status', 1)->orderBy('name', 'asc')->get();

        return view('backend.product-request.create', compact('products', 'users', 'selectedIds'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('Admin')) {
            abort(403);
        }

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.qty' => 'required|integer|min:1',
            'required_days' => 'nullable|integer|min:1',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $productRequest = new ProductRequest();
            $targetUser = User::role(['Outlet User', 'User'])
                ->where('status', 1)
                ->whereKey((int) $request->input('user_id'))
                ->first();

            if (!$targetUser) {
                throw new \InvalidArgumentException('Please select a valid active Outlet/User.');
            }

            $prefix = ($targetUser->hasRole('Outlet User') || $targetUser->hasRole('Outlet')) ? 'DS-REQ-' : 'REQ-';
            $productRequest->request_no = $prefix . strtoupper(Str::random(10));
            $productRequest->user_id = (int) $targetUser->id;
            $productRequest->status = 'approved';
            $productRequest->admin_note = 'Created by admin. Stock will be deducted only after Issue is created.';
            $productRequest->required_days = $request->required_days;
            $productRequest->note = $request->note;
            $productRequest->total_qty = 0; 
            $productRequest->save();

            $totalQty = 0;
            $totalAmount = 0;

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                // Price Hierarchy:
                // 1. Check variant outlet_price
                // 2. Check variant price
                // 3. Fallback to product outlet_price
                // 4. Fallback to product price
                $unitPrice = 0;
                
                if (!empty($item['variant_id'])) {
                    $variant = \App\Models\ProductVariant::find($item['variant_id']);
                    if ($variant) {
                        if ($variant->outlet_price > 0) {
                            $unitPrice = $variant->outlet_price;
                        } elseif ($variant->price > 0) {
                            $unitPrice = $variant->price;
                        }
                    }
                }
                
                // Fallback to product-level prices if variant price not set
                if ($unitPrice <= 0) {
                    $unitPrice = $product->outlet_price > 0 ? $product->outlet_price : $product->price;
                }

                $subtotal = $item['qty'] * $unitPrice;
                
                $totalQty += $item['qty'];
                $totalAmount += $subtotal;

                ProductRequestItem::create([
                    'product_request_id' => $productRequest->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'qty' => $item['qty'],
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);
            }

            $productRequest->total_qty = $totalQty;
            $productRequest->total_amount = $totalAmount;
            $productRequest->save();

            // Create Order record for Due Amount tracking
            // Use same number as request for clarity
            $orderNo = $productRequest->request_no;
            
            $order = \App\Models\Order::create([
                'order_no' => $orderNo,
                'user_id' => $targetUser->id,
                'status' => 'pending',
                'shipping_method' => 'admin_request',
                'billing_name' => $targetUser->name,
                'billing_email' => $targetUser->email,
                'billing_phone' => $targetUser->phone,
                'billing_address' => $targetUser->address,
                'billing_outlet_name' => $targetUser->outlet_name,
                'total_amount' => $totalAmount,
                'due_amount' => $totalAmount,
                'paid_amount' => 0,
                'payment_status' => 'pending',
                'placed_at' => now(),
            ]);


            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $unitPrice = 0;
                $variantLabel = null;
                if (!empty($item['variant_id'])) {
                    $variant = \App\Models\ProductVariant::with(['color', 'size'])->find($item['variant_id']);
                    if ($variant) {
                        $variantLabel = trim(($variant->color->name ?? '') . ' ' . ($variant->size->name ?? ''));
                        $unitPrice = ($variant->outlet_price > 0) ? $variant->outlet_price : $variant->price;
                    }
                }
                if ($unitPrice <= 0) {
                    $unitPrice = ($product->outlet_price > 0) ? $product->outlet_price : $product->price;
                }

                \App\Models\OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'variant_id' => $item['variant_id'] ?? null,
                    'vendor_id' => $product->vendor_id,
                    'product_name' => $product->name,
                    'category_name' => $product->category->name ?? null,
                    'variant_label' => $variantLabel,
                    'product_image' => $product->thumb_image,
                    'unit_price' => $unitPrice,
                    'quantity' => $item['qty'],
                    'line_total' => $item['qty'] * $unitPrice,
                ]);

            }

            // Link Order to Product Request
            $productRequest->order_id = $order->id;
            $productRequest->save();

            DB::commit();

            toastr()->success('Product Request and Order created successfully! Stock can be managed via Issue creation.');
            session()->flash('clear_request_basket', true);
            return redirect()->route('admin.product-requests.index');


        } catch (\Exception $e) {
            DB::rollBack();
            toastr()->error('Something went wrong: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $productRequest = ProductRequest::with(['user', 'order.payments', 'items.product', 'items.variant.color', 'items.variant.size'])->findOrFail($id);

        
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('Admin') && !$user->can('Manage Product Requests') && $productRequest->user_id != Auth::id()) {
            abort(403, 'Unauthorized access to this product request.');
        }

        foreach($productRequest->items as $item) {
             $stock = InventoryStock::where('product_id', $item->product_id)
                ->where('variant_id', $item->variant_id)
                ->where('outlet_id', 1)
                ->first();
             $item->current_stock = $stock ? $stock->quantity : 0;
        }

        return view('backend.product-request.show', compact('productRequest'));
    }

    /**
     * View Request Invoice (HTML)
     */
    public function viewInvoice($id)
    {
        $productRequest = ProductRequest::with(['user', 'items.product.unit', 'items.variant.color', 'items.variant.size'])->findOrFail($id);
        
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('Admin') && !$user->can('Manage Product Requests') && $productRequest->user_id != Auth::id()) {
            abort(403);
        }

        $settings = \App\Models\GeneralSetting::first();

        return view('backend.product-request.invoice', compact('productRequest', 'settings'));
    }

    /**
     * Generate Request PDF
     */
    public function printPdf($id)
    {
        // Increase resources for PDF generation to prevent 503 errors on live servers
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $productRequest = ProductRequest::with(['user', 'items.product.unit', 'items.variant.color', 'items.variant.size'])->findOrFail($id);
        
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('Admin') && !$user->can('Manage Product Requests') && $productRequest->user_id != Auth::id()) {
            abort(403);
        }

        $settings = \App\Models\GeneralSetting::first();

        // Configure DomPDF wrapper for better performance
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'sans-serif'
        ])->loadView('backend.product-request.print_pdf', compact('productRequest', 'settings'));

        $filename = 'request-' . $productRequest->request_no . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Update the status of the request.
     */
    /**
     * Update the status of the request.
     */
    public function updateStatus(Request $request, $id)
    {
        $productRequest = ProductRequest::findOrFail($id);
        
        // Only Admin/Manager can update status
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->can('Manage Product Requests')) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:pending,approved,rejected,shipped,completed',
            'admin_note' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $productRequest->update([
                'status' => $request->status,
                'admin_note' => $request->admin_note
            ]);

            // Sync Order status if linked
            if ($productRequest->order) {
                // Determine order status based on request status
                // Laravel Order statuses: pending, approved, processing, shipped, completed, cancelled, rejected
                $orderStatus = $request->status;
                $productRequest->order->update(['status' => $orderStatus]);
            }

            
            DB::commit();
            toastr()->success('Product Request updated successfully!');
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollBack();
            toastr()->error('Something went wrong: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $productRequest = ProductRequest::findOrFail($id);
        
        // Authorization: Manager can delete anything, User can only delete own pending requests
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->can('Manage Product Requests') && ($productRequest->user_id != Auth::id() || $productRequest->status !== 'pending')) {
             return response(['status' => 'error', 'message' => 'Unauthorized or request already processed']);
        }

        $productRequest->delete(); // Items deleted by cascade
        return response(['status' => 'success', 'message' => 'Deleted Successfully!']);
    }
}


