<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('admin.login');
        }

        // Check if user is trying to access /admin routes
        if ($request->is('admin/*')) {
            // Only Admin role can access /admin routes
            if (!$user->hasRole('Admin')) {
                abort(403, 'Access Denied. Only administrators can access this area.');
            }
        }

        $permission = $this->getPermission($request);

        if ($permission && !$user->can($permission)) {
            abort(403, 'Access Denied');
        }
        return $next($request);
        // return $next($request);
    }
    private function getPermission(Request $request)
    {
        $action = $request->route()->getActionName();
        
        // Specific case for Product list view (allows View Product Stock)
        if (str_contains($action, 'ProductController@index') && !$request->user()->can('Manage Products')) {
            return 'View Product Stock';
        }

        // Manual announcement screen/actions are managed under System/Administration menu.
        if (str_contains($action, 'ProductController@announcementIndex') || str_contains($action, 'ProductController@sendAnnouncement')) {
            return 'Administration';
        }

        // ReviewController requires 'Create Product Requests' permission
        if (str_contains($action, 'ReviewController')) {
            return 'Create Product Requests';
        }

        // Allow ProductRequestController to handle its own granular permissions
        if (str_contains($action, 'ProductRequestController')) {
            return null;
        }

        $map = [
            'Manage Categories' => ['CategoryController', 'SubCategoryController', 'ChildCategoryController', 'SliderController'],
            'Manage Products' => ['ProductController', 'BrandController', 'SizeController', 'ColorController', 'ReviewController', 'UnitController'],
            'Manage Brands' => ['BrandController'],
            'Manage Vendors' => ['VendorController'],
            'Administration' => ['UserController', 'RolesController', 'PermissionController', 'SettingController', 'TaxController', 'DiscountController'],
            'Manage Inventory' => ['IssueController', 'StockLedgerController', 'InventoryReportController'],
            'Manage Order Place' => ['BookingController', 'FrontendOrderController'],
            'Manage Order Receive' => ['PurchaseController'],
            'Manage Product Requests' => [],
            'Manage Reports' => ['ReportController'],
            'Manage Notification' => ['NotificationController'],
            'Accountants' => ['AccountController'],
        ];

        foreach ($map as $permission => $controllers) {
            foreach ($controllers as $controller) {
                if (str_contains($action, $controller)) {
                    return $permission;
                }
            }
        }

        return null;
    }
}
