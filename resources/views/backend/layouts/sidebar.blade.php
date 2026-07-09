<style>
/* ========================================
   ULTRA PREMIUM SIDEBAR DESIGN
   ======================================== */

/* -------- ROOT VARIABLES -------- */
:root {
  --sidebar-bg: #070b14;
  --sidebar-bg-gradient: linear-gradient(180deg, #070b14 0%, #0f1a2e 50%, #0a0e1a 100%);
  --sidebar-width: 280px;
  --sidebar-collapsed-width: 70px;
  --primary-color: #6c8cff;
  --primary-hover: #8aa8ff;
  --primary-gradient: linear-gradient(135deg, #6c8cff, #a78bfa);
  --text-color: rgba(255, 255, 255, 0.9);
  --text-muted: rgba(255, 255, 255, 0.4);
  --text-hover: #6c8cff;
  --border-color: rgba(255, 255, 255, 0.05);
  --active-bg: rgba(108, 140, 255, 0.12);
  --active-border: rgba(108, 140, 255, 0.25);
  --hover-bg: rgba(108, 140, 255, 0.08);
  --transition-speed: 0.4s;
  --shadow-color: rgba(0, 0, 0, 0.5);
}

/* -------- MAIN SIDEBAR CONTAINER -------- */
.main-sidebar {
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  width: var(--sidebar-width);
  background: var(--sidebar-bg-gradient);
  border-right: 1px solid var(--border-color);
  box-shadow: 4px 0 50px rgba(0, 0, 0, 0.4), inset -1px 0 0 rgba(255, 255, 255, 0.02);
  z-index: 1040;
  transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.main-sidebar::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: radial-gradient(ellipse at 0% 50%, rgba(108, 140, 255, 0.03) 0%, transparent 70%);
  pointer-events: none;
}

.main-sidebar::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(108, 140, 255, 0.1), transparent);
}

.main-sidebar:hover {
  box-shadow: 4px 0 80px rgba(0, 0, 0, 0.5);
}

/* -------- SIDEBAR WRAPPER -------- */
#sidebar-wrapper {
  display: flex;
  flex-direction: column;
  height: 100%;
  overflow: hidden;
  position: relative;
}

/* -------- SIDEBAR BRAND - FULL -------- */
.sidebar-brand {
  padding: 20px 24px;
  border-bottom: 1px solid var(--border-color);
  min-height: 76px;
  display: flex;
  align-items: center;
  background: rgba(255, 255, 255, 0.02);
  position: relative;
  overflow: hidden;
}

.sidebar-brand::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(108, 140, 255, 0.05) 0%, transparent 60%);
  animation: brandGlow 8s ease-in-out infinite;
}

@keyframes brandGlow {

  0%,
  100% {
    transform: translate(0, 0);
  }

  25% {
    transform: translate(-10%, -10%);
  }

  50% {
    transform: translate(10%, -5%);
  }

  75% {
    transform: translate(-5%, 10%);
  }
}

.sidebar-brand a {
  color: #ffffff !important;
  font-weight: 800;
  font-size: 18px;
  letter-spacing: -0.5px;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 14px;
  transition: all var(--transition-speed) ease;
  position: relative;
  z-index: 1;
  width: 100%;
}

.sidebar-brand a::before {
  content: '';
  width: 8px;
  height: 8px;
  background: var(--primary-gradient);
  border-radius: 50%;
  display: inline-block;
  animation: statusDot 2s ease-in-out infinite;
  box-shadow: 0 0 20px rgba(108, 140, 255, 0.3);
  flex-shrink: 0;
}

@keyframes statusDot {

  0%,
  100% {
    transform: scale(1);
    opacity: 1;
  }

  50% {
    transform: scale(0.6);
    opacity: 0.5;
  }
}

.sidebar-brand a:hover {
  transform: translateX(4px);
  color: var(--primary-hover) !important;
}

/* -------- SIDEBAR BRAND SMALL -------- */
.sidebar-brand-sm {
  display: none;
  padding: 16px;
  border-bottom: 1px solid var(--border-color);
  min-height: 76px;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.02);
  position: relative;
}

.sidebar-brand-sm a {
  color: #ffffff !important;
  font-weight: 800;
  font-size: 20px;
  text-decoration: none;
  background: var(--primary-gradient);
  width: 44px;
  height: 44px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 25px rgba(108, 140, 255, 0.3);
  transition: all var(--transition-speed) ease;
  position: relative;
  overflow: hidden;
}

.sidebar-brand-sm a::after {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 60%);
  animation: shimmer 4s ease-in-out infinite;
}

@keyframes shimmer {

  0%,
  100% {
    transform: rotate(0deg) scale(1);
  }

  50% {
    transform: rotate(180deg) scale(1.2);
  }
}

.sidebar-brand-sm a:hover {
  transform: scale(1.08) rotate(-5deg);
  box-shadow: 0 6px 35px rgba(108, 140, 255, 0.5);
}

/* -------- SIDEBAR MENU -------- */
.sidebar-menu {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 16px 14px 20px;
  list-style: none;
  margin: 0;
}

/* -------- SCROLLBAR STYLING -------- */
.sidebar-menu::-webkit-scrollbar {
  width: 4px;
}

.sidebar-menu::-webkit-scrollbar-track {
  background: transparent;
}

.sidebar-menu::-webkit-scrollbar-thumb {
  background: rgba(108, 140, 255, 0.2);
  border-radius: 10px;
}

.sidebar-menu::-webkit-scrollbar-thumb:hover {
  background: rgba(108, 140, 255, 0.4);
}

/* -------- MENU HEADER -------- */
.sidebar-menu .menu-header {
  font-size: 10px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 2px;
  color: rgba(255, 255, 255, 0.2);
  padding: 24px 16px 10px;
  position: relative;
  display: flex;
  align-items: center;
  gap: 10px;
}

.sidebar-menu .menu-header::after {
  content: '';
  flex: 1;
  height: 1px;
  background: linear-gradient(90deg, rgba(255, 255, 255, 0.05), transparent);
}

.sidebar-menu .menu-header::before {
  content: '';
  width: 4px;
  height: 4px;
  background: var(--primary-color);
  border-radius: 50%;
  opacity: 0.3;
}

.sidebar-menu .menu-header:first-child {
  padding-top: 4px;
}

/* -------- MENU ITEMS -------- */
.sidebar-menu li {
  list-style: none;
  margin-bottom: 2px;
}

.sidebar-menu li a {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  color: var(--text-muted) !important;
  font-size: 13.5px;
  font-weight: 500;
  text-decoration: none;
  border-radius: 12px;
  transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  gap: 14px;
  cursor: pointer;
  background: transparent;
  border: none;
  width: 100%;
  text-align: left;
}

.sidebar-menu li a i {
  font-size: 18px;
  width: 24px;
  text-align: center;
  color: var(--text-muted);
  transition: all var(--transition-speed) ease;
  flex-shrink: 0;
}

.sidebar-menu li a span {
  flex: 1;
  font-weight: 500;
  letter-spacing: 0.2px;
  transition: all var(--transition-speed) ease;
}

/* -------- HOVER STATE -------- */
.sidebar-menu li a:hover {
  background: var(--hover-bg);
  color: var(--text-hover) !important;
  transform: translateX(6px);
  box-shadow: 0 2px 20px rgba(108, 140, 255, 0.05);
}

.sidebar-menu li a:hover i {
  color: var(--primary-color);
  transform: scale(1.15) translateX(2px);
}

.sidebar-menu li a:hover span {
  color: var(--text-hover);
}

/* -------- ACTIVE STATE -------- */
.sidebar-menu li.active>a {
  background: var(--active-bg);
  color: #ffffff !important;
  box-shadow: inset 0 0 0 1px var(--active-border), 0 4px 25px rgba(108, 140, 255, 0.05);
}

.sidebar-menu li.active>a::before {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 4px;
  height: 30px;
  background: var(--primary-gradient);
  border-radius: 0 6px 6px 0;
  box-shadow: 0 0 30px rgba(108, 140, 255, 0.3);
}

.sidebar-menu li.active>a i {
  color: var(--primary-color);
  transform: scale(1.05);
}

.sidebar-menu li.active>a span {
  color: #ffffff;
}

/* -------- DROPDOWN TOGGLE -------- */
.sidebar-menu li .has-dropdown {
  position: relative;
}

.sidebar-menu li .has-dropdown::after {
  content: '\f054';
  font-family: 'Font Awesome 5 Free';
  font-weight: 900;
  font-size: 10px;
  color: var(--text-muted);
  position: absolute;
  right: 16px;
  transition: all var(--transition-speed) ease;
  opacity: 0.5;
}

.sidebar-menu li .has-dropdown:hover::after {
  color: var(--primary-color);
  opacity: 1;
}

.sidebar-menu li .has-dropdown[aria-expanded="true"]::after {
  transform: rotate(90deg);
  color: var(--primary-color);
  opacity: 1;
}

/* -------- DROPDOWN MENU -------- */
.sidebar-menu .dropdown-menu {
  background: rgba(255, 255, 255, 0.02);
  border: none;
  border-radius: 10px;
  padding: 4px 0 4px 8px;
  margin-top: 4px;
  box-shadow: none;
  position: relative;
  display: none;
}

.sidebar-menu .dropdown-menu.show {
  display: block;
  animation: dropdownSlide 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes dropdownSlide {
  from {
    opacity: 0;
    transform: translateY(-8px) scale(0.97);
  }

  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.sidebar-menu .dropdown-menu::before {
  content: '';
  position: absolute;
  left: 16px;
  top: 8px;
  bottom: 8px;
  width: 1px;
  background: linear-gradient(to bottom, rgba(108, 140, 255, 0.1), transparent);
}

/* -------- DROPDOWN MENU ITEMS -------- */
.sidebar-menu .dropdown-menu li a {
  padding: 9px 16px 9px 36px;
  font-size: 12.5px;
  font-weight: 400;
  color: var(--text-muted) !important;
  border-radius: 8px;
}

.sidebar-menu .dropdown-menu li a i {
  font-size: 13px;
  width: 18px;
  color: var(--text-muted);
}

.sidebar-menu .dropdown-menu li a:hover {
  background: var(--hover-bg);
  color: var(--text-hover) !important;
  transform: translateX(6px);
}

.sidebar-menu .dropdown-menu li a:hover i {
  color: var(--primary-color);
  transform: scale(1.1);
}

.sidebar-menu .dropdown-menu li.active>a {
  background: rgba(108, 140, 255, 0.08);
  color: #ffffff !important;
  box-shadow: inset 0 0 0 1px rgba(108, 140, 255, 0.08);
}

.sidebar-menu .dropdown-menu li.active>a::before {
  content: '';
  position: absolute;
  left: 8px;
  top: 50%;
  transform: translateY(-50%);
  width: 2px;
  height: 16px;
  background: var(--primary-gradient);
  border-radius: 0 4px 4px 0;
  box-shadow: 0 0 20px rgba(108, 140, 255, 0.2);
}

/* -------- COLLAPSED SIDEBAR STYLES -------- */
.sidebar-collapse .main-sidebar {
  width: var(--sidebar-collapsed-width);
}

.sidebar-collapse .sidebar-brand {
  display: none;
}

.sidebar-collapse .sidebar-brand-sm {
  display: flex !important;
}

.sidebar-collapse .sidebar-menu .menu-header {
  display: none;
}

.sidebar-collapse .sidebar-menu li a span {
  display: none;
}

.sidebar-collapse .sidebar-menu li a i {
  font-size: 22px;
  width: 100%;
  margin: 0;
}

.sidebar-collapse .sidebar-menu li a {
  justify-content: center;
  padding: 14px;
  border-radius: 12px;
}

.sidebar-collapse .sidebar-menu li .has-dropdown::after {
  display: none;
}

.sidebar-collapse .sidebar-menu .dropdown-menu {
  display: none !important;
}

.sidebar-collapse .main-sidebar:hover {
  width: var(--sidebar-width);
}

.sidebar-collapse .main-sidebar:hover .sidebar-brand {
  display: flex;
}

.sidebar-collapse .main-sidebar:hover .sidebar-brand-sm {
  display: none;
}

.sidebar-collapse .main-sidebar:hover .sidebar-menu .menu-header {
  display: flex;
}

.sidebar-collapse .main-sidebar:hover .sidebar-menu li a span {
  display: inline;
}

.sidebar-collapse .main-sidebar:hover .sidebar-menu li a {
  justify-content: flex-start;
  padding: 12px 16px;
}

.sidebar-collapse .main-sidebar:hover .sidebar-menu li a i {
  font-size: 18px;
  width: 24px;
  margin-right: 0;
}

.sidebar-collapse .main-sidebar:hover .sidebar-menu li .has-dropdown::after {
  display: block;
}

.sidebar-collapse .main-sidebar:hover .sidebar-menu .dropdown-menu {
  display: block !important;
}

/* -------- SIDEBAR FOOTER -------- */
.sidebar-footer {
  padding: 16px 20px;
  border-top: 1px solid var(--border-color);
  margin-top: auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: rgba(255, 255, 255, 0.01);
}

.sidebar-footer .user-status {
  display: flex;
  align-items: center;
  gap: 10px;
}

.sidebar-footer .user-status .status-dot {
  width: 8px;
  height: 8px;
  background: linear-gradient(135deg, #2ed573, #7bed9f);
  border-radius: 50%;
  animation: statusPulse 2s ease-in-out infinite;
  box-shadow: 0 0 20px rgba(46, 213, 115, 0.2);
}

@keyframes statusPulse {

  0%,
  100% {
    transform: scale(1);
    opacity: 1;
  }

  50% {
    transform: scale(0.6);
    opacity: 0.5;
  }
}

.sidebar-footer .user-status .status-text {
  font-size: 11px;
  color: var(--text-muted);
  font-weight: 500;
}

.sidebar-footer .version {
  background: rgba(108, 140, 255, 0.08);
  padding: 4px 12px;
  border-radius: 50px;
  font-size: 9px;
  color: var(--primary-color);
  border: 1px solid rgba(108, 140, 255, 0.08);
  font-weight: 700;
  letter-spacing: 0.5px;
}

/* -------- TOOLTIP FOR COLLAPSED STATE -------- */
.sidebar-collapse .main-sidebar:not(:hover) .sidebar-menu li a {
  position: relative;
}

.sidebar-collapse .main-sidebar:not(:hover) .sidebar-menu li a span {
  display: none;
}

.sidebar-collapse .main-sidebar:not(:hover) .sidebar-menu li a::after {
  content: attr(data-tooltip);
  position: absolute;
  left: calc(100% + 15px);
  top: 50%;
  transform: translateY(-50%);
  background: rgba(10, 14, 26, 0.95);
  color: var(--text-hover);
  padding: 6px 14px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 500;
  white-space: nowrap;
  opacity: 0;
  visibility: hidden;
  transition: all 0.2s ease;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(108, 140, 255, 0.15);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  z-index: 1050;
}

.sidebar-collapse .main-sidebar:not(:hover) .sidebar-menu li a:hover::after {
  opacity: 1;
  visibility: visible;
  transform: translateY(-50%) translateX(4px);
}

/* -------- RESPONSIVE DESIGN -------- */
@media (max-width: 991.98px) {
  .main-sidebar {
    transform: translateX(-100%);
    box-shadow: none;
    width: var(--sidebar-width) !important;
  }

  .main-sidebar.show {
    transform: translateX(0);
    box-shadow: 4px 0 80px rgba(0, 0, 0, 0.6);
  }

  .sidebar-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    z-index: 1039;
    backdrop-filter: blur(8px);
  }

  .sidebar-overlay.show {
    display: block;
    animation: overlayFade 0.3s ease;
  }

  @keyframes overlayFade {
    from {
      opacity: 0;
    }

    to {
      opacity: 1;
    }
  }

  .sidebar-brand-sm {
    display: flex !important;
  }

  .sidebar-brand {
    display: none !important;
  }

  .main-sidebar:hover {
    width: var(--sidebar-width) !important;
  }

  .main-sidebar:hover .sidebar-brand {
    display: none !important;
  }

  .main-sidebar:hover .sidebar-brand-sm {
    display: flex !important;
  }
}

/* -------- DARK THEME COMPATIBILITY -------- */
@media (prefers-color-scheme: dark) {
  :root {
    --sidebar-bg: #040810;
    --text-color: rgba(255, 255, 255, 0.92);
    --text-muted: rgba(255, 255, 255, 0.3);
    --hover-bg: rgba(108, 140, 255, 0.06);
  }
}

/* -------- ANIMATIONS -------- */
@keyframes slideInLeft {
  from {
    opacity: 0;
    transform: translateX(-20px);
  }

  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.sidebar-menu li {
  animation: slideInLeft 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards;
  opacity: 0;
}

.sidebar-menu li:nth-child(1) {
  animation-delay: 0.03s;
}

.sidebar-menu li:nth-child(2) {
  animation-delay: 0.06s;
}

.sidebar-menu li:nth-child(3) {
  animation-delay: 0.09s;
}

.sidebar-menu li:nth-child(4) {
  animation-delay: 0.12s;
}

.sidebar-menu li:nth-child(5) {
  animation-delay: 0.15s;
}

.sidebar-menu li:nth-child(6) {
  animation-delay: 0.18s;
}

.sidebar-menu li:nth-child(7) {
  animation-delay: 0.21s;
}

.sidebar-menu li:nth-child(8) {
  animation-delay: 0.24s;
}

.sidebar-menu li:nth-child(9) {
  animation-delay: 0.27s;
}

.sidebar-menu li:nth-child(10) {
  animation-delay: 0.30s;
}

.sidebar-menu li:nth-child(11) {
  animation-delay: 0.33s;
}

.sidebar-menu li:nth-child(12) {
  animation-delay: 0.36s;
}

.sidebar-menu li:nth-child(13) {
  animation-delay: 0.39s;
}

.sidebar-menu li:nth-child(14) {
  animation-delay: 0.42s;
}

.sidebar-menu li:nth-child(15) {
  animation-delay: 0.45s;
}

/* -------- DROPDOWN DIVIDER -------- */
.sidebar-menu .dropdown-menu .divider {
  margin: 6px 10px;
  border-top: 1px solid rgba(255, 255, 255, 0.04);
}

/* -------- SIDEBAR TOGGLE BUTTON (Mobile) -------- */
.sidebar-toggle-btn {
  display: none;
  position: fixed;
  bottom: 20px;
  left: 20px;
  background: var(--primary-gradient);
  color: #fff;
  border: none;
  border-radius: 50%;
  width: 50px;
  height: 50px;
  font-size: 20px;
  box-shadow: 0 4px 25px rgba(108, 140, 255, 0.3);
  cursor: pointer;
  z-index: 1050;
  transition: all var(--transition-speed) ease;
}

.sidebar-toggle-btn:hover {
  transform: scale(1.1);
  box-shadow: 0 6px 35px rgba(108, 140, 255, 0.5);
}

@media (max-width: 991.98px) {
  .sidebar-toggle-btn {
    display: flex;
    align-items: center;
    justify-content: center;
  }
}
</style>

<!-- ========================================
   SIDEBAR HTML - UPDATED
   ======================================== -->
<!-- Sidebar Overlay (Mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="main-sidebar sidebar-style-2" id="mainSidebar">
  <aside id="sidebar-wrapper">
    <!-- Brand Logo - Full -->
    <div class="sidebar-brand">
      <a href="{{ route('admin.dashboard') }}">
        {{ Auth::user()->name }}
      </a>
    </div>

    <!-- Brand Logo - Small -->
    <div class="sidebar-brand-sm">
      <a href="{{ route('admin.dashboard') }}">
        {{ substr(Auth::user()->name, 0, 2) }}
      </a>
    </div>

    <!-- Sidebar Menu -->
    <ul class="sidebar-menu">
      <!-- Dashboard -->
      <li class="menu-header">Main</li>
      <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <a href="{{ route('admin.dashboard') }}" class="nav-link" data-tooltip="Dashboard">
          <i class="fas fa-fire"></i>
          <span>Dashboard</span>
        </a>
      </li>

      <!-- E-Commerce Section -->
      @canany(['Manage Categories', 'Manage Products', 'Manage Order Place', 'Manage Order Receive'])
      <li class="menu-header">E-Commerce</li>
      @endcanany

      <!-- Categories -->
      @can('Manage Categories')
      <li
        class="dropdown {{ setActive(['admin.category.*', 'admin.sub-category.*', 'admin.child-category.*', 'admin.slider.*']) }}">
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown" data-tooltip="Categories">
          <i class="fas fa-list"></i>
          <span>Manage Categories</span>
        </a>
        <ul class="dropdown-menu">
          <li class="{{ setActive(['admin.category.*']) }}">
            <a class="nav-link" href="{{ route('admin.category.index') }}">
              <i class="fas fa-folder"></i> Category
            </a>
          </li>
          <li class="{{ setActive(['admin.sub-category.*']) }}">
            <a class="nav-link" href="{{ route('admin.sub-category.index') }}">
              <i class="fas fa-folder-open"></i> Sub Category
            </a>
          </li>
          <li class="{{ setActive(['admin.child-category.*']) }}">
            <a class="nav-link" href="{{ route('admin.child-category.index') }}">
              <i class="fas fa-level-down-alt"></i> Child Category
            </a>
          </li>
          <li class="divider"></li>
          <li class="{{ setActive(['admin.slider.*']) }}">
            <a class="nav-link" href="{{ route('admin.slider.index') }}">
              <i class="fas fa-images"></i> Slider
            </a>
          </li>
          <li class="{{ setActive(['admin.product-types.*']) }}">
            <a class="nav-link" href="{{ route('admin.product-types.index') }}">
              <i class="fas fa-layer-group"></i> Occasion Type
            </a>
          </li>
        </ul>
      </li>
      @endcan

      <!-- Products -->
      @canany(['Manage Products', 'View Product Stock'])
      <li class="menu-header">Products</li>
      <li class="dropdown {{ setActive(['admin.products.*', 'admin.units.*', 'admin.colors.*', 'admin.sizes.*']) }}">
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown" data-tooltip="Products">
          <i class="fas fa-box"></i>
          <span>Manage Products</span>
        </a>
        <ul class="dropdown-menu">
          <li class="{{ setActive(['admin.products.*']) }}">
            <a class="nav-link" href="{{ route('admin.products.index') }}">
              <i class="fas fa-boxes"></i> Products
            </a>
          </li>
          @can('Manage Products')
          <li class="divider"></li>
          <li class="{{ setActive(['admin.units.*']) }}">
            <a class="nav-link" href="{{ route('admin.units.index') }}">
              <i class="fas fa-balance-scale"></i> Units
            </a>
          </li>
          <li class="{{ setActive(['admin.colors.*']) }}">
            <a class="nav-link" href="{{ route('admin.colors.index') }}">
              <i class="fas fa-palette"></i> Colors
            </a>
          </li>
          <li class="{{ setActive(['admin.sizes.*']) }}">
            <a class="nav-link" href="{{ route('admin.sizes.index') }}">
              <i class="fas fa-ruler"></i> Sizes
            </a>
          </li>
          @endcan
        </ul>
      </li>
      @endcanany

      <!-- Inventory -->
      @can('Manage Inventory')
      <li class="menu-header">Inventory</li>
      <li
        class="dropdown {{ setActive(['admin.issues.*', 'admin.issue-returns.*', 'admin.reports.stock', 'admin.stock-ledger.index', 'admin.inventory-reports.index']) }}">
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown" data-tooltip="Inventory">
          <i class="fas fa-warehouse"></i>
          <span>Inventory</span>
        </a>
        <ul class="dropdown-menu">
          <li class="{{ setActive(['admin.inventory-reports.index']) }}">
            <a class="nav-link" href="{{ route('admin.inventory-reports.index') }}">
              <i class="fas fa-boxes"></i> Current Stock
            </a>
          </li>
          <li class="{{ setActive(['admin.issues.index']) }}">
            <a class="nav-link" href="{{ route('admin.issues.index') }}">
              <i class="fas fa-dolly"></i> Stock Issues
            </a>
          </li>
          <li class="{{ setActive(['admin.issue-returns.*']) }}">
            <a class="nav-link" href="{{ route('admin.issue-returns.index') }}">
              <i class="fas fa-undo"></i> Stock Returns
            </a>
          </li>
          <li class="divider"></li>
          <li class="{{ setActive(['admin.stock-ledger.index']) }}">
            <a class="nav-link" href="{{ route('admin.stock-ledger.index') }}">
              <i class="fas fa-history"></i> Stock Ledger
            </a>
          </li>
        </ul>
      </li>
      @endcan

      <!-- Order Place & Receive -->
      @canany(['Manage Order Place', 'Manage Order Receive'])
      @can('Manage Order Place')
      <li class="menu-header">Orders</li>
      <li
        class="dropdown {{ setActive(['admin.orders.*', 'admin.product-requests.*', 'admin.custom-product-requests.*']) }}">
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown" data-tooltip="Order Place">
          <i class="fas fa-book"></i>
          <span>Manage Request</span>
        </a>
        <ul class="dropdown-menu">
          <li class="{{ setActive(['admin.orders.*']) }}">
            <a class="nav-link" href="{{ route('admin.orders.index') }}">
              <i class="fas fa-shopping-bag"></i> Frontend Orders
            </a>
          </li>
          @canany(['Manage Custom Product Requests', 'View Custom Product Requests'])
          <li class="{{ setActive(['admin.custom-product-requests.*']) }}">
            <a class="nav-link" href="{{ route('admin.custom-product-requests.index') }}">
              <i class="fas fa-list-alt"></i> Custom Requests
            </a>
          </li>
          @endcanany
          @if(Auth::user()->hasRole('Admin'))
          <li class="divider"></li>
          <li class="{{ setActive(['admin.product-requests.index']) }}">
            <a class="nav-link" href="{{ route('admin.product-requests.index') }}">
              <i class="fas fa-clipboard-list"></i> Old Requests
            </a>
          </li>
          <li class="{{ setActive(['admin.product-requests.create']) }}">
            <a class="nav-link" href="{{ route('admin.product-requests.create') }}">
              <i class="fas fa-plus"></i> Create Request
            </a>
          </li>
          @endif
        </ul>
      </li>
      @endcan

      @can('Manage Order Receive')
      <li class="dropdown {{ setActive(['admin.purchases.*','admin.bookings.*']) }}">
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown" data-tooltip="Order Receive">
          <i class="fas fa-shopping-cart"></i>
          <span>Manage Order Place/Receive</span>
        </a>
        <ul class="dropdown-menu">
          <li class="{{ setActive(['admin.bookings.*']) }}">
            <a class="nav-link" href="{{ route('admin.bookings.index') }}">
              <i class="fas fa-calendar-check"></i> Order Place
            </a>
          </li>
          <li class="{{ setActive(['admin.purchases.index']) }}">
            <a class="nav-link" href="{{ route('admin.purchases.index') }}">
              <i class="fas fa-receipt"></i> Order Receive
            </a>
          </li>
          <li class="divider"></li>
          <li class="{{ setActive(['admin.purchases.create']) }}">
            <a class="nav-link" href="{{ route('admin.purchases.create') }}">
              <i class="fas fa-plus"></i> Create New
            </a>
          </li>
        </ul>
      </li>
      @endcan
      @endcanany

      <!-- Reports -->
      @can('Manage Reports')
      <li class="menu-header">Analytics</li>
      <li class="dropdown {{ setActive(['admin.reports.*', 'admin.reports.orders']) }}">
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown" data-tooltip="Reports">
          <i class="fas fa-chart-line"></i>
          <span>Reports</span>
        </a>
        <ul class="dropdown-menu">
          <li class="{{ setActive(['admin.reports.index']) }}">
            <a class="nav-link" href="{{ route('admin.reports.index') }}">
              <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
          </li>
          <li class="{{ setActive(['admin.reports.orders']) }}">
            <a class="nav-link" href="{{ route('admin.reports.orders') }}">
              <i class="fas fa-file-invoice"></i> Order & Issue Report
            </a>
          </li>
          <li class="divider"></li>
          <li class="{{ setActive(['admin.reports.stock']) }}">
            <a class="nav-link" href="{{ route('admin.reports.stock') }}">
              <i class="fas fa-boxes"></i> Stock Valuation
            </a>
          </li>
          <li class="{{ setActive(['admin.reports.purchase']) }}">
            <a class="nav-link" href="{{ route('admin.reports.purchase') }}">
              <i class="fas fa-history"></i> Purchase History
            </a>
          </li>
          <li class="{{ setActive(['admin.reports.product-purchase-history']) }}">
            <a class="nav-link" href="{{ route('admin.reports.product-purchase-history') }}">
              <i class="fas fa-map-marker-alt"></i> Product Tracking
            </a>
          </li>
          <li class="{{ setActive(['admin.reports.low-stock']) }}">
            <a class="nav-link" href="{{ route('admin.reports.low-stock') }}">
              <i class="fas fa-exclamation-triangle"></i> Low Stock Alert
            </a>
          </li>
          <li class="divider"></li>
          <li class="{{ setActive(['admin.reports.profit-loss']) }}">
            <a class="nav-link" href="{{ route('admin.reports.profit-loss') }}">
              <i class="fas fa-chart-bar"></i> Profit & Loss
            </a>
          </li>
          <li class="{{ setActive(['admin.reports.audit']) }}">
            <a class="nav-link" href="{{ route('admin.reports.audit') }}">
              <i class="fas fa-user-shield"></i> Audit Report
            </a>
          </li>
        </ul>
      </li>
      @endcan

      <!-- Accounts -->
      @if(Auth::user()->hasRole('Admin'))
      <li class="menu-header">Finance</li>
      <li class="dropdown {{ setActive(['admin.accounts.*']) }}">
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown" data-tooltip="Accounts">
          <i class="fas fa-file-invoice-dollar"></i>
          <span>Accounts</span>
        </a>
        <ul class="dropdown-menu">
          <li class="{{ setActive(['admin.accounts.index']) }}">
            <a class="nav-link" href="{{ route('admin.accounts.index') }}">
              <i class="fas fa-list-ul"></i> Customer Transactions
            </a>
          </li>
          <li class="{{ setActive(['admin.accounts.record-payment']) }}">
            <a class="nav-link" href="{{ route('admin.accounts.record-payment') }}">
              <i class="fas fa-plus-circle"></i> Receive Payment
            </a>
          </li>
          <li class="{{ setActive(['admin.accounts.due-orders']) }}">
            <a class="nav-link" href="{{ route('admin.accounts.due-orders') }}">
              <i class="fas fa-exclamation-circle"></i> Customer Due Orders
            </a>
          </li>
          <li class="divider"></li>
          <li class="{{ setActive(['admin.accounts.vendor-payments.index']) }}">
            <a class="nav-link" href="{{ route('admin.accounts.vendor-payments.index') }}">
              <i class="fas fa-hand-holding-usd"></i> Vendor Payments
            </a>
          </li>
          <li class="{{ setActive(['admin.accounts.vendor-payments.record-payment']) }}">
            <a class="nav-link" href="{{ route('admin.accounts.vendor-payments.record-payment') }}">
              <i class="fas fa-file-invoice-dollar"></i> Pay Vendor Invoice
            </a>
          </li>
          <li class="{{ setActive(['admin.accounts.vendor-payments.due-purchases']) }}">
            <a class="nav-link" href="{{ route('admin.accounts.vendor-payments.due-purchases') }}">
              <i class="fas fa-exclamation-circle"></i> Vendor Due Purchases
            </a>
          </li>
        </ul>
      </li>
      @endif

      <!-- Brands & Vendors -->
      @canany(['Manage Brands', 'Manage Vendors'])
      <li class="menu-header">Partners</li>
      @can('Manage Brands')
      <li class="dropdown {{ setActive(['admin.brand.*']) }}">
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown" data-tooltip="Brands">
          <i class="fas fa-tag"></i>
          <span>Brands</span>
        </a>
        <ul class="dropdown-menu">
          <li class="{{ setActive(['admin.brand.index']) }}">
            <a class="nav-link" href="{{ route('admin.brand.index') }}">
              <i class="fas fa-list"></i> All Brands
            </a>
          </li>
          <li class="{{ setActive(['admin.brand.create']) }}">
            <a class="nav-link" href="{{ route('admin.brand.create') }}">
              <i class="fas fa-plus-circle"></i> Add Brand
            </a>
          </li>
        </ul>
      </li>
      @endcan

      @can('Manage Vendors')
      <li class="dropdown {{ setActive(['admin.vendor.*']) }}">
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown" data-tooltip="Vendors">
          <i class="fas fa-truck"></i>
          <span>Vendors</span>
        </a>
        <ul class="dropdown-menu">
          <li class="{{ setActive(['admin.vendor.index']) }}">
            <a class="nav-link" href="{{ route('admin.vendor.index') }}">
              <i class="fas fa-list"></i> All Vendors
            </a>
          </li>
          <li class="{{ setActive(['admin.vendor.create']) }}">
            <a class="nav-link" href="{{ route('admin.vendor.create') }}">
              <i class="fas fa-plus-circle"></i> Add Vendor
            </a>
          </li>
        </ul>
      </li>
      @endcan
      @endcanany

      <!-- System -->
      @can('Administration')
      <li class="menu-header">System</li>
      <li
        class="dropdown {{ setActive(['admin.permission.*', 'admin.role.*', 'admin.users.*', 'admin.settings.*', 'admin.pricing-rules.*', 'admin.taxes.*', 'admin.discounts.*', 'admin.products.announcement.*']) }}">
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown" data-tooltip="Administration">
          <i class="fas fa-cogs"></i>
          <span>Administration</span>
        </a>
        <ul class="dropdown-menu">
          <li class="{{ setActive(['admin.users.*']) }}">
            <a class="nav-link" href="{{ route('admin.users.index') }}">
              <i class="fas fa-users-cog"></i> Users
            </a>
          </li>
          <li class="{{ setActive(['admin.permission.*']) }}">
            <a class="nav-link" href="{{ route('admin.permission.index') }}">
              <i class="fas fa-lock"></i> Permissions
            </a>
          </li>
          <li class="{{ setActive(['admin.role.*']) }}">
            <a class="nav-link" href="{{ route('admin.role.index') }}">
              <i class="fas fa-user-tag"></i> Roles
            </a>
          </li>
          <li class="divider"></li>
          <li class="{{ setActive(['admin.pricing-rules.*']) }}">
            <a class="nav-link" href="{{ route('admin.pricing-rules.index') }}">
              <i class="fas fa-dollar-sign"></i> Pricing Rules
            </a>
          </li>
          <li class="{{ setActive(['admin.taxes.*']) }}">
            <a class="nav-link" href="{{ route('admin.taxes.index') }}">
              <i class="fas fa-percent"></i> Tax / VAT
            </a>
          </li>
          <li class="{{ setActive(['admin.discounts.*']) }}">
            <a class="nav-link" href="{{ route('admin.discounts.index') }}">
              <i class="fas fa-tags"></i> Discount
            </a>
          </li>
          <li class="divider"></li>
          <li class="{{ setActive(['admin.products.announcement.*']) }}">
            <a class="nav-link" href="{{ route('admin.products.announcement.index') }}">
              <i class="fas fa-bullhorn"></i> Product Announcement
            </a>
          </li>
          <li class="{{ setActive(['admin.settings.*']) }}">
            <a class="nav-link" href="{{ route('admin.settings.index') }}">
              <i class="fas fa-sliders-h"></i> Settings
            </a>
          </li>
        </ul>
      </li>
      @endcan
    </ul>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
      <div class="user-status">
        <span class="status-dot"></span>
        <span class="status-text">Online</span>
      </div>
      <span class="version">
        <i class="fas fa-code-branch"></i> v2.0
      </span>
    </div>
  </aside>
</div>

<!-- Sidebar Toggle Button (Mobile) -->
<button class="sidebar-toggle-btn" id="sidebarToggleBtn" aria-label="Toggle Sidebar">
  <i class="fas fa-bars"></i>
</button>

<!-- ========================================
   JAVASCRIPT
   ======================================== -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const sidebar = document.getElementById('mainSidebar');
  const overlay = document.getElementById('sidebarOverlay');
  const toggleBtn = document.getElementById('sidebarToggleBtn');

  // Toggle sidebar function
  function toggleSidebar() {
    sidebar.classList.toggle('show');
    overlay.classList.toggle('show');
    document.body.classList.toggle('sidebar-open');
  }

  // Close sidebar function
  function closeSidebar() {
    sidebar.classList.remove('show');
    overlay.classList.remove('show');
    document.body.classList.remove('sidebar-open');
  }

  // Toggle button click
  if (toggleBtn) {
    toggleBtn.addEventListener('click', toggleSidebar);
  }

  // Data-toggle buttons click
  document.querySelectorAll('[data-toggle="sidebar"]').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      toggleSidebar();
    });
  });

  // Close sidebar on overlay click
  if (overlay) {
    overlay.addEventListener('click', closeSidebar);
  }

  // Close sidebar on ESC key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && sidebar.classList.contains('show')) {
      closeSidebar();
    }
  });

  // Handle dropdown toggles
  document.querySelectorAll('.sidebar-menu .has-dropdown').forEach(toggle => {
    toggle.addEventListener('click', function(e) {
      const parent = this.closest('.dropdown');
      if (parent) {
        e.preventDefault();
        const menu = parent.querySelector('.dropdown-menu');
        if (menu) {
          const isOpen = menu.classList.contains('show');
          // Close all other dropdowns
          document.querySelectorAll('.sidebar-menu .dropdown-menu').forEach(m => {
            m.classList.remove('show');
            m.closest('.dropdown').classList.remove('open');
            const t = m.closest('.dropdown').querySelector('.has-dropdown');
            if (t) t.setAttribute('aria-expanded', 'false');
          });
          if (!isOpen) {
            menu.classList.add('show');
            parent.classList.add('open');
            this.setAttribute('aria-expanded', 'true');
          } else {
            menu.classList.remove('show');
            parent.classList.remove('open');
            this.setAttribute('aria-expanded', 'false');
          }
        }
      }
    });
  });

  // Set default open state for active dropdown
  document.querySelectorAll('.sidebar-menu .dropdown.active').forEach(dropdown => {
    const menu = dropdown.querySelector('.dropdown-menu');
    const toggle = dropdown.querySelector('.has-dropdown');
    if (menu) {
      menu.classList.add('show');
      dropdown.classList.add('open');
      if (toggle) {
        toggle.setAttribute('aria-expanded', 'true');
      }
    }
  });

  // Auto-close sidebar on window resize (desktop)
  window.addEventListener('resize', function() {
    if (window.innerWidth > 991.98) {
      closeSidebar();
    }
  });

  // Close sidebar when clicking a link (mobile)
  document.querySelectorAll('.sidebar-menu a:not(.has-dropdown)').forEach(link => {
    link.addEventListener('click', function() {
      if (window.innerWidth <= 991.98) {
        closeSidebar();
      }
    });
  });
});
</script>