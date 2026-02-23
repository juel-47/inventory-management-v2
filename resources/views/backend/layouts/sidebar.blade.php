<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="{{ route('admin.dashboard') }}">{{ Auth::user()->name }}</a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="{{ route('admin.dashboard') }}">{{ substr(Auth::user()->name, 0, 2) }}</a>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-header">Dashboard</li>
            <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <a href="{{ route('admin.dashboard') }}" class="nav-link"><i class="fas fa-fire"></i><span>Dashboard</span></a>
            </li>

            @canany(['Manage Categories', 'Manage Products', 'Manage Order Place', 'Manage Order Receive'])
                <li class="menu-header">E-Commerce</li>
            @endcanany

            @can('Manage Categories')
                <li
                    class="dropdown {{ setActive(['admin.category.*', 'admin.sub-category.*', 'admin.child-category.*']) }}">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-list"></i>
                        <span>Manage Categories</span></a>
                    <ul class="dropdown-menu">
                        <li class="{{ setActive(['admin.category.*']) }}"><a class="nav-link"
                                href="{{ route('admin.category.index') }}"><i class="fas fa-folder"></i> Category </a></li>
                        <li class="{{ setActive(['admin.sub-category.*']) }}"><a class="nav-link"
                                href="{{ route('admin.sub-category.index') }}"><i class="fas fa-folder-open"></i> Sub Category </a></li>
                        <li class="{{ setActive(['admin.child-category.*']) }}"><a class="nav-link"
                                href="{{ route('admin.child-category.index') }}"><i class="fas fa-level-down-alt"></i> Child Category </a></li>
                        <li class="{{ setActive(['admin.product-types.*']) }}"><a class="nav-link"
                                href="{{ route('admin.product-types.index') }}"><i class="fas fa-layer-group"></i> Occasion Type </a></li>
                    </ul>
                </li>
            @endcan

            @canany(['Manage Products', 'View Product Stock'])
             <li class="menu-header">Products</li>
                <li class="dropdown {{ setActive(['admin.products.*', 'admin.units.*', 'admin.colors.*', 'admin.sizes.*']) }}">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-box"></i>
                        <span>Manage Products</span></a>
                    <ul class="dropdown-menu">
                        <li class="{{ setActive(['admin.products.*']) }}"><a class="nav-link" href="{{ route('admin.products.index') }}"><i class="fas fa-boxes"></i> Products</a></li>
                        @can('Manage Products')
                        <li class="{{ setActive(['admin.units.*']) }}"><a class="nav-link" href="{{ route('admin.units.index') }}"><i class="fas fa-balance-scale"></i> Units</a></li>
                        <li class="{{ setActive(['admin.colors.*']) }}"><a class="nav-link" href="{{ route('admin.colors.index') }}"><i class="fas fa-palette"></i> Colors</a></li>
                        <li class="{{ setActive(['admin.sizes.*']) }}"><a class="nav-link" href="{{ route('admin.sizes.index') }}"><i class="fas fa-ruler"></i> Sizes</a></li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            @canany(['Manage Order Place', 'Manage Order Receive'])
            @can('Manage Order Place')
             <li class="menu-header">Order Place</li>
             <li class="dropdown {{ setActive(['admin.bookings.*']) }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-book"></i>
                    <span>Manage Order Place</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.bookings.*']) }}"><a class="nav-link" href="{{ route('admin.bookings.index') }}"><i class="fas fa-calendar-check"></i> Order Place</a></li>
                </ul>
            </li>
            @endcan

            @can('Manage Order Receive')
            <li class="menu-header">Order Receive</li>
            <li class="dropdown {{ setActive(['admin.purchases.*']) }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-shopping-cart"></i>
                    <span>Manage Order Receive</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.purchases.index']) }}"><a class="nav-link" href="{{ route('admin.purchases.index') }}"><i class="fas fa-receipt"></i> Order Receive</a></li>
                    <li class="{{ setActive(['admin.purchases.create']) }}"><a class="nav-link" href="{{ route('admin.purchases.create') }}"><i class="fas fa-plus"></i> Create New</a></li>
                </ul>
            </li>
            @endcan
            @endcanany

            @canany(['Manage Product Requests', 'Create Product Requests', 'View Product Requests'])
            <li class="menu-header">{{ Auth::user()->can('Manage Product Requests') ? 'Outlet Request' : 'Product Request' }}</li>
            <li class="dropdown {{ setActive(['admin.product-requests.*', 'admin.custom-product-requests.*']) }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-box-open"></i>
                    <span>{{ Auth::user()->can('Manage Product Requests') ? 'Outlet Request' : 'Product Request' }}</span></a>
                <ul class="dropdown-menu">
                    @canany(['Manage Product Requests', 'View Product Requests'])
                    <li class="{{ setActive(['admin.product-requests.index']) }}"><a class="nav-link" href="{{ route('admin.product-requests.index') }}"><i class="fas fa-clipboard-list"></i> All {{ Auth::user()->can('Manage Product Requests') ? 'Requests' : 'Product Requests' }}</a></li>
                    @endcanany
                    
                    @can('Create Product Requests')
                    <li class="{{ setActive(['admin.product-requests.create']) }}"><a class="nav-link" href="{{ route('admin.product-requests.create') }}"><i class="fas fa-plus"></i> Create New</a></li>
                    @endcan

                    @canany(['Manage Custom Product Requests', 'View Custom Product Requests'])
                    <li class="divider"></li>
                    <li class="{{ setActive(['admin.custom-product-requests.index']) }}"><a class="nav-link" href="{{ route('admin.custom-product-requests.index') }}"><i class="fas fa-list-alt"></i> All Custom Requests</a></li>
                    @endcanany

                    @can('Create Custom Product Requests')
                    @if(!Auth::user()->hasRole('Admin'))
                    <li class="divider"></li>
                    <li class="{{ setActive(['admin.custom-product-requests.create']) }}"><a class="nav-link" href="{{ route('admin.custom-product-requests.create') }}"><i class="fas fa-plus-circle"></i> Request New Product</a></li>
                    @endif
                    @endcan
                </ul>
            </li>
            @endcanany



            @can('Manage Reports')
            <li class="menu-header">Reports</li>
            <li class="dropdown {{ setActive(['admin.reports.*']) }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-chart-line"></i>
                    <span>Reports</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.reports.index']) }}"><a class="nav-link" href="{{ route('admin.reports.index') }}"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li class="{{ setActive(['admin.reports.stock']) }}"><a class="nav-link" href="{{ route('admin.reports.stock') }}"><i class="fas fa-boxes"></i> Stock Valuation</a></li>
                    <li class="{{ setActive(['admin.reports.purchase']) }}"><a class="nav-link" href="{{ route('admin.reports.purchase') }}"><i class="fas fa-history"></i> Purchase History</a></li>
                    <li class="{{ setActive(['admin.reports.product-purchase-history']) }}"><a class="nav-link" href="{{ route('admin.reports.product-purchase-history') }}"><i class="fas fa-map-marker-alt"></i> Product Tracking</a></li>
                    <li class="{{ setActive(['admin.reports.low-stock']) }}"><a class="nav-link" href="{{ route('admin.reports.low-stock') }}"><i class="fas fa-exclamation-triangle"></i> Low Stock Alert</a></li>
                    <li class="{{ setActive(['admin.reports.profit-loss']) }}"><a class="nav-link" href="{{ route('admin.reports.profit-loss') }}"><i class="fas fa-chart-bar"></i> Profit & Loss</a></li>
                </ul>
            </li>
            @endcan

            @canany(['Manage Brands', 'Manage Vendors'])
            <li class="menu-header">Brands & Vendors</li>
            @can('Manage Brands')
            <li class="dropdown {{ setActive(['admin.brand.*']) }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-tag"></i> <span>Brands</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.brand.index']) }}"><a class="nav-link" href="{{ route('admin.brand.index') }}">All Brands</a></li>
                    <li class="{{ setActive(['admin.brand.create']) }}"><a class="nav-link" href="{{ route('admin.brand.create') }}">Add Brand</a></li>
                </ul>
            </li>
            @endcan

            @can('Manage Vendors')
            <li class="dropdown {{ setActive(['admin.vendor.*']) }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-truck"></i> <span>Vendors</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ setActive(['admin.vendor.index']) }}"><a class="nav-link" href="{{ route('admin.vendor.index') }}">All Vendors</a></li>
                    <li class="{{ setActive(['admin.vendor.create']) }}"><a class="nav-link" href="{{ route('admin.vendor.create') }}">Add Vendor</a></li>
                </ul>
            </li>
            @endcan
            @endcanany


            @can('Manage Inventory')
            <li class="menu-header">Inventory System</li>
            <li class="dropdown {{ setActive(['admin.issues.*', 'admin.reports.stock', 'admin.stock-ledger.index', 'admin.inventory-reports.index']) }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-warehouse"></i>
                    <span>Inventory Plane</span></a>
                <ul class="dropdown-menu">
                     <li class="{{ setActive(['admin.inventory-reports.index']) }}"><a class="nav-link" href="{{ route('admin.inventory-reports.index') }}"><i class="fas fa-boxes"></i> Current Stock</a></li>
                     <li class="{{ setActive(['admin.issues.index']) }}"><a class="nav-link" href="{{ route('admin.issues.index') }}"><i class="fas fa-dolly"></i> Stock Issues</a></li>
                     <li class="{{ setActive(['admin.stock-ledger.index']) }}"><a class="nav-link" href="{{ route('admin.stock-ledger.index') }}"><i class="fas fa-history"></i> Stock Ledger</a></li>
                </ul>
            </li>
            @endcan

            @can('Administration')
            <li class="menu-header">System</li>
            <li class="dropdown {{ setActive(['admin.permission.*', 'admin.role.*', 'admin.users.*', 'admin.settings.*', 'admin.pricing-rules.*']) }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-cogs"></i> <span>Administration</span></a>
                <ul class="dropdown-menu">
                     <li class="{{ setActive(['admin.users.*']) }}"><a class="nav-link" href="{{ route('admin.users.index') }}">Users</a></li>
                     <li class="{{ setActive(['admin.permission.*']) }}"><a class="nav-link" href="{{ route('admin.permission.index') }}">Permissions</a></li>
                     <li class="{{ setActive(['admin.role.*']) }}"><a class="nav-link" href="{{ route('admin.role.index') }}">Roles</a></li>
                     <li class="{{ setActive(['admin.pricing-rules.*']) }}"><a class="nav-link" href="{{ route('admin.pricing-rules.index') }}">Pricing Rules</a></li>
                     <li class="{{ setActive(['admin.settings.*']) }}"><a class="nav-link" href="{{ route('admin.settings.index') }}">Settings</a></li>
                </ul>
            </li>
            @endcan


        </ul>
    </aside>
</div>
