<style>
:root {
  --nb-bg: #0b1120;
  --nb-surface: #131c31;
  --nb-border: rgba(255, 255, 255, 0.06);
  --nb-primary: #6366f1;
  --nb-primary-soft: rgba(99, 102, 241, 0.12);
  --nb-text: rgba(255, 255, 255, 0.9);
  --nb-muted: rgba(255, 255, 255, 0.45);
  --nb-shadow: 0 8px 40px rgba(0, 0, 0, 0.35);
  --nb-radius: 10px;
}

.navbar.main-navbar {
  background: linear-gradient(135deg, #0b1120 0%, #111b2e 100%);
  padding: 0 32px;
  min-height: 70px;
  border-bottom: 1px solid var(--nb-border);
  box-shadow: 0 4px 30px rgba(0, 0, 0, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.04);
  position: sticky;
  top: 0;
  z-index: 1030;
}

.navbar.main-navbar .navbar-brand {
  color: #fff !important;
  font-weight: 700;
  font-size: 18px;
  padding: 0;
  display: flex;
  align-items: center;
  gap: 12px;
}

.navbar.main-navbar .navbar-brand .brand-text {
  background: linear-gradient(90deg, #fff 65%, #94a3b8);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.navbar.main-navbar .navbar-brand .brand-badge {
  display: none;
}

.navbar.main-navbar .navbar-nav {
  display: flex;
  align-items: center;
  gap: 4px;
  height: 100%;
}

.navbar.main-navbar .navbar-nav .nav-item {
  height: 100%;
  display: flex;
  align-items: center;
  position: relative;
}

.navbar.main-navbar .navbar-nav .nav-item .nav-link {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 0 18px;
  height: 70px;
  color: var(--nb-muted) !important;
  font-weight: 500;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  border-radius: 8px;
  gap: 2px;
  min-width: 58px;
}

.navbar.main-navbar .navbar-nav .nav-item .nav-link i {
  font-size: 18px;
  color: var(--nb-muted);
  margin-bottom: 2px;
  transition: all 0.3s ease;
}

.navbar.main-navbar .navbar-nav .nav-item .nav-link span {
  font-size: 9.5px;
  line-height: 1.2;
  font-weight: 600;
  letter-spacing: 0.4px;
  white-space: nowrap;
  opacity: 0.75;
}

.navbar.main-navbar .navbar-nav .nav-item .nav-link:hover {
  color: #fff !important;
  background: rgba(255, 255, 255, 0.06);
  transform: translateY(-1px);
}

.navbar.main-navbar .navbar-nav .nav-item .nav-link:hover i {
  color: var(--nb-primary);
  transform: scale(1.1);
}

.navbar.main-navbar .navbar-nav .nav-item.active .nav-link {
  color: #fff !important;
  background: var(--nb-primary-soft);
}

.navbar.main-navbar .navbar-nav .nav-item.active .nav-link i {
  color: var(--nb-primary);
}

.navbar.main-navbar .navbar-nav .nav-item.active .nav-link::after {
  content: '';
  position: absolute;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 24px;
  height: 3px;
  background: linear-gradient(90deg, #6366f1, #8b5cf6);
  border-radius: 0 0 4px 4px;
  box-shadow: 0 2px 8px rgba(99, 102, 241, 0.45);
}

.navbar.main-navbar .navbar-nav .nav-item .nav-link.has-dropdown::after {
  display: none;
}

.navbar.main-navbar .navbar-nav .nav-item .nav-link.has-dropdown .dropdown-arrow {
  font-size: 7px;
  margin-left: 4px;
  opacity: 0.5;
  transition: all 0.3s ease;
}

.navbar.main-navbar .navbar-nav .nav-item .nav-link.has-dropdown:hover .dropdown-arrow {
  opacity: 1;
  transform: rotate(180deg);
}

.navbar.main-navbar .dropdown-menu {
  background: rgba(19, 28, 49, 0.97);
  backdrop-filter: blur(16px);
  border: 1px solid var(--nb-border);
  border-radius: 12px;
  padding: 6px;
  margin-top: 6px !important;
  min-width: 220px;
  box-shadow: 0 24px 60px rgba(0, 0, 0, 0.55);
  animation: nbFade 0.2s ease;
}

@keyframes nbFade {
  from {
    opacity: 0;
    transform: translateY(-6px) scale(0.96);
  }

  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.navbar.main-navbar .dropdown-menu .dropdown-item {
  padding: 9px 14px;
  color: rgba(255, 255, 255, 0.75) !important;
  font-size: 12.5px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 10px;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.navbar.main-navbar .dropdown-menu .dropdown-item i {
  font-size: 14px;
  width: 18px;
  color: var(--nb-muted);
  transition: all 0.2s ease;
}

.navbar.main-navbar .dropdown-menu .dropdown-item:hover {
  background: var(--nb-primary-soft) !important;
  color: #fff !important;
  transform: translateX(3px);
}

.navbar.main-navbar .dropdown-menu .dropdown-item:hover i {
  color: var(--nb-primary);
}

.navbar.main-navbar .dropdown-menu .dropdown-divider {
  margin: 4px 6px;
  border-color: var(--nb-border);
}

.navbar.main-navbar .dropdown-menu .dropdown-header {
  color: var(--nb-muted);
  font-size: 9px;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  padding: 6px 12px 2px;
  font-weight: 700;
}

.navbar.main-navbar .navbar-right {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-left: auto;
  height: 100%;
}

@keyframes nbBellRing {

  0%,
  100% {
    transform: rotate(0);
  }

  10% {
    transform: rotate(14deg);
  }

  20% {
    transform: rotate(-10deg);
  }

  30% {
    transform: rotate(8deg);
  }

  40% {
    transform: rotate(-6deg);
  }

  50% {
    transform: rotate(4deg);
  }

  60% {
    transform: rotate(-2deg);
  }

  70%,
  90% {
    transform: rotate(0);
  }
}

@keyframes nbBadgePulse {
  0% {
    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.6);
  }

  70% {
    box-shadow: 0 0 0 8px rgba(239, 68, 68, 0);
  }

  100% {
    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
  }
}

.navbar.main-navbar .navbar-right .notification-toggle {
  position: relative;
  padding: 0;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  transition: all 0.3s ease;
  color: var(--nb-muted) !important;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid var(--nb-border);
  overflow: visible;
}

.navbar.main-navbar .navbar-right .notification-toggle:hover {
  background: rgba(255, 255, 255, 0.07);
  color: #fff !important;
  border-color: rgba(255, 255, 255, 0.1);
  transform: translateY(-1px);
}

.navbar.main-navbar .navbar-right .notification-toggle i {
  font-size: 17px;
}

.navbar.main-navbar .navbar-right .notification-toggle.has-count {
  background: rgba(239, 68, 68, 0.06);
  border-color: rgba(239, 68, 68, 0.15);
}

.navbar.main-navbar .navbar-right .notification-toggle.has-count i {
  animation: nbBellRing 1.2s ease-in-out;
  transform-origin: top center;
}

.navbar.main-navbar .navbar-right .notification-toggle .badge {
  position: absolute;
  top: -2px;
  right: -2px;
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: #fff;
  font-size: 8px;
  font-weight: 700;
  min-width: 17px;
  height: 17px;
  padding: 0 4px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid var(--nb-bg);
  box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
  animation: nbBadgePulse 2s infinite;
}

.navbar.main-navbar .navbar-right .profile-toggle {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 4px 12px 4px 4px;
  border-radius: 100px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid var(--nb-border);
  transition: all 0.3s ease;
  cursor: pointer;
  text-decoration: none;
  color: var(--nb-text) !important;
}

.navbar.main-navbar .navbar-right .profile-toggle:hover {
  background: rgba(255, 255, 255, 0.07);
  border-color: rgba(255, 255, 255, 0.1);
  transform: translateY(-1px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
}

.navbar.main-navbar .navbar-right .profile-toggle .avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: 2px solid rgba(99, 102, 241, 0.3);
  object-fit: cover;
  transition: all 0.3s ease;
}

.navbar.main-navbar .navbar-right .profile-toggle:hover .avatar {
  border-color: var(--nb-primary);
  box-shadow: 0 0 16px rgba(99, 102, 241, 0.2);
}

.navbar.main-navbar .navbar-right .profile-toggle .profile-info {
  display: flex;
  flex-direction: column;
  line-height: 1.2;
}

.navbar.main-navbar .navbar-right .profile-toggle .profile-info .name {
  font-size: 12.5px;
  font-weight: 600;
  color: #fff;
}

.navbar.main-navbar .navbar-right .profile-toggle .profile-info .role {
  font-size: 9.5px;
  color: var(--nb-muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.navbar.main-navbar .navbar-right .profile-toggle .chevron {
  font-size: 11px;
  color: var(--nb-muted);
  transition: all 0.3s ease;
  margin-left: 4px;
}

.navbar.main-navbar .navbar-right .profile-toggle:hover .chevron {
  color: #fff;
  transform: rotate(180deg);
}

.navbar.main-navbar .mobile-toggle {
  color: #fff !important;
  padding: 8px 10px;
  border-radius: 8px;
  transition: all 0.3s ease;
  font-size: 20px;
  background: transparent;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
}

.navbar.main-navbar .mobile-toggle:hover {
  background: rgba(255, 255, 255, 0.07);
  transform: translateY(-1px);
}

.navbar.main-navbar .divider-vertical {
  width: 1px;
  height: 28px;
  background: var(--nb-border);
  margin: 0 8px;
}

.navbar.main-navbar .dropdown-list-content::-webkit-scrollbar {
  width: 3px;
}

.navbar.main-navbar .dropdown-list-content::-webkit-scrollbar-track {
  background: transparent;
}

.navbar.main-navbar .dropdown-list-content::-webkit-scrollbar-thumb {
  background: rgba(99, 102, 241, 0.3);
  border-radius: 10px;
}

/* ========================================
   RESPONSIVE DESIGN
   ======================================== */

/* Large desktop (1400px+) – extra breathing room */
@media (min-width: 1400px) {
  .navbar.main-navbar {
    padding: 0 48px;
  }

  .navbar.main-navbar .navbar-nav .nav-item .nav-link {
    padding: 0 22px;
    min-width: 70px;
  }
}

/* Desktop / medium – keep dropdowns visible */
@media (max-width: 1399.98px) and (min-width: 992px) {
  .navbar.main-navbar {
    padding: 0 16px;
  }

  .navbar.main-navbar .navbar-nav.mx-auto {
    gap: 2px;
    padding: 0 4px;
    flex: 1 1 auto;
    min-width: 0;
  }

  .navbar.main-navbar .navbar-nav .nav-item .nav-link {
    padding: 0 8px;
    min-width: 40px;
    height: 60px;
  }

  .navbar.main-navbar .navbar-nav .nav-item .nav-link span {
    font-size: 7.5px;
    letter-spacing: 0.2px;
  }

  .navbar.main-navbar .navbar-nav .nav-item .nav-link i {
    font-size: 14px;
    margin-bottom: 1px;
  }
}

/* Standard desktop down */
@media (max-width: 1199.98px) and (min-width: 992px) {
  .navbar.main-navbar .navbar-nav .nav-item .nav-link {
    padding: 0 6px;
    min-width: 36px;
  }

  .navbar.main-navbar .navbar-nav .nav-item .nav-link span {
    font-size: 7px;
  }
}

/* Tablet / mobile – nav hides, only right section + hamburger visible */
@media (max-width: 991.98px) {
  .navbar.main-navbar {
    padding: 0 14px;
    min-height: 60px;
    display: flex;
    align-items: center;
    gap: 0;
  }

  .navbar.main-navbar .navbar-nav.mx-auto {
    display: none !important;
  }

  .navbar.main-navbar .navbar-brand {
    margin-right: auto;
  }

  .navbar.main-navbar .navbar-right {
    gap: 2px;
    margin-left: auto;
    flex-shrink: 0;
  }

  .navbar.main-navbar .navbar-right .profile-toggle .profile-info,
  .navbar.main-navbar .navbar-right .profile-toggle .chevron {
    display: none;
  }

  .navbar.main-navbar .navbar-right .profile-toggle {
    padding: 3px;
    border-radius: 50%;
    border: none;
    background: transparent;
  }

  .navbar.main-navbar .navbar-right .profile-toggle:hover {
    background: rgba(255, 255, 255, 0.08);
  }

  .navbar.main-navbar .navbar-right .profile-toggle .avatar {
    width: 30px;
    height: 30px;
    border-width: 1.5px;
  }

  .navbar.main-navbar .navbar-right .notification-toggle {
    width: 36px;
    height: 36px;
  }

  .navbar.main-navbar .navbar-right .notification-toggle i {
    font-size: 16px;
  }

  .navbar.main-navbar .search-wrapper {
    display: none;
  }

  .navbar.main-navbar .navbar-brand .brand-badge {
    display: none;
  }

  .navbar.main-navbar .mobile-toggle {
    flex-shrink: 0;
  }

  /* Notification dropdown full-width on mobile */
  .navbar.main-navbar .navbar-right .dropdown-menu.dropdown-list {
    width: calc(100vw - 32px) !important;
    max-width: 400px;
    right: -8px !important;
    left: auto !important;
  }
}

/* Mobile landscape */
@media (max-width: 767.98px) {
  .navbar.main-navbar {
    padding: 0 12px;
    min-height: 56px;
  }

  .navbar.main-navbar .navbar-brand {
    font-size: 15px;
    gap: 6px;
  }

  .navbar.main-navbar .navbar-brand .brand-icon {
    width: 30px;
    height: 30px;
    font-size: 13px;
    box-shadow: 0 2px 10px rgba(79, 140, 255, 0.25);
  }

  .navbar.main-navbar .navbar-right {
    gap: 0px;
  }

  .navbar.main-navbar .navbar-right .notification-toggle {
    width: 32px;
    height: 32px;
  }

  .navbar.main-navbar .navbar-right .notification-toggle i {
    font-size: 15px;
  }

  .navbar.main-navbar .navbar-right .notification-toggle .badge {
    min-width: 15px;
    height: 15px;
    font-size: 7px;
    top: 2px;
    right: 2px;
  }

  .navbar.main-navbar .navbar-right .profile-toggle .avatar {
    width: 28px;
    height: 28px;
  }

  .navbar.main-navbar .mobile-toggle {
    font-size: 17px;
    padding: 4px 8px;
  }

  .navbar.main-navbar .divider-vertical {
    display: none !important;
  }
}

/* Mobile portrait – tiny screens */
@media (max-width: 480px) {
  .navbar.main-navbar {
    padding: 0 8px;
    min-height: 50px;
  }

  .navbar.main-navbar .navbar-brand .brand-text {
    display: none;
  }

  .navbar.main-navbar .navbar-brand .brand-icon {
    width: 28px;
    height: 28px;
    font-size: 12px;
  }

  .navbar.main-navbar .navbar-right .notification-toggle {
    width: 30px;
    height: 30px;
  }

  .navbar.main-navbar .navbar-right .notification-toggle i {
    font-size: 13px;
  }

  .navbar.main-navbar .navbar-right .profile-toggle .avatar {
    width: 26px;
    height: 26px;
  }

  .navbar.main-navbar .mobile-toggle {
    font-size: 15px;
    padding: 2px 6px;
  }

  .navbar.main-navbar .navbar-right .dropdown-menu.dropdown-list {
    width: calc(100vw - 16px) !important;
    right: -4px !important;
  }
}

/* Fix: dropdown position on small screens */
@media (max-width: 575.98px) {
  .navbar.main-navbar .navbar-right .dropdown-menu.dropdown-menu-right {
    position: fixed !important;
    top: 60px !important;
    left: 8px !important;
    right: 8px !important;
    width: auto !important;
    max-width: none;
    transform: none !important;
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
</style>

<nav class="navbar navbar-expand-lg main-navbar">

  <button class="mobile-toggle d-lg-none" data-toggle="sidebar" aria-label="Toggle sidebar">
    <i class="fas fa-bars"></i>
  </button>

  <ul class="navbar-nav mx-auto d-none d-lg-flex">

    <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
      <a href="{{ route('admin.dashboard') }}" class="nav-link">
        <i class="fas fa-th-large"></i>
        <span>Dashboard</span>
      </a>
    </li>

    <li class="nav-item dropdown">
      <a href="#" data-toggle="dropdown" class="nav-link has-dropdown">
        <i class="fas fa-layer-group"></i>
        <span>Categories <i class="fas fa-chevron-down dropdown-arrow"></i></span>
      </a>
      <ul class="dropdown-menu">
        <li class="dropdown-header">Manage Categories</li>
        <li><a class="dropdown-item" href="{{ route('admin.category.index') }}"><i class="fas fa-tags"></i> Category</a>
        </li>
        <li><a class="dropdown-item" href="{{ route('admin.sub-category.index') }}"><i class="fas fa-tag"></i> Sub
            Category</a></li>
        <li><a class="dropdown-item" href="{{ route('admin.child-category.index') }}"><i class="fas fa-tag"></i> Child
            Category</a></li>
        <li>
          <div class="dropdown-divider"></div>
        </li>
        <li><a class="dropdown-item" href="{{ route('admin.slider.index') }}"><i class="fas fa-images"></i> Slider</a>
        </li>
        <li><a class="dropdown-item" href="{{ route('admin.product-types.index') }}"><i class="fas fa-calendar-alt"></i>
            Occasion Type</a></li>
      </ul>
    </li>

    <li class="nav-item dropdown">
      <a href="#" data-toggle="dropdown" class="nav-link has-dropdown">
        <i class="fas fa-box-open"></i>
        <span>Products <i class="fas fa-chevron-down dropdown-arrow"></i></span>
      </a>
      <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="{{ route('admin.products.index') }}"><i class="fas fa-cubes"></i> All
            Products</a></li>
        <li>
          <div class="dropdown-divider"></div>
        </li>
        <li class="dropdown-header">Attributes</li>
        <li><a class="dropdown-item" href="{{ route('admin.units.index') }}"><i class="fas fa-weight-hanging"></i>
            Units</a></li>
        <li><a class="dropdown-item" href="{{ route('admin.colors.index') }}"><i class="fas fa-palette"></i> Colors</a>
        </li>
        <li><a class="dropdown-item" href="{{ route('admin.sizes.index') }}"><i class="fas fa-ruler"></i> Sizes</a></li>
      </ul>
    </li>

    <li class="nav-item dropdown">
      <a href="#" data-toggle="dropdown" class="nav-link has-dropdown">
        <i class="fas fa-warehouse"></i>
        <span>Inventory <i class="fas fa-chevron-down dropdown-arrow"></i></span>
      </a>
      <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="{{ route('admin.inventory-reports.index') }}"><i
              class="fas fa-clipboard-check"></i> Current Stock</a></li>
        <li><a class="dropdown-item" href="{{ route('admin.issues.index') }}"><i class="fas fa-arrow-right"></i> Stock
            Issues</a></li>
        <li><a class="dropdown-item" href="{{ route('admin.issue-returns.index') }}"><i class="fas fa-undo-alt"></i>
            Stock Returns</a></li>
        <li>
          <div class="dropdown-divider"></div>
        </li>
        <li><a class="dropdown-item" href="{{ route('admin.stock-ledger.index') }}"><i class="fas fa-book"></i> Stock
            Ledger</a></li>
      </ul>
    </li>

    <li class="nav-item dropdown">
      <a href="#" data-toggle="dropdown" class="nav-link has-dropdown">
        <i class="fas fa-shopping-bag"></i>
        <span>Orders <i class="fas fa-chevron-down dropdown-arrow"></i></span>
      </a>
      <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="{{ route('admin.orders.index') }}"><i class="fas fa-store-alt"></i>
            Outlet/Shop Orders</a></li>
        <li><a class="dropdown-item" href="{{ route('admin.custom-product-requests.index') }}"><i
              class="fas fa-sync-alt"></i> Custom Requests</a></li>
        <li>
          <div class="dropdown-divider"></div>
        </li>
        <li><a class="dropdown-item" href="{{ route('admin.product-requests.index') }}"><i class="fas fa-history"></i>
            Old Requests</a></li>
        <li><a class="dropdown-item" href="{{ route('admin.product-requests.create') }}"><i
              class="fas fa-plus-circle"></i> Create Request</a></li>
      </ul>
    </li>

    <li class="nav-item dropdown">
      <a href="#" data-toggle="dropdown" class="nav-link has-dropdown">
        <i class="fas fa-cart-plus"></i>
        <span>Purchase <i class="fas fa-chevron-down dropdown-arrow"></i></span>
      </a>
      <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="{{ route('admin.bookings.index') }}"><i class="fas fa-shopping-cart"></i> All
            Order Place</a></li>
        <li><a class="dropdown-item" href="{{ route('admin.purchases.index') }}"><i class="fas fa-check-circle"></i> All
            Order Receive</a></li>
        <li>
          <div class="dropdown-divider"></div>
        </li>
        <li class="dropdown-header">Quick Actions</li>
        <li><a class="dropdown-item" href="{{ route('admin.purchases.create') }}"><i class="fas fa-plus-circle"></i>
            Create New</a></li>
      </ul>
    </li>

    <li class="nav-item dropdown">
      <a href="#" data-toggle="dropdown" class="nav-link has-dropdown">
        <i class="fas fa-chart-bar"></i>
        <span>Reports <i class="fas fa-chevron-down dropdown-arrow"></i></span>
      </a>
      <ul class="dropdown-menu">
        <li class="dropdown-header">Analytics</li>
        <li><a class="dropdown-item" href="{{ route('admin.reports.index') }}"><i class="fas fa-chart-pie"></i> All
            Reports</a></li>
        <li><a class="dropdown-item" href="{{ route('admin.reports.orders') }}"><i class="fas fa-file-invoice"></i>
            Order & Issue Report</a></li>
        <li>
          <div class="dropdown-divider"></div>
        </li>
        <li class="dropdown-header">Stock Reports</li>
        <li><a class="dropdown-item" href="{{ route('admin.reports.stock') }}"><i class="fas fa-boxes"></i> Stock
            Reports</a></li>
        <li><a class="dropdown-item" href="{{ route('admin.reports.low-stock') }}"><i
              class="fas fa-exclamation-triangle"></i> Low Stock Alert</a></li>
        <li>
          <div class="dropdown-divider"></div>
        </li>
        <li class="dropdown-header">Financial</li>
        <li><a class="dropdown-item" href="{{ route('admin.reports.profit-loss') }}"><i class="fas fa-coins"></i> Profit
            & Loss</a></li>
        <li><a class="dropdown-item" href="{{ route('admin.reports.purchase') }}"><i class="fas fa-shopping-bag"></i>
            Purchase History</a></li>
        <li><a class="dropdown-item" href="{{ route('admin.reports.product-purchase-history') }}"><i
              class="fas fa-search"></i> Product Tracking</a></li>
        <li>
          <div class="dropdown-divider"></div>
        </li>
        <li><a class="dropdown-item" href="{{ route('admin.reports.audit') }}"><i class="fas fa-clipboard-check"></i>
            Audit Report</a></li>
      </ul>
    </li>

    <li class="nav-item dropdown">
      <a href="#" data-toggle="dropdown" class="nav-link has-dropdown">
        <i class="fas fa-file-invoice-dollar"></i>
        <span>Accounts <i class="fas fa-chevron-down dropdown-arrow"></i></span>
      </a>
      <ul class="dropdown-menu">
        <li class="dropdown-header">Customer Accounts</li>
        <li><a class="dropdown-item" href="{{ route('admin.accounts.index') }}"><i class="fas fa-users"></i> Customer
            Transactions</a></li>
        <li><a class="dropdown-item" href="{{ route('admin.accounts.record-payment') }}"><i
              class="fas fa-hand-holding-usd"></i> Receive Payment</a></li>
        <li><a class="dropdown-item" href="{{ route('admin.accounts.due-orders') }}"><i class="fas fa-clock"></i>
            Customer Due Orders</a></li>
        <li>
          <div class="dropdown-divider"></div>
        </li>
        <li class="dropdown-header">Vendor Accounts</li>
        <li><a class="dropdown-item" href="{{ route('admin.accounts.vendor-payments.index') }}"><i
              class="fas fa-truck"></i> Vendor Payments</a></li>
        <li><a class="dropdown-item" href="{{ route('admin.accounts.vendor-payments.record-payment') }}"><i
              class="fas fa-money-check-alt"></i> Pay Vendor Invoice</a></li>
        <li><a class="dropdown-item" href="{{ route('admin.accounts.vendor-payments.due-purchases') }}"><i
              class="fas fa-hourglass-half"></i> Vendor Due Purchases</a></li>
      </ul>
    </li>

    <li class="nav-item dropdown">
      <a href="#" data-toggle="dropdown" class="nav-link has-dropdown">
        <i class="fas fa-tag"></i>
        <span>Brands <i class="fas fa-chevron-down dropdown-arrow"></i></span>
      </a>
      <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="{{ route('admin.brand.index') }}"><i class="fas fa-list"></i> All Brands</a>
        </li>
        <li><a class="dropdown-item" href="{{ route('admin.brand.create') }}"><i class="fas fa-plus-circle"></i> Add
            Brand</a></li>
      </ul>
    </li>

    <li class="nav-item dropdown">
      <a href="#" data-toggle="dropdown" class="nav-link has-dropdown">
        <i class="fas fa-truck"></i>
        <span>Vendors <i class="fas fa-chevron-down dropdown-arrow"></i></span>
      </a>
      <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="{{ route('admin.vendor.index') }}"><i class="fas fa-list"></i> All
            Vendors</a></li>
        <li><a class="dropdown-item" href="{{ route('admin.vendor.create') }}"><i class="fas fa-plus-circle"></i> Add
            Vendor</a></li>
      </ul>
    </li>

    <li class="nav-item dropdown">
      <a href="#" data-toggle="dropdown" class="nav-link has-dropdown">
        <i class="fas fa-cog"></i>
        <span>System <i class="fas fa-chevron-down dropdown-arrow"></i></span>
      </a>
      <ul class="dropdown-menu">
        <li class="dropdown-header">User Management</li>
        <li><a class="dropdown-item" href="{{ route('admin.users.index') }}"><i class="fas fa-users-cog"></i> Users</a>
        </li>
        <li><a class="dropdown-item" href="{{ route('admin.role.index') }}"><i class="fas fa-user-tag"></i> Roles</a>
        </li>
        <li><a class="dropdown-item" href="{{ route('admin.permission.index') }}"><i class="fas fa-lock"></i>
            Permissions</a></li>
        <li>
          <div class="dropdown-divider"></div>
        </li>
        <li class="dropdown-header">Business Rules</li>
        <li><a class="dropdown-item" href="{{ route('admin.pricing-rules.index') }}"><i class="fas fa-dollar-sign"></i>
            Pricing Rules</a></li>
        <li><a class="dropdown-item" href="{{ route('admin.taxes.index') }}"><i class="fas fa-percent"></i> Tax /
            VAT</a></li>
        <li><a class="dropdown-item" href="{{ route('admin.discounts.index') }}"><i class="fas fa-tags"></i>
            Discount</a></li>
        <li>
          <div class="dropdown-divider"></div>
        </li>
        <li class="dropdown-header">System Settings</li>
        <li><a class="dropdown-item" href="{{ route('admin.products.announcement.index') }}"><i
              class="fas fa-bullhorn"></i> Product Announcement</a></li>
        <li><a class="dropdown-item" href="{{ route('admin.settings.index') }}"><i class="fas fa-sliders-h"></i>
            Settings</a></li>
      </ul>
    </li>

  </ul>

  <ul class="navbar-nav navbar-right">

    <li class="divider-vertical d-none d-lg-block"></li>

    <li class="dropdown dropdown-list-toggle">
      <a id="low-stock-count-toggle" href="#" data-toggle="dropdown" class="notification-toggle"
        aria-label="Notifications">
        <i class="fas fa-bell"></i>
        <span id="low-stock-count-badge" class="badge" style="display: none;">0</span>
      </a>
      <div class="dropdown-menu dropdown-menu-right dropdown-list" style="width: 340px; padding: 0;">
        <div class="dropdown-header" style="padding: 14px 18px; border-bottom: 1px solid rgba(255,255,255,0.06);">
          <span style="font-size: 13px; font-weight: 600; color: #fff;">Notifications</span>
        </div>
        <div id="low-stock-list" class="dropdown-list-content"
          style="max-height: 320px; overflow-y: auto; padding: 6px;">
          <div class="text-center py-4" style="color: rgba(255,255,255,0.4); font-size: 12px;">
            <i class="fas fa-inbox" style="font-size: 22px; display: block; margin-bottom: 6px; opacity: 0.3;"></i>
            No new notifications
          </div>
        </div>
        <div class="dropdown-footer"
          style="padding: 10px 18px; border-top: 1px solid rgba(255,255,255,0.06); text-align: center;">
          <a href="{{ route('admin.notifications.all') }}"
            style="color: var(--nb-primary); text-decoration: none; font-size: 12px; font-weight: 500;">
            View All <i class="fas fa-arrow-right ml-1"></i>
          </a>
        </div>
      </div>
    </li>

    <li class="dropdown">
      <a href="#" data-toggle="dropdown" class="profile-toggle" aria-label="Profile">
        <img class="avatar" src="https://ui-avatars.com/api/?name=Admin&background=6366f1&color=fff&size=32"
          alt="Admin">
        <div class="profile-info">
          <span class="name">Admin</span>
          <span class="role">Administrator</span>
        </div>
        <i class="fas fa-chevron-down chevron"></i>
      </a>
      <div class="dropdown-menu dropdown-menu-right" style="min-width: 190px; padding: 6px;">
        <a href="#" class="dropdown-item"><i class="fas fa-user"></i> My Profile</a>
        <a href="#" class="dropdown-item"><i class="fas fa-cog"></i> Settings</a>
        <div class="dropdown-divider"></div>
        <a href="#" class="dropdown-item" style="color: #f87171 !important;"><i class="fas fa-sign-out-alt"></i>
          Logout</a>
      </div>
    </li>

  </ul>
</nav>