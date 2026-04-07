<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->can('Manage Reports')) {
            // Admin Global Stats
            $totalActiveProducts = Product::where('status', 1)->count();
            $totalInactiveProducts = Product::where('status', 0)->count();
            $totalProducts = Product::count();
            $totalIssues = Issue::count();
            $pendingRequests = ProductRequest::where('status', 'pending')->count();
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
            $requestStatus = ProductRequest::select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status');

            // Ensure all statuses are present for consistent coloring
            $statuses = ['pending', 'approved', 'rejected'];
            $statusData = [];
            foreach ($statuses as $status) {
                $statusData[] = $requestStatus[$status] ?? 0;
            }

            return view('backend.dashboard', compact(
                'totalActiveProducts',
                'totalInactiveProducts',
                'totalProducts',
                'totalIssues',
                'pendingRequests',
                'totalOutlets',
                'recentRequests',
                'issueLabels',
                'issueData',
                'statusData'
            ));
        } else {
            // Outlet Specific Stats
            $myTotalRequests = ProductRequest::where('user_id', $user->id)->count();
            $myPendingRequests = ProductRequest::where('user_id', $user->id)->where('status', 'pending')->count();
            $myTotalSpent = ProductRequest::where('user_id', $user->id)
                ->whereIn('status', ['approved', 'completed', 'complete'])
                ->sum('total_amount');

            $recentRequests = Order::where('user_id', $user->id)->with('user')->orderByDesc('id')->take(5)->get();

            return view('backend.dashboard', compact(
                'myTotalRequests',
                'myPendingRequests',
                'myTotalSpent',
                'recentRequests'
            ));
        }
    }
}
