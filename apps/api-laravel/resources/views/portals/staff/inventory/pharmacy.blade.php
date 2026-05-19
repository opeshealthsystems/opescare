@extends('layouts.portal')

@section('title', 'Pharmacy Inventory')

@section('sidebar_role_badge')
<div class="sidebar-role-badge">{{ __('public.staff_portal.role_clinical_staff', [], app()->getLocale()) ?: 'Clinical Staff' }}</div>
@endsection
@section('sidebar_user_role', __('public.staff_portal.role_clinical_staff', [], app()->getLocale()) ?: 'Clinical Staff')

@section('sidebar_nav')
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Overview</div>
    <a href="{{ route('portals.staff') }}" class="sidebar-link">
        <i data-lucide="layout-dashboard"></i>
        <span>{{ __('public.portal.nav_dashboard', [], app()->getLocale()) ?: 'Dashboard' }}</span>
    </a>
    <a href="{{ route('portals.staff.analytics') }}" class="sidebar-link">
        <i data-lucide="bar-chart-2"></i>
        <span>{{ __('public.portal.nav_analytics', [], app()->getLocale()) ?: 'Analytics' }}</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Clinical</div>
    <a href="{{ route('portals.staff.appointments') }}" class="sidebar-link">
        <i data-lucide="calendar-check-2"></i>
        <span>{{ __('public.portal.nav_appointments', [], app()->getLocale()) ?: 'Appointments' }}</span>
    </a>
    <a href="{{ route('portals.staff.queue') }}" class="sidebar-link">
        <i data-lucide="list-ordered"></i>
        <span>{{ __('public.portal.nav_queue', [], app()->getLocale()) ?: 'Patient Queue' }}</span>
    </a>
    <a href="{{ route('portals.staff.visits') }}" class="sidebar-link">
        <i data-lucide="stethoscope"></i>
        <span>{{ __('public.portal.nav_visits', [], app()->getLocale()) ?: 'Visits' }}</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">HR & Staff</div>
    <a href="{{ route('portals.staff.hr.directory') }}" class="sidebar-link">
        <i data-lucide="users"></i>
        <span>{{ __('public.portal.nav_staff_directory', [], app()->getLocale()) ?: 'Directory' }}</span>
    </a>
    <a href="{{ route('portals.staff.hr.roster') }}" class="sidebar-link">
        <i data-lucide="calendar-range"></i>
        <span>{{ __('public.portal.nav_staff_roster', [], app()->getLocale()) ?: 'Duty Roster' }}</span>
    </a>
    <a href="{{ route('portals.staff.hr.leave') }}" class="sidebar-link">
        <i data-lucide="plane-takeoff"></i>
        <span>{{ __('public.portal.nav_staff_leave', [], app()->getLocale()) ?: 'Leave' }}</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Inventory</div>
    <a href="{{ route('portals.staff.inventory.pharmacy') }}" class="sidebar-link active">
        <i data-lucide="pill"></i>
        <span>{{ __('public.portal.nav_inventory_pharmacy', [], app()->getLocale()) ?: 'Pharmacy' }}</span>
    </a>
    <a href="{{ route('portals.staff.inventory.blood') }}" class="sidebar-link">
        <i data-lucide="droplets"></i>
        <span>{{ __('public.portal.nav_inventory_blood', [], app()->getLocale()) ?: 'Blood Bank' }}</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Operations</div>
    <a href="{{ route('portals.staff.billing') }}" class="sidebar-link">
        <i data-lucide="receipt"></i>
        <span>{{ __('public.portal.nav_billing', [], app()->getLocale()) ?: 'Billing' }}</span>
    </a>
    <a href="{{ route('portals.staff.support') }}" class="sidebar-link">
        <i data-lucide="headset"></i>
        <span>{{ __('public.portal.nav_support', [], app()->getLocale()) ?: 'Support' }}</span>
    </a>
    <a href="{{ route('portals.staff.data_import.index') }}" class="sidebar-link">
        <i data-lucide="upload-cloud"></i>
        <span>{{ __('public.portal.nav_data_import', [], app()->getLocale()) ?: 'Data Import' }}</span>
    </a>
    <a href="{{ route('portals.staff.search') }}" class="sidebar-link {{ request()->routeIs('portals.staff.search') ? 'active' : '' }}">
        <i data-lucide="search"></i>
        <span>{{ __('public.portal.nav_search', [], app()->getLocale()) ?: 'Global Search' }}</span>
    </a>
    <a href="{{ route('portals.staff.files.index') }}" class="sidebar-link {{ request()->routeIs('portals.staff.files*') ? 'active' : '' }}">
        <i data-lucide="paperclip"></i>
        <span>{{ __('public.portal.nav_files', [], app()->getLocale()) ?: 'Files & Attachments' }}</span>
    </a>
    <a href="{{ route('portals.staff.wards') }}" class="sidebar-link {{ request()->routeIs('portals.staff.wards*') ? 'active' : '' }}">
        <i data-lucide="bed"></i>
        <span>{{ __('public.portal.nav_wards', [], app()->getLocale()) ?: 'Wards & Beds' }}</span>
    </a>
</div>
@endsection

@section('breadcrumb_home', __('public.staff_portal.title', [], app()->getLocale()) ?: 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Pharmacy Inventory')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Pharmacy Inventory</h1>
        <p class="page-subtitle">Manage medicine stock levels, flag recalls, and track availability.</p>
    </div>
    <button type="button" class="btn btn-primary btn-sm" onclick="openAddModal()">
        <i data-lucide="plus-circle" style="width:14px;height:14px;"></i>
        Add Item
    </button>
</div>

@if(session('success'))
    <div class="auth-alert auth-alert-success" style="margin-bottom:1rem;">
        <i data-lucide="check-circle"></i><div>{{ session('success') }}</div>
    </div>
@endif
@if(session('error'))
    <div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;">
        <i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div>
    </div>
@endif

{{-- Summary Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:.75rem;margin-bottom:1.25rem;">
    <div class="panel" style="text-align:center;padding:1rem .5rem;">
        <div style="font-size:1.75rem;font-weight:700;color:var(--p-primary);">{{ $summary['total'] }}</div>
        <div style="font-size:.75rem;color:var(--p-text-secondary);">Total Items</div>
    </div>
    <div class="panel" style="text-align:center;padding:1rem .5rem;">
        <div style="font-size:1.75rem;font-weight:700;color:var(--p-success);">{{ $summary['in_stock'] }}</div>
        <div style="font-size:.75rem;color:var(--p-text-secondary);">In Stock</div>
    </div>
    <div class="panel" style="text-align:center;padding:1rem .5rem;">
        <div style="font-size:1.75rem;font-weight:700;color:var(--p-warning);">{{ $summary['low_stock'] }}</div>
        <div style="font-size:.75rem;color:var(--p-text-secondary);">Low Stock</div>
    </div>
    <div class="panel" style="text-align:center;padding:1rem .5rem;">
        <div style="font-size:1.75rem;font-weight:700;color:var(--p-danger);">{{ $summary['out_of_stock'] }}</div>
        <div style="font-size:.75rem;color:var(--p-text-secondary);">Out of Stock</div>
    </div>
    <div class="panel" style="text-align:center;padding:1rem .5rem;">
        <div style="font-size:1.75rem;font-weight:700;color:var(--p-danger);">{{ $summary['expired'] }}</div>
        <div style="font-size:.75rem;color:var(--p-text-secondary);">Expired</div>
    </div>
    <div class="panel" style="text-align:center;padding:1rem .5rem;">
        <div style="font-size:1.75rem;font-weight:700;color:var(--p-warning);">{{ $summary['recalled'] }}</div>
        <div style="font-size:.75rem;color:var(--p-text-secondary);">Recalled</div>
    </div>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('portals.staff.inventory.pharmacy') }}" class="filter-bar" style="flex-wrap:wrap;gap:.5rem;">
    <select name="stock_status" class="form-control">
        <option value="">All Statuses</option>
        @foreach(['in_stock','low_stock','out_of_stock'] as $s)
            <option value="{{ $s }}" {{ request('stock_status') === $s ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
        @endforeach
    </select>
    @if($forms->isNotEmpty())
    <select name="form" class="form-control">
        <option value="">All Forms</option>
        @foreach($forms as $f)
            <option value="{{ $f }}" {{ request('form') === $f ? 'selected' : '' }}>{{ $f }}</option>
        @endforeach
    </select>
    @endif
    <select name="is_expired" class="form-control">
        <option value="">All</option>
        <option value="1" {{ request('is_expired') === '1' ? 'selected' : '' }}>Expired Only</option>
        <option value="0" {{ request('is_expired') === '0' ? 'selected' : '' }}>Not Expired</option>
    </select>
    <input type="text" name="search" class="form-control" placeholder="Search medicine…" value="{{ request('search') }}">
    <button type="submit" class="btn btn-primary btn-sm">
        <i data-lucide="filter" style="width:13px;height:13px;"></i> Filter
    </button>
    <a href="{{ route('portals.staff.inventory.pharmacy') }}" class="btn btn-ghost btn-sm">Clear</a>
</form>

<div class="panel">
    <div class="panel-body" style="padding:0;">
        @if($items->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="pill"></i></div>
                <h3>No Inventory Items</h3>
                <p>Add your first medicine item to begin tracking stock.</p>
                <button type="button" class="btn btn-primary btn-sm" style="margin-top:1rem;" onclick="openAddModal()">Add Item</button>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Medicine</th>
                            <th>Form / Strength</th>
                            <th>Qty</th>
                            <th>Status</th>
                            <th>Flags</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                        @php
                            $sBadge = match($item->stock_status) {
                                'in_stock'     => 'badge-success',
                                'low_stock'    => 'badge-warning',
                                'out_of_stock' => 'badge-danger',
                                default        => 'badge-neutral',
                            };
                        @endphp
                        <tr>
                            <td data-label="Medicine">
                                <strong>{{ $item->medicine_name }}</strong>
                                <br><span style="font-size:var(--p-text-xs);color:var(--p-text-secondary);">{{ $item->generic_name }}</span>
                            </td>
                            <td data-label="Form / Strength">{{ $item->form }} · {{ $item->strength }}</td>
                            <td data-label="Qty"><strong>{{ number_format($item->available_quantity) }}</strong></td>
                            <td data-label="Status">
                                <span class="badge {{ $sBadge }}">{{ ucwords(str_replace('_',' ',$item->stock_status)) }}</span>
                            </td>
                            <td data-label="Flags">
                                @if($item->is_expired)   <span class="badge badge-danger" style="font-size:.65rem;margin:.1rem;">Expired</span> @endif
                                @if($item->is_recalled)  <span class="badge badge-warning" style="font-size:.65rem;margin:.1rem;">Recalled</span> @endif
                                @if($item->is_quarantined) <span class="badge badge-warning" style="font-size:.65rem;margin:.1rem;">Quarantine</span> @endif
                                @if(!$item->is_expired && !$item->is_recalled && !$item->is_quarantined)
                                    <span style="color:var(--p-text-secondary);font-size:var(--p-text-xs);">—</span>
                                @endif
                            </td>
                            <td data-label="Last Updated" style="font-size:var(--p-text-xs);">
                                {{ \Carbon\Carbon::parse($item->last_stock_update)->format('M d, H:i') }}
                            </td>
                            <td data-label="Actions">
                                <div style="display:flex;gap:.35rem;flex-wrap:wrap;">
                                    <button type="button" class="btn btn-primary btn-xs"
                                        onclick="openRestockModal('{{ $item->id }}', '{{ addslashes($item->medicine_name) }}')">
                                        <i data-lucide="plus" style="width:11px;height:11px;"></i>
                                        Restock
                                    </button>
                                    <button type="button" class="btn btn-ghost btn-xs"
                                        onclick="openDispenseModal('{{ $item->id }}', '{{ addslashes($item->medicine_name) }}')">
                                        <i data-lucide="minus" style="width:11px;height:11px;"></i>
                                        Dispense
                                    </button>
                                    <button type="button" class="btn btn-ghost btn-xs"
                                        onclick="openFlagModal('{{ $item->id }}', '{{ addslashes($item->medicine_name) }}', {{ $item->is_expired ? 1 : 0 }}, {{ $item->is_recalled ? 1 : 0 }}, {{ $item->is_quarantined ? 1 : 0 }})">
                                        <i data-lucide="flag" style="width:11px;height:11px;"></i>
                                        Flags
                                    </button>
                                    <form method="POST" action="{{ route('portals.staff.inventory.pharmacy.delete', $item->id) }}" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-xs"
                                            onclick="return confirm('Remove {{ addslashes($item->medicine_name) }} from inventory?')">
                                            <i data-lucide="trash-2" style="width:11px;height:11px;"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- Add Item Modal --}}
<div id="add-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;overflow-y:auto;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:480px;margin:1rem;">
        <h3 style="margin:0 0 1.25rem;font-size:1.1rem;">Add Inventory Item</h3>
        <form method="POST" action="{{ route('portals.staff.inventory.pharmacy.store') }}">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
                <div class="form-group">
                    <label class="form-label">Medicine Name *</label>
                    <input type="text" name="medicine_name" class="form-control" required maxlength="200">
                </div>
                <div class="form-group">
                    <label class="form-label">Generic Name *</label>
                    <input type="text" name="generic_name" class="form-control" required maxlength="200">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
                <div class="form-group">
                    <label class="form-label">Form *</label>
                    <input type="text" name="form" class="form-control" required maxlength="80" placeholder="e.g. Tablet, Syrup, Injection">
                </div>
                <div class="form-group">
                    <label class="form-label">Strength *</label>
                    <input type="text" name="strength" class="form-control" required maxlength="80" placeholder="e.g. 500mg, 250mg/5ml">
                </div>
            </div>
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Available Quantity *</label>
                <input type="number" name="available_quantity" class="form-control" required min="0" value="0">
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1rem;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeAddModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="plus-circle" style="width:13px;height:13px;"></i>
                    Add Item
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Restock Modal --}}
<div id="restock-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:360px;margin:1rem;">
        <h3 style="margin:0 0 .25rem;font-size:1.1rem;">Restock</h3>
        <p id="restock-name" style="font-size:.85rem;color:var(--p-text-secondary);margin:0 0 1.25rem;"></p>
        <form id="restock-form" method="POST" action="">
            @csrf
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Quantity to Add *</label>
                <input type="number" name="quantity" class="form-control" required min="1" value="1">
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1rem;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeRestockModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="plus" style="width:13px;height:13px;"></i>
                    Restock
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Dispense Modal --}}
<div id="dispense-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:360px;margin:1rem;">
        <h3 style="margin:0 0 .25rem;font-size:1.1rem;">Dispense</h3>
        <p id="dispense-name" style="font-size:.85rem;color:var(--p-text-secondary);margin:0 0 1.25rem;"></p>
        <form id="dispense-form" method="POST" action="">
            @csrf
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Quantity to Dispense *</label>
                <input type="number" name="quantity" class="form-control" required min="1" value="1">
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1rem;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeDispenseModal()">Cancel</button>
                <button type="submit" class="btn btn-warning btn-sm">
                    <i data-lucide="minus" style="width:13px;height:13px;"></i>
                    Dispense
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Flag Modal --}}
<div id="flag-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:360px;margin:1rem;">
        <h3 style="margin:0 0 .25rem;font-size:1.1rem;">Update Flags</h3>
        <p id="flag-name" style="font-size:.85rem;color:var(--p-text-secondary);margin:0 0 1.25rem;"></p>
        <form id="flag-form" method="POST" action="">
            @csrf
            <div style="display:flex;flex-direction:column;gap:.75rem;margin-bottom:1rem;">
                <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;">
                    <input type="checkbox" id="flag-expired" name="is_expired" value="1"> Expired
                </label>
                <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;">
                    <input type="checkbox" id="flag-recalled" name="is_recalled" value="1"> Recalled
                </label>
                <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;">
                    <input type="checkbox" id="flag-quarantined" name="is_quarantined" value="1"> Quarantined
                </label>
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeFlagModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Save Flags</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    var baseUrl = '{{ url('/portals/staff/inventory/pharmacy') }}';

    function openAddModal()  { document.getElementById('add-modal').style.display = 'flex'; }
    function closeAddModal() { document.getElementById('add-modal').style.display = 'none'; }
    document.getElementById('add-modal').addEventListener('click', function(e) { if(e.target===this) closeAddModal(); });

    function openRestockModal(id, name) {
        document.getElementById('restock-name').textContent = name;
        document.getElementById('restock-form').action = baseUrl + '/' + id + '/restock';
        document.getElementById('restock-modal').style.display = 'flex';
    }
    function closeRestockModal() { document.getElementById('restock-modal').style.display = 'none'; }
    document.getElementById('restock-modal').addEventListener('click', function(e) { if(e.target===this) closeRestockModal(); });

    function openDispenseModal(id, name) {
        document.getElementById('dispense-name').textContent = name;
        document.getElementById('dispense-form').action = baseUrl + '/' + id + '/dispense';
        document.getElementById('dispense-modal').style.display = 'flex';
    }
    function closeDispenseModal() { document.getElementById('dispense-modal').style.display = 'none'; }
    document.getElementById('dispense-modal').addEventListener('click', function(e) { if(e.target===this) closeDispenseModal(); });

    function openFlagModal(id, name, expired, recalled, quarantined) {
        document.getElementById('flag-name').textContent = name;
        document.getElementById('flag-form').action = baseUrl + '/' + id + '/flag';
        document.getElementById('flag-expired').checked = !!expired;
        document.getElementById('flag-recalled').checked = !!recalled;
        document.getElementById('flag-quarantined').checked = !!quarantined;
        document.getElementById('flag-modal').style.display = 'flex';
    }
    function closeFlagModal() { document.getElementById('flag-modal').style.display = 'none'; }
    document.getElementById('flag-modal').addEventListener('click', function(e) { if(e.target===this) closeFlagModal(); });
</script>
@endsection
