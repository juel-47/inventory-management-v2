<style>
    /* Fix for Top Nav Layout: Icon above Text */
    .navbar.main-navbar .navbar-nav {
        flex-direction: row;
        align-items: center; /* Center items vertically in the list */
        display: flex;
        height: 100%; /* Take full height of navbar */
    }
    .navbar.main-navbar .collapse.navbar-collapse {
        align-items: center !important;
        display: flex !important;
    }
    
    /* Ensure right side aligned too */
    .navbar.main-navbar .navbar-right {
        align-items: center;
        display: flex;
        height: 100%;
        margin-left: auto;
    }
    .navbar.main-navbar .navbar-nav .nav-item {
        height: 100%;
        display: flex;
        align-items: center;
    }
    .navbar.main-navbar .navbar-nav .nav-item .nav-link {
        display: flex;
        flex-direction: column;
        justify-content: center; /* Center content vertically within link */
        align-items: center;
        text-align: center;
        height: 100%; /* Full height */
        padding: 0 20px; /* Horizontal padding only, height handled by flex */
        color: #fff !important; /* Default white text */
        font-weight: 600;
        transition: all 0.3s;
        border-radius: 5px;
        min-height: 70px; /* Minimum height to ensure clickability if navbar grows */
    }
    
    /* Active & Open States: White Background, Dark Text */
    .navbar.main-navbar .navbar-nav .nav-item.active .nav-link,
    .navbar.main-navbar .navbar-nav .nav-item.show .nav-link,
    .navbar.main-navbar .navbar-nav .nav-item .nav-link:hover {
        background-color: #fff !important;
        color: #6777ef !important; /* Primary blue color for text */
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .navbar.main-navbar .navbar-nav .nav-item .nav-link i {
        margin-right: 0 !important;
        margin-bottom: 6px;
        font-size: 24px; /* Bigger Icon */
    }
    .navbar.main-navbar .navbar-nav .nav-item .nav-link span {
        font-size: 14px; /* Bigger Text */
        line-height: 1.2;
    }
    
    /* Ensure dropdowns don't conflict */
    .dropdown-menu {
        margin-top: 10px !important;
    }

    /* Mobile Responsive Tweaks */
    @media (max-width: 991.98px) {
        .navbar.main-navbar {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            /* justify-content: space-between !important; */
            padding: 0 15px !important;
        }
        
        .navbar.main-navbar .navbar-nav {
            display: none !important; /* The main items are hidden on mobile anyway by d-none d-lg-flex */
        }
        
        .navbar.main-navbar .navbar-right {
            margin-left: auto !important;
            display: flex !important;
            flex-direction: row !important;
            /* align-items: center !important; */
        }

        .navbar.main-navbar .navbar-right .dropdown {
            margin-left: 10px;
        }
        
        /* Sidebar toggle on the left */
        .navbar.main-navbar [data-toggle="sidebar"] {
            margin-right: auto;
        }
    }

    /* Desktop Centering Fix - V3 */
    @media (min-width: 992px) {
        .navbar.main-navbar {
            display: flex !important;
            /* justify-content: center !important; */
            position: relative;
        }
        
        .navbar.main-navbar .navbar-nav.mx-auto {
            margin-left: 0 !important;
            margin-right: 0 !important;
            display: flex !important;
            /* justify-content: center; */
        }
        
        .navbar.main-navbar .navbar-right {
            position: absolute !important;
            right: -10px;
            top: 0;
            height: 100%;
            display: flex !important;
            /* align-items: center; */
        }
    }
</style>
<nav class="navbar navbar-expand-lg main-navbar">
    <!-- Mobile Hamburger Toggle (Opens Sidebar) -->
    <a href="#" data-toggle="sidebar" class="nav-link nav-link-lg d-lg-none text-white">
        <i class="fas fa-bars" style="font-size: 24px;"></i>
    </a>

    <!-- Top Nav Items: Visible ONLY on Desktop -->
    <ul class="navbar-nav mx-auto d-none d-lg-flex"> <!-- Hidden on mobile, Flex on desktop -->
            <!-- Dashboard -->
            <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <a href="{{ route('admin.dashboard') }}" class="nav-link"><i class="fas fa-fire"></i><span>Dashboard</span></a>
            </li>

            <!-- Categories -->
            @can('Manage Categories')
            <li class="nav-item dropdown {{ setActive(['admin.category.*', 'admin.sub-category.*', 'admin.child-category.*', 'admin.slider.*']) }}">
                <a href="#" data-toggle="dropdown" class="nav-link has-dropdown"><i class="fas fa-list"></i><span>Categories</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.category.*']) }}"><a class="nav-link" href="{{ route('admin.category.index') }}">Category</a></li>
                    <li class="{{ setActive(['admin.sub-category.*']) }}"><a class="nav-link" href="{{ route('admin.sub-category.index') }}">Sub Category</a></li>
                    <li class="{{ setActive(['admin.child-category.*']) }}"><a class="nav-link" href="{{ route('admin.child-category.index') }}">Child Category</a></li>
                    <li class="{{ setActive(['admin.slider.*']) }}"><a class="nav-link" href="{{ route('admin.slider.index') }}">Slider</a></li>
                    <li class="{{ setActive(['admin.product-types.*']) }}"><a class="nav-link"
                                href="{{ route('admin.product-types.index') }}">Occasion Type </a></li>
                </ul>
            </li>
            @endcan

            <!-- Products -->
            @canany(['Manage Products', 'View Product Stock'])
            <li class="nav-item dropdown {{ setActive(['admin.products.*', 'admin.units.*', 'admin.colors.*', 'admin.sizes.*']) }}">
                <a href="#" data-toggle="dropdown" class="nav-link has-dropdown"><i class="fas fa-box"></i><span>Products</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.products.*']) }}"><a class="nav-link" href="{{ route('admin.products.index') }}">All Products</a></li>
                    @can('Manage Products')
                    <li class="{{ setActive(['admin.units.*']) }}"><a class="nav-link" href="{{ route('admin.units.index') }}">Units</a></li>
                    <li class="{{ setActive(['admin.colors.*']) }}"><a class="nav-link" href="{{ route('admin.colors.index') }}">Colors</a></li>
                    <li class="{{ setActive(['admin.sizes.*']) }}"><a class="nav-link" href="{{ route('admin.sizes.index') }}">Sizes</a></li>
                    @endcan
                </ul>
            </li>
            @endcanany

            <!-- Inventory Plane -->
            @can('Manage Inventory')
             <li class="nav-item dropdown {{ setActive(['admin.issues.*', 'admin.stock-ledger.index', 'admin.inventory-reports.index']) }}">
                <a href="#" data-toggle="dropdown" class="nav-link has-dropdown"><i class="fas fa-warehouse"></i><span>Inventory</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.inventory-reports.index']) }}"><a class="nav-link" href="{{ route('admin.inventory-reports.index') }}">Current Stock</a></li>
                    <li class="{{ setActive(['admin.issues.index']) }}"><a class="nav-link" href="{{ route('admin.issues.index') }}">Stock Issues</a></li>
                    <li class="{{ setActive(['admin.stock-ledger.index']) }}"><a class="nav-link" href="{{ route('admin.stock-ledger.index') }}">Stock Ledger</a></li>
                </ul>
            </li>
            @endcan  

             <!-- Order Place -->
            @can('Manage Order Place')
            <li class="nav-item dropdown {{ setActive(['admin.bookings.*', 'admin.orders.*', 'admin.product-requests.*']) }}">
                <a href="#" data-toggle="dropdown" class="nav-link has-dropdown"><i class="fas fa-book"></i><span>Order Place</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.bookings.*']) }}"><a class="nav-link" href="{{ route('admin.bookings.index') }}">All Order Place</a></li>
                    <li class="{{ setActive(['admin.orders.*']) }}"><a class="nav-link" href="{{ route('admin.orders.index') }}">Outlet/Shop Orders</a></li>
                    @if(Auth::user()->hasRole('Admin'))
                    <li class="{{ setActive(['admin.product-requests.index']) }}"><a class="nav-link" href="{{ route('admin.product-requests.index') }}">All Requests</a></li>
                    <li class="{{ setActive(['admin.product-requests.create']) }}"><a class="nav-link" href="{{ route('admin.product-requests.create') }}">Create Request</a></li>
                    @endif
                </ul>
            </li>
            @endcan

            <!-- Purchases -->
            @can('Manage Order Receive')
            <li class="nav-item dropdown {{ setActive(['admin.purchases.*']) }}">
                <a href="#" data-toggle="dropdown" class="nav-link has-dropdown"><i class="fas fa-shopping-cart"></i><span>Order Receive</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.purchases.index']) }}"><a class="nav-link" href="{{ route('admin.purchases.index') }}">All Order Receive</a></li>
                    <li class="{{ setActive(['admin.purchases.create']) }}"><a class="nav-link" href="{{ route('admin.purchases.create') }}">Create New</a></li>
                </ul>
            </li>
            @endcan
            

             {{-- @canany(['Manage Product Requests', 'Create Product Requests', 'View Product Requests'])
             <!-- Product Requests -->
             <li class="nav-item dropdown {{ setActive(['admin.product-requests.*']) }}">
                <a href="#" data-toggle="dropdown" class="nav-link has-dropdown"><i class="fas fa-box-open"></i><span>{{ Auth::user()->can('Manage Product Requests') ? 'Outlet Request' : 'Product Request' }}</span></a>
                <ul class="dropdown-menu">
                    @canany(['Manage Product Requests', 'View Product Requests'])
                    <li class="{{ setActive(['admin.product-requests.index']) }}"><a class="nav-link" href="{{ route('admin.product-requests.index') }}">All {{ Auth::user()->can('Manage Product Requests') ? 'Requests' : 'Product Requests' }}</a></li>
                    @endcanany

                    @can('Create Product Requests')
                    <li class="{{ setActive(['admin.product-requests.create']) }}"><a class="nav-link" href="{{ route('admin.product-requests.create') }}">Create New</a></li>
                    @endcan

                    @canany(['Manage Custom Product Requests', 'View Custom Product Requests'])
                    <li class="divider"></li>
                    <li class="{{ setActive(['admin.custom-product-requests.index']) }}"><a class="nav-link" href="{{ route('admin.custom-product-requests.index') }}">All Custom Requests</a></li>
                    @endcanany

                    @can('Create Custom Product Requests')
                    <li class="divider"></li>
                    <li class="{{ setActive(['admin.custom-product-requests.create']) }}"><a class="nav-link" href="{{ route('admin.custom-product-requests.create') }}">Request New Product</a></li>
                    @endcan
                </ul>
            </li>
            @endcanany --}}

             <!-- Reports -->
             @can('Manage Reports')
             <li class="nav-item dropdown {{ setActive(['admin.reports.*']) }}">
                 <a href="#" data-toggle="dropdown" class="nav-link has-dropdown"><i class="fas fa-chart-line"></i><span>Reports</span></a>
                 <ul class="dropdown-menu">
                     <li class="{{ setActive(['admin.reports.index']) }}"><a class="nav-link" href="{{ route('admin.reports.index') }}">All Reports</a></li>
                     <li class="{{ setActive(['admin.reports.stock']) }}"><a class="nav-link" href="{{ route('admin.reports.stock') }}">Stock Reports</a></li>
                     <li class="{{ setActive(['admin.reports.purchase']) }}"><a class="nav-link" href="{{ route('admin.reports.purchase') }}">Purchase History</a></li>
                     <li class="{{ setActive(['admin.reports.product-purchase-history']) }}"><a class="nav-link" href="{{ route('admin.reports.product-purchase-history') }}">Product Tracking</a></li>
                     <li class="{{ setActive(['admin.reports.low-stock']) }}"><a class="nav-link" href="{{ route('admin.reports.low-stock') }}">Low Stock Alert</a></li>
                     <li class="{{ setActive(['admin.reports.profit-loss']) }}"><a class="nav-link" href="{{ route('admin.reports.profit-loss') }}">Profit & Loss</a></li>
                 </ul>
             </li>
             @endcan
 
             <!-- Accounts -->
             <li class="nav-item dropdown {{ setActive(['admin.accounts.*']) }}">
                 <a href="#" data-toggle="dropdown" class="nav-link has-dropdown"><i class="fas fa-file-invoice-dollar"></i><span>Accounts</span></a>
                 <ul class="dropdown-menu">
                     <li class="{{ setActive(['admin.accounts.index']) }}"><a class="nav-link" href="{{ route('admin.accounts.index') }}">Transaction History</a></li>
                     <li class="{{ setActive(['admin.accounts.record-payment']) }}"><a class="nav-link" href="{{ route('admin.accounts.record-payment') }}">Record Payment</a></li>
                     <li class="{{ setActive(['admin.accounts.due-orders']) }}"><a class="nav-link" href="{{ route('admin.accounts.due-orders') }}">Due Orders</a></li>
                 </ul>
             </li>

            <!-- More (Brands, Vendors, Settings) -->
        <!-- Brands -->
        @can('Manage Brands')
        <li class="nav-item dropdown {{ setActive(['admin.brand.*']) }}">
            <a href="#" data-toggle="dropdown" class="nav-link has-dropdown"><i class="fas fa-tag"></i><span>Brands</span></a>
            <ul class="dropdown-menu">
                <li class="{{ setActive(['admin.brand.index']) }}"><a class="nav-link" href="{{ route('admin.brand.index') }}">All Brands</a></li>
                <li class="{{ setActive(['admin.brand.create']) }}"><a class="nav-link" href="{{ route('admin.brand.create') }}">Add Brand</a></li>
            </ul>
        </li>
        @endcan

        <!-- Vendors -->
        @can('Manage Vendors')
        <li class="nav-item dropdown {{ setActive(['admin.vendor.*']) }}">
            <a href="#" data-toggle="dropdown" class="nav-link has-dropdown"><i class="fas fa-truck"></i><span>Vendors</span></a>
            <ul class="dropdown-menu">
                <li class="{{ setActive(['admin.vendor.index']) }}"><a class="nav-link" href="{{ route('admin.vendor.index') }}">All Vendors</a></li>
                <li class="{{ setActive(['admin.vendor.create']) }}"><a class="nav-link" href="{{ route('admin.vendor.create') }}">Add Vendor</a></li>
            </ul>
        </li>
        @endcan

        <!-- System -->
        @can('Administration')
         <li class="nav-item dropdown {{ setActive(['admin.permission.*', 'admin.role.*', 'admin.users.*', 'admin.settings.*', 'admin.pricing-rules.*', 'admin.taxes.*', 'admin.discounts.*', 'admin.products.announcement.*']) }}">
            <a href="#" data-toggle="dropdown" class="nav-link has-dropdown"><i class="fas fa-cogs"></i><span>System</span></a>
            <ul class="dropdown-menu">
            <li class="{{ setActive(['admin.users.*']) }}"><a class="nav-link" href="{{ route('admin.users.index') }}">Users</a></li>
            <li class="{{ setActive(['admin.permission.*']) }}"><a class="nav-link" href="{{ route('admin.permission.index') }}">Permissions</a></li>
            <li class="{{ setActive(['admin.role.*']) }}"><a class="nav-link" href="{{ route('admin.role.index') }}">Roles</a></li>
            <li class="{{ setActive(['admin.pricing-rules.*']) }}"><a class="nav-link" href="{{ route('admin.pricing-rules.index') }}">Pricing Rules</a></li>
            <li class="{{ setActive(['admin.taxes.*']) }}"><a class="nav-link" href="{{ route('admin.taxes.index') }}">Tax / VAT</a></li>
            <li class="{{ setActive(['admin.discounts.*']) }}"><a class="nav-link" href="{{ route('admin.discounts.index') }}">Discount</a></li>
            <li class="{{ setActive(['admin.products.announcement.*']) }}"><a class="nav-link" href="{{ route('admin.products.announcement.index') }}">Product Announcement</a></li>
            <li class="{{ setActive(['admin.settings.*']) }}"><a class="nav-link" href="{{ route('admin.settings.index') }}">Settings</a></li>
            </ul>
        </li>
        @endcan
        </ul>

    <ul class="navbar-nav navbar-right">
      @can('Manage Notification')
      <li class="dropdown dropdown-list-toggle">
        <a href="#" data-toggle="dropdown" class="nav-link notification-toggle nav-link-lg" id="low-stock-count-toggle" style="position: relative; padding: 0 15px;">
            <i class="fas fa-bell" style="font-size: 26px;"></i>
            <span class="badge badge-danger" id="low-stock-count-badge" 
                  style="display: none; position: absolute; top: 2px; right: 2px; font-size: 10px; font-weight: 800; min-width: 18px; height: 18px; padding: 0 5px; border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.2); border: 2px solid #fff;">
                0
            </span>
        </a>
        <div class="dropdown-menu dropdown-list dropdown-menu-right" style="width: 350px; border-radius: 8px; border: none; box-shadow: 0 10px 40px 0 rgba(0,0,0,.1);">
            <div class="dropdown-header" style="font-size: 15px; padding: 15px 20px; background: #fff; border-bottom: 1px solid #f9f9f9; color: #34395e; font-weight: 700;">
                Notifications
                <div class="float-right">
                    <a href="javascript:void(0)" onclick="markAllAsRead()" style="text-transform: none; font-weight: 600; font-size: 12px; color: #6777ef;">Mark all as read</a>
                </div>
            </div>
            <div class="dropdown-list-content dropdown-list-icons" id="low-stock-list" style="overflow-y: auto; max-height: 400px;">
                <!-- Dynamic Items will be injected here -->
                <div class="dropdown-item dropdown-item-unread text-center py-4 text-muted">
                    No new notifications
                </div>
            </div>
            <div class="dropdown-footer text-center" style="padding: 10px 0; border-top: 1px solid #f9f9f9; background: #fff; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                <a href="{{ route('admin.notifications.all') }}" style="font-size: 13px; font-weight: 600; text-decoration: none; color: #6777ef;">View All Notifications <i class="fas fa-chevron-right ml-1"></i></a>
            </div>
        </div>
      </li>
      @endcan
      <li class="dropdown">
        <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
        <img alt="image" height="30px" src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}" class="rounded-circle mr-1">
        {{-- <div class="d-sm-none d-lg-inline-block">Hi, {{ Auth::user()->name }} </div> --}}
    </a>
        <div class="dropdown-menu dropdown-menu-right">
          <a href="{{ route('admin.profile') }}" class="dropdown-item has-icon">
            <i class="far fa-user"></i> Profile
          </a>
          @can('Administration')
          <a href="{{ route('admin.settings.index') }}" class="dropdown-item has-icon">
            <i class="fas fa-cogs"></i> Settings
          </a>
          @endcan
          <div class="dropdown-divider"></div>
           <!-- Authentication -->
           <form method="POST" action="{{ route('logout') }}">
            @csrf
            <a href="{{ route('logout') }}" onclick="event.preventDefault();
            this.closest('form').submit();" class="dropdown-item has-icon text-danger">
              <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </form>
        </div>
      </li>
    </ul>
  </nav>


