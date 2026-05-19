<nav class="portal-sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <i data-lucide="package" style="width:22px;height:22px;color:#0891b2;"></i>
            <span>Supply Chain</span>
        </div>
    </div>

    <div class="sidebar-section-label">OVERVIEW</div>
    <a href="{{ route('portals.staff.supply') }}"
       class="sidebar-link {{ request()->routeIs('portals.staff.supply') ? 'active' : '' }}">
        <i data-lucide="layout-dashboard"></i><span>Dashboard</span>
    </a>

    <div class="sidebar-section-label">CATALOG</div>
    <a href="{{ route('portals.staff.supply.items') }}"
       class="sidebar-link {{ request()->routeIs('portals.staff.supply.items') ? 'active' : '' }}">
        <i data-lucide="list"></i><span>Items Catalog</span>
    </a>
    <a href="{{ route('portals.staff.supply.suppliers') }}"
       class="sidebar-link {{ request()->routeIs('portals.staff.supply.suppliers') ? 'active' : '' }}">
        <i data-lucide="truck"></i><span>Suppliers</span>
    </a>

    <div class="sidebar-section-label">STOCK</div>
    <a href="{{ route('portals.staff.supply.stock') }}"
       class="sidebar-link {{ request()->routeIs('portals.staff.supply.stock') ? 'active' : '' }}">
        <i data-lucide="boxes"></i><span>Stock Levels</span>
    </a>
    <a href="{{ route('portals.staff.supply.movements') }}"
       class="sidebar-link {{ request()->routeIs('portals.staff.supply.movements') ? 'active' : '' }}">
        <i data-lucide="arrow-left-right"></i><span>Movements</span>
    </a>

    <div class="sidebar-section-label">PROCUREMENT</div>
    <a href="{{ route('portals.staff.supply.purchase_orders') }}"
       class="sidebar-link {{ request()->routeIs('portals.staff.supply.purchase_orders') ? 'active' : '' }}">
        <i data-lucide="file-text"></i><span>Purchase Orders</span>
    </a>
    <a href="{{ route('portals.staff.supply.goods_receipts') }}"
       class="sidebar-link {{ request()->routeIs('portals.staff.supply.goods_receipts') ? 'active' : '' }}">
        <i data-lucide="package-check"></i><span>Goods Receipts</span>
    </a>

    <div class="sidebar-section-label">NAVIGATE</div>
    <a href="{{ route('portals.staff') }}" class="sidebar-link">
        <i data-lucide="arrow-left"></i><span>Staff Portal</span>
    </a>
</nav>
