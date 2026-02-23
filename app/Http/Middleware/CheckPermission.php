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

        // ReviewController requires 'Create Product Requests' permission
        if (str_contains($action, 'ReviewController')) {
            return 'Create Product Requests';
        }

        // Allow ProductRequestController to handle its own granular permissions
        if (str_contains($action, 'ProductRequestController')) {
            return null;
        }

        $map = [
            'Manage Categories' => ['CategoryController', 'SubCategoryController', 'ChildCategoryController'],
            'Manage Products' => ['ProductController', 'BrandController', 'SizeController', 'ColorController', 'ReviewController', 'UnitController'],
            'Manage Brands' => ['BrandController'],
            'Manage Vendors' => ['VendorController'],
            'Administration' => ['UserController', 'RolesController', 'PermissionController', 'SettingController'],
            'Manage Inventory' => ['IssueController', 'StockLedgerController', 'InventoryReportController'],
            'Manage Order Place' => ['BookingController'],
            'Manage Order Receive' => ['PurchaseController'],
            'Manage Product Requests' => [],
            'Manage Reports' => ['ReportController'],
            'Manage Notification' => ['NotificationController'],
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
