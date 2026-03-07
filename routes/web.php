<?php

use App\Http\Controllers\Backend\BookingController;
use App\Http\Controllers\Backend\BrandController;
// use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Backend\SubCategoryController;
use App\Http\Controllers\Backend\TaxController;
use App\Http\Controllers\Backend\ChildCategoryController;
use App\Http\Controllers\Backend\ColorController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\DiscountController;
use App\Http\Controllers\Backend\FrontendOrderController;
use App\Http\Controllers\Backend\InventoryReportController;
use App\Http\Controllers\Backend\IssueController;
use App\Http\Controllers\Backend\PermissionController;
use App\Http\Controllers\Backend\ProductController;
use App\Http\Controllers\Backend\ProductRequestController;
use App\Http\Controllers\Backend\ProfileController;
use App\Http\Controllers\Backend\PurchaseController;
use App\Http\Controllers\Backend\ReportController;
use App\Http\Controllers\Backend\RolesController;
use App\Http\Controllers\Backend\PricingRuleController;
use App\Http\Controllers\Backend\SettingController;
use App\Http\Controllers\Backend\SizeController;
use App\Http\Controllers\Backend\StockLedgerController;
use App\Http\Controllers\Backend\UnitController;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\Backend\VendorController;
use App\Http\Controllers\Backend\ProductTypeController;
use App\Http\Controllers\Backend\ReviewController;
use App\Http\Controllers\Backend\CartController;
use App\Http\Controllers\Backend\CustomProductRequestController;
use App\Http\Controllers\Frontend\WishlistController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// Sample file download route (public)
Route::get('/sample/{filename}', function ($filename) {
    $path = 'sample/' . $filename;
    if (Storage::disk('public')->exists($path)) {
        $file = Storage::disk('public')->path($path);
        return response()->download($file, $filename);
    }
    abort(404);
})->name('admin.products.sample.download');

Route::get('/', [\App\Http\Controllers\Frontend\HomeController::class, 'index'])->name('home');
Route::get('/shop', [\App\Http\Controllers\Frontend\HomeController::class, 'shop'])->name('shop');
Route::get('/product/{slug}', [\App\Http\Controllers\Frontend\HomeController::class, 'productDetails'])->name('product.details');
Route::get('/products/live-search', [\App\Http\Controllers\Frontend\HomeController::class, 'liveSearch'])->name('frontend.products.live-search');
Route::get('/frontend/reviews/product/{productId}', [ReviewController::class, 'getProductReviews'])->name('frontend.reviews.product');
// Frontend cart page
Route::get('/cart', [\App\Http\Controllers\Frontend\CartController::class, 'index'])->name('cart.index');

if (app()->environment('local')) {
    Route::get('/_preview/error/{code}', function (int $code) {
        abort($code);
    })->whereNumber('code')->name('error.preview');
}

// ── Frontend Cart API (DB-backed, auth users only) ──────────────────────────
Route::middleware(['auth', 'role:Outlet User|User'])->group(function () {
    Route::get('/my-account', [\App\Http\Controllers\Frontend\AccountController::class, 'index'])->name('account.index');
    Route::post('/my-account/profile', [\App\Http\Controllers\Frontend\AccountController::class, 'updateProfile'])->name('account.profile.update');
    Route::post('/my-account/password', [\App\Http\Controllers\Frontend\AccountController::class, 'updatePassword'])->name('account.password.update');
    Route::post('/my-account/order-form/add-to-cart', [\App\Http\Controllers\Frontend\AccountController::class, 'addOrderFormToCart'])->name('account.order-form.add-to-cart');
    Route::post('/my-account/order-form/save', [\App\Http\Controllers\Frontend\AccountController::class, 'saveOrderForm'])->name('account.order-form.save');
    Route::post('/my-account/saved-forms/{savedRequest}/checkout', [\App\Http\Controllers\Frontend\AccountController::class, 'checkoutSavedForm'])->name('account.saved-forms.checkout');
    Route::delete('/my-account/saved-forms/{savedRequest}', [\App\Http\Controllers\Frontend\AccountController::class, 'deleteSavedForm'])->name('account.saved-forms.delete');
    Route::get('/checkout', [\App\Http\Controllers\Frontend\CartController::class, 'checkout'])->name('checkout.index');
    Route::post('/checkout/place-order', [\App\Http\Controllers\Frontend\CartController::class, 'placeOrder'])->name('checkout.place-order');
    Route::get('/my-orders', [\App\Http\Controllers\Frontend\OrderController::class, 'index'])->name('orders.index');
    Route::get('/my-orders/{order}', [\App\Http\Controllers\Frontend\OrderController::class, 'show'])->name('orders.show');
    Route::post('/my-orders/{order}/reorder', [\App\Http\Controllers\Frontend\OrderController::class, 'reorder'])->name('orders.reorder');
    Route::get('/frontend/cart/items',           [\App\Http\Controllers\Frontend\CartController::class, 'items'])->name('frontend.cart.items');
    Route::post('/frontend/cart/add',            [\App\Http\Controllers\Frontend\CartController::class, 'add'])->name('frontend.cart.add');
    Route::post('/frontend/cart/remove',         [\App\Http\Controllers\Frontend\CartController::class, 'remove'])->name('frontend.cart.remove');
    Route::post('/frontend/cart/update-qty',     [\App\Http\Controllers\Frontend\CartController::class, 'updateQuantity'])->name('frontend.cart.update-qty');
    Route::post('/frontend/cart/clear',          [\App\Http\Controllers\Frontend\CartController::class, 'clear'])->name('frontend.cart.clear');

    // ── Wishlist ─────────────────────────────────────────────────────────────
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::get('/wishlist/ids', [WishlistController::class, 'getIds'])->name('wishlist.ids');
    Route::post('/wishlist/clear', [WishlistController::class, 'clearAll'])->name('wishlist.clear');
    Route::get('/frontend/reviews/user-product/{productId}', [ReviewController::class, 'getUserProductReview'])->name('frontend.reviews.user-product');
    Route::post('/frontend/reviews/store', [ReviewController::class, 'store'])->name('frontend.reviews.store');
    Route::delete('/frontend/reviews/{reviewId}', [ReviewController::class, 'destroy'])->name('frontend.reviews.destroy');
});

// Route::get('/dashboard', function () {
//     return view('backend.dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

/** Backend Routes */
Route::group(['middleware' => ['auth', 'check.permission'], 'prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::put('users/change-status', [UserController::class, 'changeStatus'])->name('users.change-status');
    Route::resource('users', UserController::class);
    Route::resource('role', RolesController::class);
    Route::resource('permission', PermissionController::class);

    /** category routes */
    Route::put('category/change-status', [CategoryController::class, 'changeStatus'])->name('category.change-status');
    Route::resource('category', CategoryController::class);

    /** subcategory routes */
    Route::put('subcategory/change-status', [SubCategoryController::class, 'changeStatus'])->name('subcategory.change-status');
    Route::resource('sub-category', SubCategoryController::class);

    /** child category routes */
    Route::controller(ChildCategoryController::class)->group(function () {
        Route::put('child-category/change-status', 'changeStatus')->name('child-category.change-status');
        Route::get('get-subcategories', 'getSubCategories')->name('get-subCategories');
        Route::get('get-child-categories', 'getChildCategories')->name('get-child-categories');
    });
    Route::resource('child-category', ChildCategoryController::class);
    /* brand controller */
    Route::put('brand/change-status', [BrandController::class, 'changeStatus'])->name('brand.change-status');
    Route::resource('brand', BrandController::class);


    /** vendor */
    Route::get('vendor/get-details', [VendorController::class, 'getVendorDetails'])->name('vendor.get-details');
    Route::put('vendor/change-status', [VendorController::class, 'changeStatus'])->name('vendor.change-status');
    Route::resource('vendor', VendorController::class);

    /** Unit Routes */
    Route::put('units/change-status', [UnitController::class, 'changeStatus'])->name('units.change-status');
    Route::resource('units', UnitController::class);

    /** Color Routes */
    Route::put('colors/change-status', [ColorController::class, 'changeStatus'])->name('colors.change-status');
    Route::resource('colors', ColorController::class);

    /** Size Routes */
    Route::put('sizes/change-status', [SizeController::class, 'changeStatus'])->name('sizes.change-status');
    Route::resource('sizes', SizeController::class);

    /** Product Routes */
    Route::put('products/change-status', [ProductController::class, 'changeStatus'])->name('products.change-status');
    Route::get('products/import', [ProductController::class, 'importView'])->name('products.import.view');
    Route::post('products/import/preview', [ProductController::class, 'importPreview'])->name('products.import.preview');
    Route::post('products/import', [ProductController::class, 'importStore'])->name('products.import.store');
    Route::get('products/announcement', [ProductController::class, 'announcementIndex'])->name('products.announcement.index');
    Route::post('products/announcement/send', [ProductController::class, 'sendAnnouncement'])->name('products.announcement.send');
    Route::resource('products', ProductController::class);

    /** Product Type Routes */
    Route::put('product-types/change-status', [ProductTypeController::class, 'changeStatus'])->name('product-types.change-status');
    Route::resource('product-types', ProductTypeController::class);

    /** Booking Routes */
    Route::controller(BookingController::class)->group(function () {
        Route::get('bookings/get-subcategories', 'getSubCategories')->name('bookings.get-subcategories');
        Route::get('bookings/get-childcategories', 'getChildCategories')->name('bookings.get-childcategories');
        Route::get('bookings/view-invoice/{id}', 'viewInvoice')->name('bookings.view-invoice');
        Route::get('bookings/download-pdf/{id}', 'downloadPdf')->name('bookings.download-pdf');
    });
    // New route (Primary)
    Route::put('bookings/status-update', [BookingController::class, 'changeStatus'])->name('bookings.status-update');
    // Legacy route (Fallback to prevent RouteNotFoundException)
    Route::put('bookings/change-status', [BookingController::class, 'changeStatus'])->name('bookings.change-status');

    Route::resource('bookings', BookingController::class);

    /** Purchase Routes */
    Route::get('purchases/get-booking-details', [PurchaseController::class, 'getBookingDetails'])->name('purchases.get-booking-details');
    Route::get('purchases/{id}/invoice', [PurchaseController::class, 'viewInvoice'])->name('purchases.view-invoice');
    Route::get('purchases/{id}/download-pdf', [PurchaseController::class, 'downloadPdf'])->name('purchases.download-pdf');
    Route::post('purchases/{id}/attachments', [PurchaseController::class, 'uploadAttachments'])->name('purchases.upload-attachments');
    Route::delete('purchases/{id}/attachments/{attachmentId}', [PurchaseController::class, 'deleteAttachment'])->name('purchases.delete-attachment');
    Route::resource('purchases', PurchaseController::class);

    /** Frontend Orders (Customer Orders) */
    Route::get('orders', [FrontendOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}/pi-invoice', [FrontendOrderController::class, 'piInvoice'])->name('orders.pi-invoice');
    Route::get('orders/{order}/view-invoice', [FrontendOrderController::class, 'viewInvoice'])->name('orders.view-invoice');
    Route::get('orders/{order}/download-invoice', [FrontendOrderController::class, 'downloadInvoice'])->name('orders.download-invoice');
    Route::get('orders/{order}', [FrontendOrderController::class, 'show'])->name('orders.show');
    Route::put('orders/{order}/status', [FrontendOrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::delete('orders/{order}', [FrontendOrderController::class, 'destroy'])->name('orders.destroy');

    /** Pricing Rules (Multipliers) */
    Route::resource('pricing-rules', PricingRuleController::class);

    /** Tax / VAT Rules */
    Route::put('taxes/change-status', [TaxController::class, 'changeStatus'])->name('taxes.change-status');
    Route::put('taxes/set-default', [TaxController::class, 'setDefault'])->name('taxes.set-default');
    Route::resource('taxes', TaxController::class);

    /** Discount Rules */
    Route::put('discounts/change-status', [DiscountController::class, 'changeStatus'])->name('discounts.change-status');
    Route::put('discounts/set-default', [DiscountController::class, 'setDefault'])->name('discounts.set-default');
    Route::resource('discounts', DiscountController::class);

    /** Report Routes */
    Route::controller(ReportController::class)->group(function () {
        Route::get('reports', 'index')->name('reports.index');
        Route::get('reports/stock', 'stockReport')->name('reports.stock');
        Route::get('reports/purchase', 'purchaseReport')->name('reports.purchase');
        Route::get('reports/product-purchase-history', 'productPurchaseHistory')->name('reports.product-purchase-history');
        Route::get('reports/low-stock', 'lowStockReport')->name('reports.low-stock');
        Route::get('reports/profit-loss', 'profitLossReport')->name('reports.profit-loss');
        Route::get('low-stock-check', 'lowStockCheck')->name('low-stock-check'); // AJAX endpoint
        Route::post('low-stock-mark-read', 'markNotificationsRead')->name('low-stock-mark-read');
        Route::get('notifications/all', 'allNotifications')->name('notifications.all');
    });


    /** Product Request Routes */
    Route::get('product-requests/{id}/view-invoice', [ProductRequestController::class, 'viewInvoice'])->name('product-requests.view-invoice');
    Route::get('product-requests/{id}/invoice', [ProductRequestController::class, 'printPdf'])->name('product-requests.download-invoice');
    Route::put('product-requests/update-status/{id}', [ProductRequestController::class, 'updateStatus'])->name('product-requests.update-status');
    Route::resource('product-requests', ProductRequestController::class);

    /** Custom Product Request Routes */
    Route::put('custom-product-requests/update-status/{id}', [CustomProductRequestController::class, 'updateStatus'])->name('custom-product-requests.update-status');
    Route::resource('custom-product-requests', CustomProductRequestController::class);

    // Settings
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

    /** Inventory Plane Routes */
    Route::get('issues/get-request-items', [IssueController::class, 'getRequestItems'])->name('issues.get-request-items');
    Route::get('issues/{id}/view-invoice', [IssueController::class, 'viewInvoice'])->name('issues.view-invoice');
    Route::get('issues/{id}/invoice', [IssueController::class, 'downloadInvoice'])->name('issues.download-invoice');
    Route::resource('issues', IssueController::class);
    Route::get('stock-ledger', [StockLedgerController::class, 'index'])->name('stock-ledger.index');
    Route::get('inventory-reports/export-pdf', [InventoryReportController::class, 'exportPdf'])->name('inventory-reports.export-pdf');
    Route::get('inventory-reports', [InventoryReportController::class, 'index'])->name('inventory-reports.index');

    /** profile routes */
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'index')->name('profile');
        Route::post('/profile/update', 'updateProfile')->name('profile.update');
        Route::post('/profile/update/password', 'updatePassword')->name('password.update');
    });

    /** Review Routes */
    Route::controller(ReviewController::class)->group(function () {
        Route::get('reviews/user-product/{productId}', 'getUserProductReview')->name('reviews.user-product');
        Route::post('reviews/store', 'store')->name('reviews.store');
        Route::get('reviews/product/{productId}', 'getProductReviews')->name('reviews.product');
        Route::delete('reviews/{reviewId}', 'destroy')->name('reviews.destroy');
        Route::get('reviews', 'index')->name('reviews.index');
    });

    /** Cart Routes (Database Cart System) */
    Route::controller(CartController::class)->group(function () {
        Route::get('cart/count', 'getCount')->name('cart.count');
        Route::get('cart/items', 'getItems')->name('cart.items');
        Route::get('cart/product-ids', 'getProductIds')->name('cart.product-ids');
        Route::get('cart/vendor', 'getVendor')->name('cart.vendor');
        Route::post('cart/add', 'add')->name('cart.add');
        Route::post('cart/remove', 'remove')->name('cart.remove');
        Route::post('cart/clear', 'clear')->name('cart.clear');
    });
    /** Accounts & Payments */
    Route::get('accounts', [\App\Http\Controllers\Backend\AccountController::class, 'index'])->name('accounts.index');
    Route::get('accounts/record-payment', [\App\Http\Controllers\Backend\AccountController::class, 'create'])->name('accounts.record-payment');
    Route::get('accounts/search-order', [\App\Http\Controllers\Backend\AccountController::class, 'searchOrder'])->name('accounts.search-order');
    Route::get('accounts/due-orders', [\App\Http\Controllers\Backend\AccountController::class, 'dueOrders'])->name('accounts.due-orders');
    Route::post('accounts/orders/{order}/payment', [\App\Http\Controllers\Backend\AccountController::class, 'storePayment'])->name('accounts.store-payment');

});

require __DIR__ . '/auth.php';
