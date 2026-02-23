<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\GeneralSetting;
use App\Models\Product;
use App\Models\ProductRequest;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ReportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:Manage Reports'),
        ];
    }

    /**
     * Reports Dashboard
     */
    public function index()
    {
        // 1. Total Stock Value: Sum (Stock Qty * Purchase Price)
        // We join with inventory_stocks to get the actual quantity
        $totalStockValue = DB::table('inventory_stocks')
            ->join('products', 'inventory_stocks.product_id', '=', 'products.id')
            ->where('products.status', 1)
            ->sum(DB::raw('inventory_stocks.quantity * products.purchase_price'));
        
        $totalProducts = Product::where('status', 1)->count();
        
        $lowStockCount = Product::where('status', 1)
            ->withSum('inventoryStocks', 'quantity')
            ->havingRaw('inventory_stocks_sum_quantity <= 100 OR inventory_stocks_sum_quantity IS NULL')
            ->get()
            ->count();
        
        // 2. Total Revenue: From completed Product Requests
        $totalRevenue = ProductRequest::where('status', 'completed')->sum('total_amount');
        
        // 3. COGS: Estimated from ProductRequestItems for completed requests
        $totalCost = DB::table('product_request_items')
            ->join('product_requests', 'product_request_items.product_request_id', '=', 'product_requests.id')
            ->join('products', 'product_request_items.product_id', '=', 'products.id')
            ->where('product_requests.status', 'completed')
            ->sum(DB::raw('product_request_items.qty * products.purchase_price'));

        $grossProfit = $totalRevenue - $totalCost;

        // Current Month Purchases for Context
        $monthlyPurchases = Purchase::whereMonth('date', date('m'))
            ->whereYear('date', date('Y'))
            ->sum('total_amount');

        return view('backend.reports.index', compact(
            'totalStockValue',
            'totalProducts',
            'lowStockCount',
            'monthlyPurchases',
            'totalRevenue',
            'grossProfit'
        ));
    }

    /**
     * Stock Valuation Report
     */
    public function stockReport(Request $request)
    {
        $query = Product::with(['category', 'unit', 'brand', 'inventoryStocks'])
            ->where('status', 1);

        // Filters
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->brand_id) {
            $query->where('brand_id', $request->brand_id);
        }

        // Calculate Summary Stats from database aggregates BEFORE pagination
        $summaryData = (clone $query)->selectRaw('
            SUM((SELECT SUM(quantity) FROM inventory_stocks WHERE product_id = products.id)) as untyped_total_qty,
            SUM((SELECT SUM(quantity) FROM inventory_stocks WHERE product_id = products.id) * purchase_price) as untyped_total_value,
            SUM((SELECT SUM(quantity) FROM inventory_stocks WHERE product_id = products.id) * price) as untyped_potential_revenue
        ')->first();

        $totalQty = $summaryData->untyped_total_qty ?? 0;
        $totalValue = $summaryData->untyped_total_value ?? 0;
        $potentialRevenue = $summaryData->untyped_potential_revenue ?? 0;
        $potentialProfit = $potentialRevenue - $totalValue;

        $products = $query->paginate(30)->withQueryString();
        
        $categories = Category::where('status', 1)->get();
        $brands = Brand::where('status', 1)->get();
        $settings = GeneralSetting::first();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backend.reports.partials.stock_table_rows', compact('products', 'settings'))->render(),
                'pagination' => $products->links()->render(),
                'totalQty' => number_format($totalQty),
                'totalValue' => $settings->currency_icon . number_format($totalValue, 2),
                'potentialRevenue' => $settings->currency_icon . number_format($potentialRevenue, 2),
                'potentialProfit' => $settings->currency_icon . number_format($potentialProfit, 2),
            ]);
        }

        return view('backend.reports.stock', compact('products', 'categories', 'brands', 'totalQty', 'totalValue', 'potentialRevenue', 'potentialProfit', 'settings'));
    }

    /**
     * Purchase History Report
     */
    public function purchaseReport(Request $request)
    {
        $query = Purchase::with(['vendor', 'user', 'details']);

        // Date Range Filter
        if ($request->start_date) {
            $query->where('date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->where('date', '<=', $request->end_date);
        }

        // Vendor Filter
        if ($request->vendor_id) {
            $query->where('vendor_id', $request->vendor_id);
        }

        $purchases = $query->orderBy('date', 'desc')->paginate(30)->withQueryString();
        $vendors = Vendor::where('status', 1)->get();

        return view('backend.reports.purchase', compact('purchases', 'vendors'));
    }

    /**
     * Product-wise Purchase History (Track same product from different vendors)
     */
    public function productPurchaseHistory(Request $request)
    {
        $query = PurchaseDetail::with(['product', 'purchase.vendor', 'purchase.user']);

        // Product Filter
        if ($request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        $details = $query->orderBy('id', 'desc')->paginate(30)->withQueryString();
        $products = Product::where('status', 1)->get();

        return view('backend.reports.product_purchase_history', compact('details', 'products'));
    }

    /**
     * Low Stock Alert Report
     */
    public function lowStockReport(Request $request)
    {
        $query = Product::with(['category', 'unit'])
            ->withSum('inventoryStocks', 'quantity')
            ->where('status', 1)
            ->havingRaw('inventory_stocks_sum_quantity <= 100 OR inventory_stocks_sum_quantity IS NULL');

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('product_number', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($subQ) use ($search) {
                        $subQ->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $products = $query->orderBy('inventory_stocks_sum_quantity', 'asc')
            ->paginate(30)->withQueryString();

        return view('backend.reports.low_stock', compact('products'));
    }

    /**
     * AJAX Endpoint for Combined Alerts
     */
    public function lowStockCheck()
    {
        $data = $this->getNotificationData();
        return response()->json($data);
    }

    /**
     * View all notifications page
     */
    public function allNotifications()
    {
        $data = $this->getNotificationData();
        return view('backend.notifications.all', ['notifications' => $data['notifications']]);
    }

    /**
     * Mark all notifications as read
     */
    public function markNotificationsRead()
    {
        session(['notifications_read_at' => now()]);
        return response()->json(['status' => 'success']);
    }

    private function getNotificationData()
    {
        $notifications = [];
        $lastReadAt = session('notifications_read_at');
        
        // Expire "Mark as Read" after 10 minutes
        if ($lastReadAt && $lastReadAt->diffInMinutes(now()) >= 10) {
            session()->forget('notifications_read_at');
            $lastReadAt = null;
        }

        $unreadCount = 0;
        
        // 1. Fetch Low Stock Products (Threshold 100)
        $lowStockProducts = Product::where('status', 1)
            ->withSum('inventoryStocks', 'quantity')
            ->havingRaw('inventory_stocks_sum_quantity <= 100 OR inventory_stocks_sum_quantity IS NULL')
            ->orderBy('updated_at', 'desc') // Fetch by recent update
            ->take(15)
            ->get();

        foreach ($lowStockProducts as $product) {
            $isUnread = !$lastReadAt || $product->updated_at->gt($lastReadAt);
            if ($isUnread) $unreadCount++;

            $notifications[] = [
                'type' => 'lowStock',
                'title' => $product->name,
                'desc' => ($product->inventory_stocks_sum_quantity ?? 0) . ' in stock',
                'time' => $product->updated_at->diffForHumans(),
                'timestamp' => $product->updated_at->timestamp, // For sorting
                'url' => route('admin.reports.low-stock'),
                'icon' => 'fas fa-exclamation-triangle',
                'class' => ($product->inventory_stocks_sum_quantity <= 0) ? 'bg-danger' : 'bg-warning',
                'is_unread' => $isUnread,
                'is_out_of_stock' => ($product->inventory_stocks_sum_quantity <= 0)
            ];
        }

        // 2. Fetch Pending Product Requests (Admin only)
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user && $user->can('Manage Product Requests')) {
            $pendingRequests = ProductRequest::with('user')
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->take(15)
                ->get();

            foreach ($pendingRequests as $req) {
                $isUnread = !$lastReadAt || $req->created_at->gt($lastReadAt);
                if ($isUnread) $unreadCount++;

                $userName = $req->user ? $req->user->name : 'Unknown User';
                $notifications[] = [
                    'type' => 'request',
                    'title' => 'New Request: ' . $req->request_no,
                    'desc' => 'From ' . $userName . ' (Qty: ' . $req->total_qty . ')',
                    'time' => $req->created_at->diffForHumans(),
                    'timestamp' => $req->created_at->timestamp, // For sorting
                    'url' => route('admin.product-requests.index'),
                    'icon' => 'fas fa-box-open',
                    'class' => 'bg-info',
                    'is_unread' => $isUnread,
                    'is_out_of_stock' => false
                ];
            }
        }

        // 3. Fetch Pending User Registrations (Admin only)
        if ($user && $user->can('Administration')) {
            $pendingUsers = User::where('status', 0)
                ->where('role_id', '!=', 1) // Exclude main admin if somehow status=0
                ->orderBy('created_at', 'desc')
                ->take(15)
                ->get();

            foreach ($pendingUsers as $pUser) {
                $isUnread = !$lastReadAt || $pUser->created_at->gt($lastReadAt);
                if ($isUnread) $unreadCount++;

                // Map role_id to name
                $roleName = match($pUser->role_id) {
                    1 => 'Admin',
                    2 => 'User',
                    3 => 'Outlet User',
                    default => 'Unknown Role (' . $pUser->role_id . ')'
                };

                $notifications[] = [
                    'type' => 'registration',
                    'title' => 'New User: ' . $pUser->name,
                    'desc' => 'Needs Approval (Role: ' . $roleName . ')',
                    'time' => $pUser->created_at->diffForHumans(),
                    'timestamp' => $pUser->created_at->timestamp,
                    'url' => route('admin.users.index'),
                    'icon' => 'fas fa-user-plus',
                    'class' => 'bg-primary',
                    'is_unread' => $isUnread,
                    'is_out_of_stock' => false
                ];
            }
        }
        usort($notifications, function($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        return [
            'count' => $unreadCount,
            'notifications' => array_slice($notifications, 0, 20) // Limit to top 20
        ];
    }

    /**
     * Profit & Loss Report
     */
    public function profitLossReport(Request $request)
    {
        $revenueQuery = ProductRequest::where('status', 'completed');
        $purchasesQuery = Purchase::query();
        
        if ($request->start_date) {
            $revenueQuery->where('created_at', '>=', $request->start_date . ' 00:00:00');
            $purchasesQuery->where('date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $revenueQuery->where('created_at', '<=', $request->end_date . ' 23:59:59');
            $purchasesQuery->where('date', '<=', $request->end_date);
        }

        // Calculate Revenue
        $totalRevenue = $revenueQuery->sum('total_amount');

        // Calculate COGS for those completed requests
        $completedRequestIds = $revenueQuery->pluck('id');
        
        $totalCost = DB::table('product_request_items')
            ->join('products', 'product_request_items.product_id', '=', 'products.id')
            ->whereIn('product_request_items.product_request_id', $completedRequestIds)
            ->sum(DB::raw('product_request_items.qty * products.purchase_price'));

        // Calculate Profit
        $grossProfit = $totalRevenue - $totalCost;
        $profitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;
        
        $totalPurchases = $purchasesQuery->sum('total_amount');

        return view('backend.reports.profit_loss', compact(
            'totalRevenue',
            'totalCost',
            'grossProfit',
            'profitMargin',
            'totalPurchases'
        ));
    }

}
