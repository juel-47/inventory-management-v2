<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\AuditLog;
use App\Models\GeneralSetting;
use App\Models\Product;
use App\Models\ProductRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use App\Models\Issue;
use App\Models\IssueItem;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\User;
use App\Models\Vendor;
use Barryvdh\DomPDF\Facade\Pdf;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Carbon;
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
        // 1. Total Stock Value: Using Weighted Average Cost from PurchaseDetails
        // We calculate the average purchase price per product and multiply by current stock
        $totalStockValue = DB::table('inventory_stocks')
            ->join('products', 'inventory_stocks.product_id', '=', 'products.id')
            ->join(DB::raw('(SELECT product_id, AVG(unit_cost) as avg_cost FROM purchase_details GROUP BY product_id) as costs'), 'products.id', '=', 'costs.product_id')
            ->where('products.status', 1)
            ->sum(DB::raw('inventory_stocks.quantity * costs.avg_cost'));
        
        $totalProducts = Product::where('status', 1)->count();
        
        $lowStockCount = Product::where('status', 1)
            ->withSum('inventoryStocks', 'quantity')
            ->havingRaw('inventory_stocks_sum_quantity <= 100 OR inventory_stocks_sum_quantity IS NULL')
            ->get()
            ->count();
        
        // 2. Total Revenue: From Issues (Actual Sales/Outgoings)
        // We sum the total value of items issued. Since IssueItem might not have a 'price', 
        // we use the product's selling price or the order's unit price.
        $totalRevenue = DB::table('issue_items')
            ->join('products', 'issue_items.product_id', '=', 'products.id')
            ->sum(DB::raw('issue_items.quantity * COALESCE(products.price, products.purchase_price, 0)'));
        
        // 3. COGS (Cost of Goods Sold): Based on actual issued quantity * average purchase cost
        $totalCost = DB::table('issue_items')
            ->join(DB::raw('(SELECT product_id, AVG(unit_cost) as avg_cost FROM purchase_details GROUP BY product_id) as costs'), 'issue_items.product_id', '=', 'costs.product_id')
            ->sum(DB::raw('issue_items.quantity * costs.avg_cost'));

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
            ->withSum('inventoryStocks', 'quantity')
            ->where('status', 1);

        // Filters
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->brand_id) {
            $query->where('brand_id', $request->brand_id);
        }

        // Calculate Summary Stats from database aggregates BEFORE pagination
        $summaryQuery = DB::table('products')
            ->join('inventory_stocks', 'products.id', '=', 'inventory_stocks.product_id')
            ->join(DB::raw('(SELECT product_id, AVG(unit_cost) as avg_cost FROM purchase_details GROUP BY product_id) as costs'), 'products.id', '=', 'costs.product_id')
            ->where('products.status', 1);

        if ($request->category_id) {
            $summaryQuery->where('products.category_id', $request->category_id);
        }
        if ($request->brand_id) {
            $summaryQuery->where('products.brand_id', $request->brand_id);
        }

        $summaryData = $summaryQuery->selectRaw('
            SUM(inventory_stocks.quantity) as untyped_total_qty,
            SUM(inventory_stocks.quantity * costs.avg_cost) as untyped_total_value,
            SUM(inventory_stocks.quantity * products.price) as untyped_potential_revenue
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
        if ($request->filled('search')) {
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

        // Vendor filter
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        $products = $query->orderBy('inventory_stocks_sum_quantity', 'asc')
            ->paginate(30)->withQueryString();

        $vendors = Vendor::where('status', 1)->orderBy('shop_name')->get();

        return view('backend.reports.low_stock', compact('products', 'vendors'));
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

        // 4. Fetch PDF Ready Notifications from Cache
        if ($user) {
            $pdfCacheKey = 'user_pdf_notifications_' . $user->id;
            $pdfNotifications = \Illuminate\Support\Facades\Cache::get($pdfCacheKey, []);
            // \Illuminate\Support\Facades\Log::info("Fetching PDF notifications for user {$user->id} from key {$pdfCacheKey}. Found: " . count($pdfNotifications));
            
            foreach ($pdfNotifications as $pdfNotif) {
                $notifTime = \Illuminate\Support\Carbon::createFromTimestamp($pdfNotif['timestamp']);
                $isUnread = !$lastReadAt || $notifTime->gt($lastReadAt);
                if ($isUnread) $unreadCount++;

                $notifications[] = [
                    'type' => 'pdf_ready',
                    'title' => $pdfNotif['title'],
                    'desc' => $pdfNotif['desc'],
                    'time' => $notifTime->diffForHumans(),
                    'timestamp' => $pdfNotif['timestamp'],
                    'url' => $pdfNotif['url'],
                    'icon' => $pdfNotif['icon'],
                    'class' => $pdfNotif['class'],
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
        // Use IssueItems for actual revenue and cost, not ProductRequests
        $revenueQuery = DB::table('issue_items')
            ->join('products', 'issue_items.product_id', '=', 'products.id')
            ->join('issues', 'issue_items.issue_id', '=', 'issues.id');
        
        $purchasesQuery = Purchase::query();
        
        if ($request->start_date) {
            $revenueQuery->whereDate('issues.created_at', '>=', $request->start_date);
            $purchasesQuery->where('date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $revenueQuery->whereDate('issues.created_at', '<=', $request->end_date);
            $purchasesQuery->where('date', '<=', $request->end_date);
        }

        // Calculate Actual Revenue: Sum of (Issued Qty * Product Price)
        $totalRevenue = $revenueQuery->sum(DB::raw('issue_items.quantity * COALESCE(products.price, products.purchase_price, 0)'));

        // Calculate Actual COGS: Sum of (Issued Qty * Average Purchase Cost)
        $totalCost = DB::table('issue_items')
            ->join(DB::raw('(SELECT product_id, AVG(unit_cost) as avg_cost FROM purchase_details GROUP BY product_id) as costs'), 'issue_items.product_id', '=', 'costs.product_id')
            ->join('issues', 'issue_items.issue_id', '=', 'issues.id')
            ->when($request->start_date, function($q) use ($request) {
                return $q->whereDate('issues.created_at', '>=', $request->start_date);
            })
            ->when($request->end_date, function($q) use ($request) {
                return $q->whereDate('issues.created_at', '<=', $request->end_date);
            })
            ->sum(DB::raw('issue_items.quantity * costs.avg_cost'));

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

    /**
     * Order & Issue Report — two modes:
     *   Global  (user_id empty) → aggregate across all users
     *   360°    (user_id set)   → deep-dive for one user
     */
    public function orderReport(Request $request)
    {
        $users = User::role(['Outlet User', 'User'])->get(['id', 'name', 'outlet_name']);

        $query = Order::where('status', 'completed');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('placed_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('placed_at', '<=', $request->date_to);
        }
        if ($request->filled('month')) {
            $query->whereMonth('placed_at', $request->month);
        }
        if ($request->filled('year')) {
            $query->whereYear('placed_at', $request->year);
        }

        $orderIds = (clone $query)->pluck('id');

        $summary = (clone $query)->selectRaw('
            COUNT(*) as total_orders,
            COALESCE(SUM(total_amount),0) as total_value,
            COALESCE(AVG(total_amount),0) as avg_order_value
        ')->first();

        $issueStats = Issue::leftJoin('issue_items', 'issues.id', '=', 'issue_items.issue_id')
            ->where(function ($q) use ($orderIds, $request) {
                $q->whereIn('order_id', $orderIds);
                $q->orWhere(function ($sq) use ($request) {
                    $sq->whereNull('order_id');
                    if ($request->filled('user_id')) $sq->where('outlet_id', $request->user_id);
                    if ($request->filled('date_from')) $sq->whereDate('issues.created_at', '>=', $request->date_from);
                    if ($request->filled('date_to')) $sq->whereDate('issues.created_at', '<=', $request->date_to);
                    if ($request->filled('month')) $sq->whereMonth('issues.created_at', $request->month);
                    if ($request->filled('year')) $sq->whereYear('issues.created_at', $request->year);
                });
            })
            ->selectRaw('COUNT(DISTINCT issues.id) as total_issues, COALESCE(SUM(issue_items.quantity),0) as total_issued_qty')
            ->first();

        if ($request->filled('user_id')) {
            $user = User::findOrFail($request->user_id);

            $paymentStats = OrderPayment::whereIn('order_id', $orderIds)
                ->selectRaw('COALESCE(SUM(amount),0) as total_paid')->first();
            $totalDue = $summary->total_value - $paymentStats->total_paid;

            $linkedValue = IssueItem::whereHas('issue', fn($q) => $q->whereIn('order_id', $orderIds))
                ->join('issues', 'issue_items.issue_id', '=', 'issues.id')
                ->join('products', 'issue_items.product_id', '=', 'products.id')
                ->sum(DB::raw('issue_items.quantity * COALESCE(products.price, products.purchase_price, 0)'));

            $standaloneValue = IssueItem::whereHas('issue', function ($q) use ($request) {
                    $q->whereNull('order_id')->where('outlet_id', $request->user_id);
                    if ($request->filled('date_from')) $q->whereDate('issues.created_at', '>=', $request->date_from);
                    if ($request->filled('date_to')) $q->whereDate('issues.created_at', '<=', $request->date_to);
                    if ($request->filled('month')) $q->whereMonth('issues.created_at', $request->month);
                    if ($request->filled('year')) $q->whereYear('issues.created_at', $request->year);
                })
                ->join('issues', 'issue_items.issue_id', '=', 'issues.id')
                ->join('products', 'issue_items.product_id', '=', 'products.id')
                ->sum(DB::raw('issue_items.quantity * COALESCE(products.price, products.purchase_price, 0)'));

            $issueValue = $linkedValue + $standaloneValue;
            $pendingValue = max(0, $summary->total_value - $linkedValue);

            $orders = $query->with('items')->orderByDesc('placed_at')->get();

            $issues = Issue::with(['items.product', 'order'])
                ->where(function ($q) use ($orderIds, $request) {
                    $q->whereIn('order_id', $orderIds);
                    $q->orWhere(function ($sq) use ($request) {
                        $sq->whereNull('order_id');
                        if ($request->filled('user_id')) $sq->where('outlet_id', $request->user_id);
                        if ($request->filled('date_from')) $sq->whereDate('issues.created_at', '>=', $request->date_from);
                        if ($request->filled('date_to')) $sq->whereDate('issues.created_at', '<=', $request->date_to);
                        if ($request->filled('month')) $sq->whereMonth('issues.created_at', $request->month);
                        if ($request->filled('year')) $sq->whereYear('issues.created_at', $request->year);
                    });
                })
                ->orderByDesc('created_at')
                ->get()
                ->map(function ($issue) {
                    $value = 0;
                    foreach ($issue->items as $item) {
                        $price = $item->product->price ?? $item->product->purchase_price ?? 0;
                        $value += $item->quantity * (float) $price;
                    }
                    $issue->computed_value = $value;
                    return $issue;
                });

            $payments = OrderPayment::with('order')
                ->whereIn('order_id', $orderIds)
                ->orderByDesc('created_at')
                ->get();

            $productComparison = OrderItem::whereIn('order_id', $orderIds)
                ->selectRaw('product_id, product_name, SUM(quantity) as ordered_qty, COALESCE(SUM(line_total),0) as ordered_value')
                ->groupBy('product_id', 'product_name')
                ->orderByDesc('ordered_value')
                ->get()
                ->map(function ($item) use ($orderIds) {
                    $issuedQty = IssueItem::whereHas('issue', fn($q) => $q->whereIn('order_id', $orderIds))
                        ->where('product_id', $item->product_id)->sum('quantity');
                    $item->issued_qty = (int) $issuedQty;
                    $item->pending_qty = max(0, $item->ordered_qty - $item->issued_qty);
                    return $item;
                });

            $monthlyTrend = OrderItem::whereIn('order_id', $orderIds)
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.status', 'completed')
                ->selectRaw("DATE_FORMAT(orders.placed_at, '%Y-%m') as month, COUNT(DISTINCT orders.id) as orders_count")
                ->groupBy('month')->orderBy('month')
                ->get()
                ->map(function ($trend) use ($orderIds, $request) {
                    $m = $trend->month;
                    $linkedQty = IssueItem::whereHas('issue', fn($q) => $q->whereIn('order_id', $orderIds)
                        ->whereYear('created_at', substr($m, 0, 4))
                        ->whereMonth('created_at', substr($m, 5, 2))
                    )->sum('quantity');
                    $standaloneQty = IssueItem::whereHas('issue', function($q) use ($request, $m) {
                        $q->whereNull('order_id');
                        if ($request->filled('user_id')) $q->where('outlet_id', $request->user_id);
                        $q->whereYear('created_at', substr($m, 0, 4));
                        $q->whereMonth('created_at', substr($m, 5, 2));
                    })->sum('quantity');
                    $trend->issue_qty = (int) $linkedQty + (int) $standaloneQty;

                    $issueQuery = IssueItem::join('issues', 'issue_items.issue_id', '=', 'issues.id')
                        ->join('products', 'issue_items.product_id', '=', 'products.id')
                        ->whereYear('issues.created_at', substr($m, 0, 4))
                        ->whereMonth('issues.created_at', substr($m, 5, 2))
                        ->where(function ($q) use ($orderIds, $request) {
                            $q->whereIn('issues.order_id', $orderIds);
                            $q->orWhere(function ($sq) use ($request) {
                                $sq->whereNull('issues.order_id');
                                if ($request->filled('user_id')) $sq->where('issues.outlet_id', $request->user_id);
                            });
                        });
                    $trend->total_amount = $issueQuery->sum(DB::raw('issue_items.quantity * COALESCE(products.price, products.purchase_price, 0)'));
                    $trend->unique_products = (clone $issueQuery)->distinct('issue_items.product_id')->count('issue_items.product_id');
                    return $trend;
                });

            return view('backend.reports.orders', compact(
                'user', 'users', 'summary', 'issueStats', 'orderIds',
                'paymentStats', 'totalDue', 'issueValue', 'pendingValue',
                'orders', 'issues', 'payments', 'productComparison', 'monthlyTrend'
            ));
        }

        $issueValue = Issue::leftJoin('issue_items', 'issues.id', '=', 'issue_items.issue_id')
            ->join('products', 'issue_items.product_id', '=', 'products.id')
            ->where(function ($q) use ($orderIds, $request) {
                $q->whereIn('order_id', $orderIds);
                $q->orWhere(function ($sq) use ($request) {
                    $sq->whereNull('order_id');
                    if ($request->filled('date_from')) $sq->whereDate('issues.created_at', '>=', $request->date_from);
                    if ($request->filled('date_to')) $sq->whereDate('issues.created_at', '<=', $request->date_to);
                    if ($request->filled('month')) $sq->whereMonth('issues.created_at', $request->month);
                    if ($request->filled('year')) $sq->whereYear('issues.created_at', $request->year);
                });
            })
            ->sum(DB::raw('issue_items.quantity * COALESCE(products.price, products.purchase_price, 0)'));

        $orders = $query->with(['user', 'items.product'])->orderByDesc('placed_at')->paginate(30)->withQueryString();

        $productFrequency = OrderItem::whereIn('order_id', $orderIds)
            ->selectRaw('product_id, product_name, COUNT(*) as times_ordered, SUM(quantity) as total_qty, COALESCE(SUM(line_total),0) as total_value')
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('times_ordered')
            ->paginate(30)->withQueryString();

        $monthlyTrend = OrderItem::whereIn('order_id', $orderIds)
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->selectRaw("DATE_FORMAT(orders.placed_at, '%Y-%m') as month, COUNT(DISTINCT orders.id) as orders_count, COALESCE(SUM(order_items.line_total),0) as total_amount, COUNT(DISTINCT order_items.product_id) as unique_products")
            ->groupBy('month')->orderBy('month')
            ->get();

        $userSummary = Order::whereIn('id', $orderIds)
            ->selectRaw('user_id, COUNT(*) as total_orders, COALESCE(SUM(total_amount),0) as total_value')
            ->groupBy('user_id')
            ->orderByDesc('total_value')
            ->get()
            ->keyBy('user_id');

        return view('backend.reports.orders', compact(
            'summary', 'issueStats', 'productFrequency', 'monthlyTrend', 'userSummary', 'users', 'orderIds', 'orders', 'issueValue'
        ));
    }

    /**
     * Order & Issue Report — PDF Export
     */
    public function orderReportPdf(Request $request)
    {
        $query = Order::with(['user', 'items.product'])->where('status', 'completed');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('placed_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('placed_at', '<=', $request->date_to);
        }
        if ($request->filled('month')) {
            $query->whereMonth('placed_at', $request->month);
        }
        if ($request->filled('year')) {
            $query->whereYear('placed_at', $request->year);
        }

        $orderIds = (clone $query)->pluck('id');

        $summary = (clone $query)->selectRaw('
            COUNT(*) as total_orders,
            COALESCE(SUM(total_amount),0) as total_value,
            COALESCE(AVG(total_amount),0) as avg_order_value
        ')->first();

        $issueStats = Issue::leftJoin('issue_items', 'issues.id', '=', 'issue_items.issue_id')
            ->where(function ($q) use ($orderIds, $request) {
                $q->whereIn('order_id', $orderIds);
                $q->orWhere(function ($sq) use ($request) {
                    $sq->whereNull('order_id');
                    if ($request->filled('user_id')) $sq->where('outlet_id', $request->user_id);
                    if ($request->filled('date_from')) $sq->whereDate('issues.created_at', '>=', $request->date_from);
                    if ($request->filled('date_to')) $sq->whereDate('issues.created_at', '<=', $request->date_to);
                    if ($request->filled('month')) $sq->whereMonth('issues.created_at', $request->month);
                    if ($request->filled('year')) $sq->whereYear('issues.created_at', $request->year);
                });
            })
            ->selectRaw('COUNT(DISTINCT issues.id) as total_issues, COALESCE(SUM(issue_items.quantity),0) as total_issued_qty')
            ->first();

        $settings = GeneralSetting::first();

        // ─── 360° per-user PDF ───────────────────────────────────
        if ($request->filled('user_id')) {
            $user = User::find($request->user_id);

            $paymentStats = OrderPayment::whereIn('order_id', $orderIds)
                ->selectRaw('COALESCE(SUM(amount),0) as total_paid')->first();
            $totalDue = $summary->total_value - $paymentStats->total_paid;

            $linkedValue = IssueItem::whereHas('issue', fn($q) => $q->whereIn('order_id', $orderIds))
                ->join('issues', 'issue_items.issue_id', '=', 'issues.id')
                ->join('products', 'issue_items.product_id', '=', 'products.id')
                ->sum(DB::raw('issue_items.quantity * COALESCE(products.price, products.purchase_price, 0)'));

            $standaloneValue = IssueItem::whereHas('issue', function ($q) use ($request) {
                    $q->whereNull('order_id')->where('outlet_id', $request->user_id);
                    if ($request->filled('date_from')) $q->whereDate('issues.created_at', '>=', $request->date_from);
                    if ($request->filled('date_to')) $q->whereDate('issues.created_at', '<=', $request->date_to);
                    if ($request->filled('month')) $q->whereMonth('issues.created_at', $request->month);
                    if ($request->filled('year')) $q->whereYear('issues.created_at', $request->year);
                })
                ->join('issues', 'issue_items.issue_id', '=', 'issues.id')
                ->join('products', 'issue_items.product_id', '=', 'products.id')
                ->sum(DB::raw('issue_items.quantity * COALESCE(products.price, products.purchase_price, 0)'));

            $issueValue = $linkedValue + $standaloneValue;
            $pendingValue = max(0, $summary->total_value - $linkedValue);

            // Orders
            $orders = $query->with('items')->orderByDesc('placed_at')->get();

            // Issues with computed value (linked + standalone)
            $issues = Issue::with(['items.product', 'order'])
                ->where(function ($q) use ($orderIds, $request) {
                    $q->whereIn('order_id', $orderIds);
                    $q->orWhere(function ($sq) use ($request) {
                        $sq->whereNull('order_id');
                        if ($request->filled('user_id')) $sq->where('outlet_id', $request->user_id);
                        if ($request->filled('date_from')) $sq->whereDate('issues.created_at', '>=', $request->date_from);
                        if ($request->filled('date_to')) $sq->whereDate('issues.created_at', '<=', $request->date_to);
                        if ($request->filled('month')) $sq->whereMonth('issues.created_at', $request->month);
                        if ($request->filled('year')) $sq->whereYear('issues.created_at', $request->year);
                    });
                })
                ->orderByDesc('created_at')
                ->get()
                ->map(function ($issue) {
                    $value = 0;
                    foreach ($issue->items as $item) {
                        $price = $item->product->price ?? $item->product->purchase_price ?? 0;
                        $value += $item->quantity * (float) $price;
                    }
                    $issue->computed_value = $value;
                    return $issue;
                });

            // Payments
            $payments = OrderPayment::with('order')
                ->whereIn('order_id', $orderIds)
                ->orderByDesc('created_at')
                ->get();

            // Product comparison
            $productComparison = OrderItem::whereIn('order_id', $orderIds)
                ->selectRaw('product_id, product_name, SUM(quantity) as ordered_qty, COALESCE(SUM(line_total),0) as ordered_value')
                ->groupBy('product_id', 'product_name')
                ->orderByDesc('ordered_value')
                ->get()
                ->map(function ($item) use ($orderIds) {
                    $issuedQty = IssueItem::whereHas('issue', fn($q) => $q->whereIn('order_id', $orderIds))
                        ->where('product_id', $item->product_id)->sum('quantity');
                    $item->issued_qty = (int) $issuedQty;
                    $item->pending_qty = max(0, $item->ordered_qty - $item->issued_qty);
                    return $item;
                });

            // Monthly trend
            $monthlyTrend = OrderItem::whereIn('order_id', $orderIds)
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.status', 'completed')
                ->selectRaw("DATE_FORMAT(orders.placed_at, '%Y-%m') as month, COUNT(DISTINCT orders.id) as orders_count")
                ->groupBy('month')->orderBy('month')
                ->get()
                ->map(function ($trend) use ($orderIds, $request) {
                    $m = $trend->month;
                    $linkedQty = IssueItem::whereHas('issue', fn($q) => $q->whereIn('order_id', $orderIds)
                        ->whereYear('created_at', substr($m, 0, 4))
                        ->whereMonth('created_at', substr($m, 5, 2))
                    )->sum('quantity');
                    $standaloneQty = IssueItem::whereHas('issue', function($q) use ($request, $m) {
                        $q->whereNull('order_id');
                        if ($request->filled('user_id')) $q->where('outlet_id', $request->user_id);
                        $q->whereYear('created_at', substr($m, 0, 4));
                        $q->whereMonth('created_at', substr($m, 5, 2));
                    })->sum('quantity');
                    $trend->issue_qty = (int) $linkedQty + (int) $standaloneQty;

                    $issueQuery = IssueItem::join('issues', 'issue_items.issue_id', '=', 'issues.id')
                        ->join('products', 'issue_items.product_id', '=', 'products.id')
                        ->whereYear('issues.created_at', substr($m, 0, 4))
                        ->whereMonth('issues.created_at', substr($m, 5, 2))
                        ->where(function ($q) use ($orderIds, $request) {
                            $q->whereIn('issues.order_id', $orderIds);
                            $q->orWhere(function ($sq) use ($request) {
                                $sq->whereNull('issues.order_id');
                                if ($request->filled('user_id')) $sq->where('issues.outlet_id', $request->user_id);
                            });
                        });
                    $trend->total_amount = $issueQuery->sum(DB::raw('issue_items.quantity * COALESCE(products.price, products.purchase_price, 0)'));
                    $trend->unique_products = (clone $issueQuery)->distinct('issue_items.product_id')->count('issue_items.product_id');
                    return $trend;
                });

            $pdf = Pdf::loadView('backend.reports.orders_pdf', compact(
                'user', 'summary', 'issueStats', 'paymentStats', 'totalDue', 'issueValue', 'pendingValue',
                'orders', 'issues', 'payments', 'productComparison', 'monthlyTrend', 'settings', 'request'
            ))->setPaper('a4', 'landscape');
        } else {
            // ─── Global PDF ───────────────────────────────────────
            $issueValue = Issue::leftJoin('issue_items', 'issues.id', '=', 'issue_items.issue_id')
                ->join('products', 'issue_items.product_id', '=', 'products.id')
                ->where(function ($q) use ($orderIds, $request) {
                    $q->whereIn('order_id', $orderIds);
                    $q->orWhere(function ($sq) use ($request) {
                        $sq->whereNull('order_id');
                        if ($request->filled('date_from')) $sq->whereDate('issues.created_at', '>=', $request->date_from);
                        if ($request->filled('date_to')) $sq->whereDate('issues.created_at', '<=', $request->date_to);
                        if ($request->filled('month')) $sq->whereMonth('issues.created_at', $request->month);
                        if ($request->filled('year')) $sq->whereYear('issues.created_at', $request->year);
                    });
                })
                ->sum(DB::raw('issue_items.quantity * COALESCE(products.price, products.purchase_price, 0)'));

            $productFrequency = OrderItem::whereIn('order_id', $orderIds)
                ->selectRaw('product_id, product_name, COUNT(*) as times_ordered, SUM(quantity) as total_qty, COALESCE(SUM(line_total),0) as total_value')
                ->groupBy('product_id', 'product_name')
                ->orderByDesc('times_ordered')
                ->get();

            $userSummary = Order::whereIn('id', $orderIds)
                ->selectRaw('user_id, COUNT(*) as total_orders, COALESCE(SUM(total_amount),0) as total_value')
                ->groupBy('user_id')
                ->orderByDesc('total_value')
                ->get()
                ->keyBy('user_id');

            $pdf = Pdf::loadView('backend.reports.orders_pdf', compact(
                'summary', 'issueStats', 'productFrequency', 'userSummary', 'settings', 'request', 'orderIds', 'issueValue'
            ))->setPaper('a4', 'landscape');
        }

        $fileName = 'order-issue-report-' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($fileName);
    }

    /**
     * Order & Issue Report — Async PDF Generation (Background Job)
     */
    public function orderReportPdfAsync(Request $request)
    {
        $filters = $request->only(['user_id', 'month', 'year', 'date_from', 'date_to']);

        dispatch(new \App\Jobs\GenerateReportPdfJob($filters, auth()->id()));

        Toastr::info('Order & Issue Report is generating in the background. Check notifications when ready.');

        return redirect()->back();
    }

    /**
     * Order & Issue Report — Download Generated PDF
     */
    public function downloadReportPdf($file)
    {
        $path = storage_path('app/public/reports/' . $file);

        if (!file_exists($path)) {
            return redirect()->back()->with('error', 'File not found or has expired.');
        }

        return response()->download($path)->deleteFileAfterSend(true);
    }

    /**
     * Audit trail report.
     */
    public function auditReport(Request $request)
    {
        $query = AuditLog::with(['user', 'vendor'])->latest();

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', (int) $request->user_id);
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', (int) $request->vendor_id);
        }

        if ($request->filled('reference')) {
            $reference = trim((string) $request->reference);
            $query->where('reference_no', 'like', '%' . $reference . '%');
        }

        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
        }

        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
        }

        $logs = $query->paginate(30)->withQueryString();
        $summaryQuery = clone $query;

        $summary = [
            'count' => (clone $summaryQuery)->count(),
            'today_count' => (clone $summaryQuery)->whereDate('created_at', today())->count(),
            'modules' => (clone $summaryQuery)->select('module')->distinct()->count('module'),
            'users' => (clone $summaryQuery)->whereNotNull('user_id')->distinct()->count('user_id'),
        ];

        $modules = AuditLog::query()
            ->whereNotNull('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module');

        $actions = AuditLog::query()
            ->whereNotNull('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $users = User::query()->orderBy('name')->get(['id', 'name']);
        $vendors = Vendor::query()->orderBy('shop_name')->get(['id', 'shop_name']);

        return view('backend.reports.audit', compact('logs', 'summary', 'modules', 'actions', 'users', 'vendors'));
    }

}
