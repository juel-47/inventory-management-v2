<style>
/* ========================================
   PREMIUM ADMIN NAVBAR - PROFESSIONAL DESIGN
   ======================================== */

/* -------- ROOT VARIABLES -------- */
:root {
  --navbar-bg: #22c55e;
  --navbar-bg-gradient: linear-gradient(135deg, #0f1724 0%, #1a2332 50%, #0f1724 100%);
  --primary-color: #4f8cff;
  --primary-hover: #6b9fff;
  --text-color: rgba(255, 255, 255, 0.85);
  --text-muted: rgba(255, 255, 255, 0.5);
  --border-color: rgba(255, 255, 255, 0.06);
  --shadow-color: rgba(79, 140, 255, 0.15);
  --transition-speed: 0.3s;
}

/* -------- BASE NAVBAR -------- */
.navbar.main-navbar {
  background: var(--navbar-bg-gradient);
  padding: 0 32px;
  min-height: 72px;
  border-bottom: 1px solid var(--border-color);
  box-shadow: 0 2px 40px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.05);
  position: sticky;
  top: 0;
  z-index: 1030;
  backdrop-filter: blur(10px);
  transition: all var(--transition-speed) ease;
}

/* -------- BRAND / LOGO -------- */
.navbar.main-navbar .navbar-brand {
  color: #ffffff !important;
  font-weight: 700;
  font-size: 20px;
  letter-spacing: -0.3px;
  padding: 0;
  display: flex;
  align-items: center;
  gap: 10px;
}

.navbar.main-navbar .navbar-brand .brand-icon {
  width: 38px;
  height: 38px;
  background: linear-gradient(135deg, #4f8cff, #6c5ce7);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  color: #fff;
  box-shadow: 0 4px 15px rgba(79, 140, 255, 0.3);
}

.navbar.main-navbar .navbar-brand .brand-text {
  background: linear-gradient(135deg, #ffffff 60%, #a8b8d8);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  font-weight: 700;
}

.navbar.main-navbar .navbar-brand .brand-badge {
  background: rgba(79, 140, 255, 0.2);
  color: #4f8cff;
  font-size: 9px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 20px;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  border: 1px solid rgba(79, 140, 255, 0.2);
}

/* -------- NAVIGATION CONTAINER -------- */
.navbar.main-navbar .navbar-nav {
  display: flex;
  align-items: center;
  gap: 4px;
  height: 100%;
}

/* -------- NAV ITEMS -------- */
.navbar.main-navbar .navbar-nav .nav-item {
  height: 100%;
  display: flex;
  align-items: center;
  position: relative;
}

/* -------- NAV LINKS -------- */
.navbar.main-navbar .navbar-nav .nav-item .nav-link {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 0 16px;
  height: 72px;
  color: var(--text-muted) !important;
  font-weight: 500;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  border-radius: 8px;
  gap: 2px;
  min-width: 60px;
}

/* Link Icons */
.navbar.main-navbar .navbar-nav .nav-item .nav-link i {
  font-size: 18px;
  transition: all var(--transition-speed) ease;
  color: var(--text-muted);
  margin-bottom: 2px;
}

/* Link Text */
.navbar.main-navbar .navbar-nav .nav-item .nav-link span {
  font-size: 10px;
  line-height: 1.2;
  font-weight: 600;
  letter-spacing: 0.3px;
  white-space: nowrap;
  opacity: 0.8;
}

/* -------- HOVER STATE -------- */
.navbar.main-navbar .navbar-nav .nav-item .nav-link:hover {
  color: #ffffff !important;
  background: rgba(255, 255, 255, 0.06);
  transform: translateY(-1px);
}

.navbar.main-navbar .navbar-nav .nav-item .nav-link:hover i {
  color: var(--primary-color);
  transform: scale(1.1);
}

.navbar.main-navbar .navbar-nav .nav-item .nav-link:hover span {
  opacity: 1;
}

/* -------- ACTIVE STATE -------- */
.navbar.main-navbar .navbar-nav .nav-item.active .nav-link {
  color: #ffffff !important;
  background: rgba(79, 140, 255, 0.12);
  box-shadow: inset 0 0 0 1px rgba(79, 140, 255, 0.15);
}

.navbar.main-navbar .navbar-nav .nav-item.active .nav-link i {
  color: var(--primary-color);
}

.navbar.main-navbar .navbar-nav .nav-item.active .nav-link::before {
  content: '';
  position: absolute;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 30px;
  height: 3px;
  background: linear-gradient(90deg, #4f8cff, #6c5ce7);
  border-radius: 0 0 4px 4px;
  box-shadow: 0 2px 10px rgba(79, 140, 255, 0.4);
}

/* -------- DROPDOWN TOGGLE -------- */
.navbar.main-navbar .navbar-nav .nav-item .nav-link.has-dropdown::after {
  display: none;
}

.navbar.main-navbar .navbar-nav .nav-item .nav-link.has-dropdown .dropdown-arrow {
  font-size: 8px;
  margin-left: 4px;
  opacity: 0.5;
  transition: all var(--transition-speed) ease;
}

.navbar.main-navbar .navbar-nav .nav-item .nav-link.has-dropdown:hover .dropdown-arrow {
  opacity: 1;
  transform: rotate(180deg);
}

/* -------- DROPDOWN MENU -------- */
.navbar.main-navbar .dropdown-menu {
  background: #1a2332;
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 12px;
  padding: 8px;
  margin-top: 8px !important;
  min-width: 240px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.05);
  animation: dropdownFade 0.25s ease;
}

@keyframes dropdownFade {
  from {
    opacity: 0;
    transform: translateY(-8px) scale(0.98);
  }

  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.navbar.main-navbar .dropdown-menu .dropdown-item {
  padding: 10px 14px;
  color: rgba(255, 255, 255, 0.8) !important;
  font-size: 13px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 12px;
  border-radius: 8px;
  transition: all 0.2s ease;
  cursor: pointer;
}

.navbar.main-navbar .dropdown-menu .dropdown-item i {
  font-size: 15px;
  width: 20px;
  color: var(--text-muted);
  transition: all 0.2s ease;
}

.navbar.main-navbar .dropdown-menu .dropdown-item:hover {
  background: rgba(79, 140, 255, 0.12) !important;
  color: #ffffff !important;
  transform: translateX(4px);
}

.navbar.main-navbar .dropdown-menu .dropdown-item:hover i {
  color: var(--primary-color);
}

.navbar.main-navbar .dropdown-menu .dropdown-divider {
  margin: 6px 8px;
  border-color: rgba(255, 255, 255, 0.06);
}

.navbar.main-navbar .dropdown-menu .dropdown-header {
  color: var(--text-muted);
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 1px;
  padding: 8px 14px 4px;
  font-weight: 700;
}

/* -------- RIGHT SECTION -------- */
.navbar.main-navbar .navbar-right {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-left: auto;
  height: 100%;
}

/* -------- NOTIFICATION -------- */
.navbar.main-navbar .navbar-right .notification-toggle {
  position: relative;
  padding: 0;
  width: 42px;
  height: 42px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all var(--transition-speed) ease;
  color: var(--text-muted) !important;
  background: transparent;
  border: none;
}

.navbar.main-navbar .navbar-right .notification-toggle:hover {
  background: rgba(255, 255, 255, 0.06);
  color: #ffffff !important;
  transform: scale(1.05);
}

.navbar.main-navbar .navbar-right .notification-toggle i {
  font-size: 20px;
}

.navbar.main-navbar .navbar-right .notification-toggle .badge {
  position: absolute;
  top: 4px;
  right: 4px;
  background: linear-gradient(135deg, #ff6b6b, #ee5a24);
  color: #fff;
  font-size: 9px;
  font-weight: 700;
  min-width: 18px;
  height: 18px;
  padding: 0 5px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid var(--navbar-bg);
  box-shadow: 0 2px 10px rgba(255, 107, 107, 0.4);
}

/* -------- PROFILE -------- */
.navbar.main-navbar .navbar-right .profile-toggle {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 4px 12px 4px 4px;
  border-radius: 50px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.06);
  transition: all var(--transition-speed) ease;
  cursor: pointer;
  text-decoration: none;
  color: var(--text-color) !important;
}

.navbar.main-navbar .navbar-right .profile-toggle:hover {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(255, 255, 255, 0.12);
  transform: translateY(-1px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
}

.navbar.main-navbar .navbar-right .profile-toggle .avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  border: 2px solid rgba(79, 140, 255, 0.3);
  object-fit: cover;
  transition: all var(--transition-speed) ease;
}

.navbar.main-navbar .navbar-right .profile-toggle:hover .avatar {
  border-color: var(--primary-color);
  box-shadow: 0 0 20px rgba(79, 140, 255, 0.2);
}

.navbar.main-navbar .navbar-right .profile-toggle .profile-info {
  display: flex;
  flex-direction: column;
  line-height: 1.2;
}

.navbar.main-navbar .navbar-right .profile-toggle .profile-info .name {
  font-size: 13px;
  font-weight: 600;
  color: #ffffff;
}

.navbar.main-navbar .navbar-right .profile-toggle .profile-info .role {
  font-size: 10px;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.navbar.main-navbar .navbar-right .profile-toggle .chevron {
  font-size: 12px;
  color: var(--text-muted);
  transition: all var(--transition-speed) ease;
  margin-left: 4px;
}

.navbar.main-navbar .navbar-right .profile-toggle:hover .chevron {
  color: #ffffff;
  transform: rotate(180deg);
}

/* -------- MOBILE HAMBURGER -------- */
.navbar.main-navbar .mobile-toggle {
  color: #ffffff !important;
  padding: 8px 12px;
  border-radius: 8px;
  transition: all var(--transition-speed) ease;
  font-size: 22px;
  background: transparent;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
}

.navbar.main-navbar .mobile-toggle:hover {
  background: rgba(255, 255, 255, 0.06);
}

/* -------- SEARCH BAR (Optional) -------- */
.navbar.main-navbar .search-wrapper {
  position: relative;
  margin-right: 8px;
}

.navbar.main-navbar .search-wrapper input {
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 50px;
  padding: 8px 16px 8px 38px;
  color: #ffffff;
  font-size: 13px;
  width: 200px;
  transition: all var(--transition-speed) ease;
}

.navbar.main-navbar .search-wrapper input::placeholder {
  color: var(--text-muted);
  font-size: 12px;
}

.navbar.main-navbar .search-wrapper input:focus {
  outline: none;
  background: rgba(255, 255, 255, 0.08);
  border-color: var(--primary-color);
  width: 240px;
  box-shadow: 0 0 30px rgba(79, 140, 255, 0.05);
}

.navbar.main-navbar .search-wrapper i {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-muted);
  font-size: 14px;
}

/* ========================================
   RESPONSIVE DESIGN
   ======================================== */

@media (max-width: 1200px) {
  .navbar.main-navbar .navbar-nav .nav-item .nav-link {
    padding: 0 12px;
    min-width: 50px;
  }

  .navbar.main-navbar .navbar-nav .nav-item .nav-link span {
    font-size: 9px;
  }

  .navbar.main-navbar .navbar-nav .nav-item .nav-link i {
    font-size: 16px;
  }

  .navbar.main-navbar .search-wrapper input {
    width: 150px;
  }

  .navbar.main-navbar .search-wrapper input:focus {
    width: 180px;
  }
}

@media (max-width: 991.98px) {
  .navbar.main-navbar {
    padding: 0 16px;
    min-height: 64px;
  }

  .navbar.main-navbar .navbar-nav.mx-auto {
    display: none !important;
  }

  .navbar.main-navbar .navbar-right {
    gap: 4px;
  }

  .navbar.main-navbar .navbar-right .profile-toggle .profile-info {
    display: none;
  }

  .navbar.main-navbar .navbar-right .profile-toggle .chevron {
    display: none;
  }

  .navbar.main-navbar .navbar-right .profile-toggle {
    padding: 4px;
  }

  .navbar.main-navbar .navbar-right .profile-toggle .avatar {
    width: 32px;
    height: 32px;
  }

  .navbar.main-navbar .navbar-right .notification-toggle {
    width: 38px;
    height: 38px;
  }

  .navbar.main-navbar .navbar-right .notification-toggle i {
    font-size: 18px;
  }

  .navbar.main-navbar .search-wrapper {
    display: none;
  }

  .navbar.main-navbar .navbar-brand .brand-badge {
    display: none;
  }
}

@media (max-width: 575.98px) {
  .navbar.main-navbar {
    padding: 0 12px;
    min-height: 56px;
  }

  .navbar.main-navbar .navbar-brand {
    font-size: 16px;
  }

  .navbar.main-navbar .navbar-brand .brand-icon {
    width: 32px;
    height: 32px;
    font-size: 14px;
  }

  .navbar.main-navbar .navbar-right .notification-toggle {
    width: 34px;
    height: 34px;
  }

  .navbar.main-navbar .navbar-right .notification-toggle i {
    font-size: 16px;
  }

  .navbar.main-navbar .navbar-right .profile-toggle .avatar {
    width: 28px;
    height: 28px;
  }
}

/* ========================================
   DARK THEME COMPATIBILITY
   ======================================== */

@media (prefers-color-scheme: dark) {
  :root {
    --navbar-bg: #0a0e17;
    --text-color: rgba(255, 255, 255, 0.9);
    --text-muted: rgba(255, 255, 255, 0.4);
  }
}

/* ========================================
   UTILITY CLASSES
   ======================================== */

.navbar.main-navbar .divider-vertical {
  width: 1px;
  height: 30px;
  background: rgba(255, 255, 255, 0.06);
  margin: 0 8px;
}

/* Scrollbar Styling for Dropdown */
.navbar.main-navbar .dropdown-list-content::-webkit-scrollbar {
  width: 4px;
}

.navbar.main-navbar .dropdown-list-content::-webkit-scrollbar-track {
  background: transparent;
}

.navbar.main-navbar .dropdown-list-content::-webkit-scrollbar-thumb {
  background: rgba(79, 140, 255, 0.3);
  border-radius: 10px;
}

.navbar.main-navbar .dropdown-list-content::-webkit-scrollbar-thumb:hover {
  background: rgba(79, 140, 255, 0.5);
}
</style>

<!-- ========================================
   NAVBAR HTML
   ======================================== -->
<nav class="navbar navbar-expand-lg main-navbar">
  <!-- Mobile Toggle -->
  <button class="mobile-toggle d-lg-none" data-toggle="sidebar" aria-label="Toggle sidebar">
    <i class="fas fa-bars"></i>
  </button>

  <!-- Brand / Logo -->
  <a href="{{ route('admin.dashboard') }}" class="navbar-brand">
    <span class="brand-icon">
      <i class="fas fa-cube"></i>
    </span>
    <span class="brand-text">AdminHub</span>

  </a>

  <!-- Desktop Navigation -->
  <ul class="navbar-nav mx-auto d-none d-lg-flex">
    <!-- Dashboard -->
    <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
      <a href="{{ route('admin.dashboard') }}" class="nav-link">
        <i class="fas fa-th-large"></i>
        <span>Dashboard</span>
      </a>
    </li>

    <!-- Categories -->
    @can('Manage Categories')
    <li
      class="nav-item dropdown {{ setActive(['admin.category.*', 'admin.sub-category.*', 'admin.child-category.*', 'admin.slider.*']) }}">
      <a href="#" data-toggle="dropdown" class="nav-link has-dropdown">
        <i class="fas fa-layer-group"></i>
        <span>Categories <i class="fas fa-chevron-down dropdown-arrow"></i></span>
      </a>
      <ul class="dropdown-menu">
        <li class="dropdown-header">Manage Categories</li>
        <li class="{{ setActive(['admin.category.*']) }}">
          <a class="dropdown-item" href="{{ route('admin.category.index') }}">
            <i class="fas fa-tags"></i> Category
          </a>
        </li>
        <li class="{{ setActive(['admin.sub-category.*']) }}">
          <a class="dropdown-item" href="{{ route('admin.sub-category.index') }}">
            <i class="fas fa-tag"></i> Sub Category
          </a>
        </li>
        <li class="{{ setActive(['admin.child-category.*']) }}">
          <a class="dropdown-item" href="{{ route('admin.child-category.index') }}">
            <i class="fas fa-tag"></i> Child Category
          </a>
        </li>
        <li>
          <div class="dropdown-divider"></div>
        </li>
        <li class="{{ setActive(['admin.slider.*']) }}">
          <a class="dropdown-item" href="{{ route('admin.slider.index') }}">
            <i class="fas fa-images"></i> Slider
          </a>
        </li>
        <li class="{{ setActive(['admin.product-types.*']) }}">
          <a class="dropdown-item" href="{{ route('admin.product-types.index') }}">
            <i class="fas fa-calendar-alt"></i> Occasion Type
          </a>
        </li>
      </ul>
    </li>
    @endcan

    <!-- Products -->
    @canany(['Manage Products', 'View Product Stock'])
    <li
      class="nav-item dropdown {{ setActive(['admin.products.*', 'admin.units.*', 'admin.colors.*', 'admin.sizes.*']) }}">
      <a href="#" data-toggle="dropdown" class="nav-link has-dropdown">
        <i class="fas fa-box-open"></i>
        <span>Products <i class="fas fa-chevron-down dropdown-arrow"></i></span>
      </a>
      <ul class="dropdown-menu">
        <li class="{{ setActive(['admin.products.*']) }}">
          <a class="dropdown-item" href="{{ route('admin.products.index') }}">
            <i class="fas fa-cubes"></i> All Products
          </a>
        </li>
        @can('Manage Products')
        <li>
          <div class="dropdown-divider"></div>
        </li>
        <li class="dropdown-header">Attributes</li>
        <li class="{{ setActive(['admin.units.*']) }}">
          <a class="dropdown-item" href="{{ route('admin.units.index') }}">
            <i class="fas fa-weight-hanging"></i> Units
          </a>
        </li>
        <li class="{{ setActive(['admin.colors.*']) }}">
          <a class="dropdown-item" href="{{ route('admin.colors.index') }}">
            <i class="fas fa-palette"></i> Colors
          </a>
        </li>
        <li class="{{ setActive(['admin.sizes.*']) }}">
          <a class="dropdown-item" href="{{ route('admin.sizes.index') }}">
            <i class="fas fa-arrows-alt-v"></i> Sizes
          </a>
        </li>
        @endcan
      </ul>
    </li>
    @endcanany

    <!-- Inventory -->
    @can('Manage Inventory')
    <li
      class="nav-item dropdown {{ setActive(['admin.issues.*', 'admin.issue-returns.*', 'admin.stock-ledger.index', 'admin.inventory-reports.index']) }}">
      <a href="#" data-toggle="dropdown" class="nav-link has-dropdown">
        <i class="fas fa-warehouse"></i>
        <span>Inventory <i class="fas fa-chevron-down dropdown-arrow"></i></span>
      </a>
      <ul class="dropdown-menu">
        <li class="{{ setActive(['admin.inventory-reports.index']) }}">
          <a class="dropdown-item" href="{{ route('admin.inventory-reports.index') }}">
            <i class="fas fa-clipboard-check"></i> Current Stock
          </a>
        </li>
        <li class="{{ setActive(['admin.issues.index']) }}">
          <a class="dropdown-item" href="{{ route('admin.issues.index') }}">
            <i class="fas fa-arrow-right"></i> Stock Issues
          </a>
        </li>
        <li class="{{ setActive(['admin.issue-returns.*']) }}">
          <a class="dropdown-item" href="{{ route('admin.issue-returns.index') }}">
            <i class="fas fa-undo-alt"></i> Stock Returns
          </a>
        </li>
        <li>
          <div class="dropdown-divider"></div>
        </li>
        <li class="{{ setActive(['admin.stock-ledger.index']) }}">
          <a class="dropdown-item" href="{{ route('admin.stock-ledger.index') }}">
            <i class="fas fa-book"></i> Stock Ledger
          </a>
        </li>
      </ul>
    </li>
    @endcan

    <!-- Orders -->
    @can('Manage Order Place')
    <li
      class="nav-item dropdown {{ setActive(['admin.orders.*', 'admin.product-requests.*', 'admin.custom-product-requests.*']) }}">
      <a href="#" data-toggle="dropdown" class="nav-link has-dropdown">
        <i class="fas fa-shopping-bag"></i>
        <span>Orders <i class="fas fa-chevron-down dropdown-arrow"></i></span>
      </a>
      <ul class="dropdown-menu">
        <li class="{{ setActive(['admin.orders.*']) }}">
          <a class="dropdown-item" href="{{ route('admin.orders.index') }}">
            <i class="fas fa-store-alt"></i> Outlet/Shop Orders
          </a>
        </li>
        @canany(['Manage Custom Product Requests', 'View Custom Product Requests'])
        <li class="{{ setActive(['admin.custom-product-requests.*']) }}">
          <a class="dropdown-item" href="{{ route('admin.custom-product-requests.index') }}">
            <i class="fas fa-sync-alt"></i> Custom Requests
          </a>
        </li>
        @endcanany
        @if(Auth::user()->hasRole('Admin'))
        <li>
          <div class="dropdown-divider"></div>
        </li>
        <li class="{{ setActive(['admin.product-requests.index']) }}">
          <a class="dropdown-item" href="{{ route('admin.product-requests.index') }}">
            <i class="fas fa-history"></i> Old Requests
          </a>
        </li>
        <li class="{{ setActive(['admin.product-requests.create']) }}">
          <a class="dropdown-item" href="{{ route('admin.product-requests.create') }}">
            <i class="fas fa-plus-circle"></i> Create Request
          </a>
        </li>
        @endif
      </ul>
    </li>
    @endcan

    <!-- Purchases -->
    @can('Manage Order Receive')
    <li class="nav-item dropdown {{ setActive(['admin.purchases.*','admin.bookings.*']) }}">
      <a href="#" data-toggle="dropdown" class="nav-link has-dropdown">
        <i class="fas fa-cart-plus"></i>
        <span>Purchase <i class="fas fa-chevron-down dropdown-arrow"></i></span>
      </a>
      <ul class="dropdown-menu">
        <li class="{{ setActive(['admin.bookings.*']) }}">
          <a class="dropdown-item" href="{{ route('admin.bookings.index') }}">
            <i class="fas fa-shopping-cart"></i> All Order Place
          </a>
        </li>
        <li class="{{ setActive(['admin.purchases.index']) }}">
          <a class="dropdown-item" href="{{ route('admin.purchases.index') }}">
            <i class="fas fa-check-circle"></i> All Order Receive
          </a>
        </li>
        <li>
          <div class="dropdown-divider"></div>
        </li>
        <li class="{{ setActive(['admin.purchases.create']) }}">
          <a class="dropdown-item" href="{{ route('admin.purchases.create') }}">
            <i class="fas fa-plus-circle"></i> Create New
          </a>
        </li>
      </ul>
    </li>
    @endcan

    <!-- Reports -->
    @can('Manage Reports')
    <li class="nav-item dropdown {{ setActive(['admin.reports.*', 'admin.reports.orders']) }}">
      <a href="#" data-toggle="dropdown" class="nav-link has-dropdown">
        <i class="fas fa-chart-bar"></i>
        <span>Reports <i class="fas fa-chevron-down dropdown-arrow"></i></span>
      </a>
      <ul class="dropdown-menu">
        <li class="dropdown-header">Analytics</li>
        <li class="{{ setActive(['admin.reports.index']) }}">
          <a class="dropdown-item" href="{{ route('admin.reports.index') }}">
            <i class="fas fa-chart-pie"></i> All Reports
          </a>
        </li>
        <li class="{{ setActive(['admin.reports.orders']) }}">
          <a class="dropdown-item" href="{{ route('admin.reports.orders') }}">
            <i class="fas fa-file-invoice"></i> Order & Issue Report
          </a>
        </li>
        <li>
          <div class="dropdown-divider"></div>
        </li>
        <li class="dropdown-header">Stock Reports</li>
        <li class="{{ setActive(['admin.reports.stock']) }}">
          <a class="dropdown-item" href="{{ route('admin.reports.stock') }}">
            <i class="fas fa-boxes"></i> Stock Reports
          </a>
        </li>
        <li class="{{ setActive(['admin.reports.low-stock']) }}">
          <a class="dropdown-item" href="{{ route('admin.reports.low-stock') }}">
            <i class="fas fa-exclamation-triangle"></i> Low Stock Alert
          </a>
        </li>
        <li>
          <div class="dropdown-divider"></div>
        </li>
        <li class="dropdown-header">Financial</li>
        <li class="{{ setActive(['admin.reports.profit-loss']) }}">
          <a class="dropdown-item" href="{{ route('admin.reports.profit-loss') }}">
            <i class="fas fa-coins"></i> Profit & Loss
          </a>
        </li>
        <li class="{{ setActive(['admin.reports.purchase']) }}">
          <a class="dropdown-item" href="{{ route('admin.reports.purchase') }}">
            <i class="fas fa-shopping-bag"></i> Purchase History
          </a>
        </li>
        <li class="{{ setActive(['admin.reports.product-purchase-history']) }}">
          <a class="dropdown-item" href="{{ route('admin.reports.product-purchase-history') }}">
            <i class="fas fa-search"></i> Product Tracking
          </a>
        </li>
        <li>
          <div class="dropdown-divider"></div>
        </li>
        <li class="{{ setActive(['admin.reports.audit']) }}">
          <a class="dropdown-item" href="{{ route('admin.reports.audit') }}">
            <i class="fas fa-clipboard-check"></i> Audit Report
          </a>
        </li>
      </ul>
    </li>
    @endcan

    <!-- Accounts -->
    <li class="nav-item dropdown {{ setActive(['admin.accounts.*']) }}">
      <a href="#" data-toggle="dropdown" class="nav-link has-dropdown">
        <i class="fas fa-file-invoice-dollar"></i>
        <span>Accounts <i class="fas fa-chevron-down dropdown-arrow"></i></span>
      </a>
      <ul class="dropdown-menu">
        <li class="dropdown-header">Customer Accounts</li>
        <li class="{{ setActive(['admin.accounts.index']) }}">
          <a class="dropdown-item" href="{{ route('admin.accounts.index') }}">
            <i class="fas fa-users"></i> Customer Transactions
          </a>
        </li>
        <li class="{{ setActive(['admin.accounts.record-payment']) }}">
          <a class="dropdown-item" href="{{ route('admin.accounts.record-payment') }}">
            <i class="fas fa-hand-holding-usd"></i> Receive Payment
          </a>
        </li>
        <li class="{{ setActive(['admin.accounts.due-orders']) }}">
          <a class="dropdown-item" href="{{ route('admin.accounts.due-orders') }}">
            <i class="fas fa-clock"></i> Customer Due Orders
          </a>
        </li>
        <li>
          <div class="dropdown-divider"></div>
        </li>
        <li class="dropdown-header">Vendor Accounts</li>
        <li class="{{ setActive(['admin.accounts.vendor-payments.index']) }}">
          <a class="dropdown-item" href="{{ route('admin.accounts.vendor-payments.index') }}">
            <i class="fas fa-truck"></i> Vendor Payments
          </a>
        </li>
        <li class="{{ setActive(['admin.accounts.vendor-payments.record-payment']) }}">
          <a class="dropdown-item" href="{{ route('admin.accounts.vendor-payments.record-payment') }}">
            <i class="fas fa-money-check-alt"></i> Pay Vendor Invoice
          </a>
        </li>
        <li class="{{ setActive(['admin.accounts.vendor-payments.due-purchases']) }}">
          <a class="dropdown-item" href="{{ route('admin.accounts.vendor-payments.due-purchases') }}">
            <i class="fas fa-hourglass-half"></i> Vendor Due Purchases
          </a>
        </li>
      </ul>
    </li>

    <!-- Brands -->
    @can('Manage Brands')
    <li class="nav-item dropdown {{ setActive(['admin.brand.*']) }}">
      <a href="#" data-toggle="dropdown" class="nav-link has-dropdown">
        <i class="fas fa-tag"></i>
        <span>Brands <i class="fas fa-chevron-down dropdown-arrow"></i></span>
      </a>
      <ul class="dropdown-menu">
        <li class="{{ setActive(['admin.brand.index']) }}">
          <a class="dropdown-item" href="{{ route('admin.brand.index') }}">
            <i class="fas fa-list"></i> All Brands
          </a>
        </li>
        <li class="{{ setActive(['admin.brand.create']) }}">
          <a class="dropdown-item" href="{{ route('admin.brand.create') }}">
            <i class="fas fa-plus-circle"></i> Add Brand
          </a>
        </li>
      </ul>
    </li>
    @endcan

    <!-- Vendors -->
    @can('Manage Vendors')
    <li class="nav-item dropdown {{ setActive(['admin.vendor.*']) }}">
      <a href="#" data-toggle="dropdown" class="nav-link has-dropdown">
        <i class="fas fa-truck"></i>
        <span>Vendors <i class="fas fa-chevron-down dropdown-arrow"></i></span>
      </a>
      <ul class="dropdown-menu">
        <li class="{{ setActive(['admin.vendor.index']) }}">
          <a class="dropdown-item" href="{{ route('admin.vendor.index') }}">
            <i class="fas fa-list"></i> All Vendors
          </a>
        </li>
        <li class="{{ setActive(['admin.vendor.create']) }}">
          <a class="dropdown-item" href="{{ route('admin.vendor.create') }}">
            <i class="fas fa-plus-circle"></i> Add Vendor
          </a>
        </li>
      </ul>
    </li>
    @endcan

    <!-- System -->
    @can('Administration')
    <li
      class="nav-item dropdown {{ setActive(['admin.permission.*', 'admin.role.*', 'admin.users.*', 'admin.settings.*', 'admin.pricing-rules.*', 'admin.taxes.*', 'admin.discounts.*', 'admin.products.announcement.*']) }}">
      <a href="#" data-toggle="dropdown" class="nav-link has-dropdown">
        <i class="fas fa-cog"></i>
        <span>System <i class="fas fa-chevron-down dropdown-arrow"></i></span>
      </a>
      <ul class="dropdown-menu">
        <li class="dropdown-header">User Management</li>
        <li class="{{ setActive(['admin.users.*']) }}">
          <a class="dropdown-item" href="{{ route('admin.users.index') }}">
            <i class="fas fa-users-cog"></i> Users
          </a>
        </li>
        <li class="{{ setActive(['admin.role.*']) }}">
          <a class="dropdown-item" href="{{ route('admin.role.index') }}">
            <i class="fas fa-user-tag"></i> Roles
          </a>
        </li>
        <li class="{{ setActive(['admin.permission.*']) }}">
          <a class="dropdown-item" href="{{ route('admin.permission.index') }}">
            <i class="fas fa-lock"></i> Permissions
          </a>
        </li>
        <li>
          <div class="dropdown-divider"></div>
        </li>
        <li class="dropdown-header">Business Rules</li>
        <li class="{{ setActive(['admin.pricing-rules.*']) }}">
          <a class="dropdown-item" href="{{ route('admin.pricing-rules.index') }}">
            <i class="fas fa-dollar-sign"></i> Pricing Rules
          </a>
        </li>
        <li class="{{ setActive(['admin.taxes.*']) }}">
          <a class="dropdown-item" href="{{ route('admin.taxes.index') }}">
            <i class="fas fa-percent"></i> Tax / VAT
          </a>
        </li>
        <li class="{{ setActive(['admin.discounts.*']) }}">
          <a class="dropdown-item" href="{{ route('admin.discounts.index') }}">
            <i class="fas fa-tags"></i> Discount
          </a>
        </li>
        <li>
          <div class="dropdown-divider"></div>
        </li>
        <li class="dropdown-header">System Settings</li>
        <li class="{{ setActive(['admin.products.announcement.*']) }}">
          <a class="dropdown-item" href="{{ route('admin.products.announcement.index') }}">
            <i class="fas fa-bullhorn"></i> Product Announcement
          </a>
        </li>
        <li class="{{ setActive(['admin.settings.*']) }}">
          <a class="dropdown-item" href="{{ route('admin.settings.index') }}">
            <i class="fas fa-sliders-h"></i> Settings
          </a>
        </li>
      </ul>
    </li>
    @endcan
  </ul>

  <!-- Right Side -->
  <ul class="navbar-nav navbar-right">


    <li class="divider-vertical d-none d-lg-block"></li>

    <!-- Notifications -->
    @can('Manage Notification')
    <li class="dropdown dropdown-list-toggle">
      <a href="#" data-toggle="dropdown" class="notification-toggle" id="low-stock-count-toggle"
        aria-label="Notifications">
        <i class="fas fa-bell"></i>
        <span class="badge" id="low-stock-count-badge" style="display: none;">0</span>
      </a>
      <div class="dropdown-menu dropdown-menu-right dropdown-list" style="width: 360px; padding: 0;">
        <div class="dropdown-header"
          style="padding: 16px 20px; background: transparent; border-bottom: 1px solid rgba(255,255,255,0.06);">
          <span style="font-size: 14px; font-weight: 600; color: #fff;">Notifications</span>
          <span class="float-right">
            <a href="javascript:void(0)" onclick="markAllAsRead()"
              style="font-size: 12px; color: var(--primary-color); text-decoration: none; font-weight: 500;">Mark all
              read</a>
          </span>
        </div>
        <div class="dropdown-list-content" id="low-stock-list"
          style="max-height: 350px; overflow-y: auto; padding: 8px;">
          <div class="text-center py-4" style="color: var(--text-muted); font-size: 13px;">
            <i class="fas fa-inbox" style="font-size: 24px; display: block; margin-bottom: 8px; opacity: 0.5;"></i>
            No new notifications
          </div>
        </div>
        <div class="dropdown-footer"
          style="padding: 12px 20px; border-top: 1px solid rgba(255,255,255,0.06); text-align: center;">
          <a href="{{ route('admin.notifications.all') }}"
            style="color: var(--primary-color); text-decoration: none; font-size: 13px; font-weight: 500;">
            View All <i class="fas fa-arrow-right ml-1"></i>
          </a>
        </div>
      </div>
    </li>
    @endcan

    <!-- Profile -->
    <li class="dropdown">
      <a href="#" data-toggle="dropdown" class="profile-toggle" aria-label="Profile">
        <img class="avatar"
          src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=4f8cff&color=fff&size=34"
          alt="{{ Auth::user()->name }}">
        <div class="profile-info">
          <span class="name">{{ Auth::user()->name }}</span>
          <span class="role">{{ Auth::user()->roles->first()->name ?? 'User' }}</span>
        </div>
        <i class="fas fa-chevron-down chevron"></i>
      </a>
      <div class="dropdown-menu dropdown-menu-right" style="min-width: 200px; padding: 8px;">
        <a href="{{ route('admin.profile') }}" class="dropdown-item">
          <i class="fas fa-user"></i> My Profile
        </a>
        @can('Administration')
        <a href="{{ route('admin.settings.index') }}" class="dropdown-item">
          <i class="fas fa-cog"></i> Settings
        </a>
        @endcan
        <div class="dropdown-divider"></div>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();"
            class="dropdown-item" style="color: #ff6b6b !important;">
            <i class="fas fa-sign-out-alt"></i> Logout
          </a>
        </form>
      </div>
    </li>
  </ul>
</nav>