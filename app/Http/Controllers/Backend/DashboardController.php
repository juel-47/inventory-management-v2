<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductRequest;
use App\Models\Issue;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Initialize all variables with default values to prevent "Undefined variable" errors in view
        $totalActiveProducts = $totalInactiveProducts = $totalProducts = $totalIssues = $pendingRequests = $totalOutlets = 0;
        $myTotalRequests = $myPendingRequests = 0;
        $myTotalSpent = 0;
        $recentRequests = collect();
        $issueLabels = $issueData = $statusData = collect();

        if ($user->can('Manage Reports')) {
            // Admin Global Stats
            $totalActiveProducts = Product::where('status', 1)->count();
            $totalInactiveProducts = Product::where('status', 0)->count();
            $totalProducts = Product::count();
            $totalIssues = Issue::count();
            $pendingRequests = Order::where('status', 'pending')->count();
            $totalOutlets = User::role('Outlet User')->count();
            
            // Recent frontend orders for Admin
            $recentRequests = Order::with('user')->orderByDesc('id')->take(5)->get();

            // Chart Data: Monthly Issues (Last 12 Months)
            $monthlyIssues = Issue::select(
                DB::raw('count(*) as total'),
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month_year"),
                DB::raw("DATE_FORMAT(created_at, '%M') as month_name")
            )
            ->where('created_at', '>=', now()->subMonths(11))
            ->groupBy('month_year', 'month_name')
            ->orderBy('month_year')
            ->get();

            $issueLabels = $monthlyIssues->pluck('month_name');
            $issueData = $monthlyIssues->pluck('total');

            // Chart Data: Request Status Distribution
            $requestStatus = Order::select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status');
            
            // Ensure all statuses are present for consistent coloring
            $statuses = ['pending', 'approved', 'rejected'];
            $statusData = [];
            foreach ($statuses as $status) {
                $statusData[] = $requestStatus[$status] ?? 0;
            }
        } 
        
        // Always calculate Outlet Specific Stats if the user has relevant permissions
        // This allows a user to have BOTH Admin and Outlet-like permissions
        if (!$user->can('Manage Reports') || $user->hasAnyRole(['Outlet User', 'User'])) {
            $myTotalRequests = Order::where('user_id', $user->id)->count();
            $myPendingRequests = Order::where('user_id', $user->id)->where('status', 'pending')->count();
            $myTotalSpent = Order::where('user_id', $user->id)
                ->whereIn('status', ['approved', 'completed', 'complete']) 
                ->sum('total_amount');
            
            // Only override recentRequests if not already set by Admin logic
            if ($recentRequests->isEmpty()) {
                $recentRequests = Order::where('user_id', $user->id)->with('user')->orderByDesc('id')->take(5)->get();
            }
        }

        return view('backend.dashboard', compact(
            'totalActiveProducts',
            'totalInactiveProducts',
            'totalProducts', 
            'totalIssues', 
            'pendingRequests', 
            'totalOutlets',
            'myTotalRequests',
            'myPendingRequests',
            'myTotalSpent',
            'recentRequests',
            'issueLabels',
            'issueData',
            'statusData'
        ));
    }
}
