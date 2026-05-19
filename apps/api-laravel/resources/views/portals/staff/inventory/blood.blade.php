@extends('layouts.portal')

@section('title', 'Blood Bank Inventory')

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
    <a href="{{ route('portals.staff.cdss') }}" class="sidebar-link {{ request()->routeIs('portals.staff.cdss*') ? 'active' : '' }}">
        <i data-lucide="brain-circuit"></i>
        <span>Clinical Alerts</span>
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
    <a href="{{ route('portals.staff.inventory.pharmacy') }}" class="sidebar-link">
        <i data-lucide="pill"></i>
        <span>{{ __('public.portal.nav_inventory_pharmacy', [], app()->getLocale()) ?: 'Pharmacy' }}</span>
    </a>
    <a href="{{ route('portals.staff.inventory.blood') }}" class="sidebar-link active">
        <i data-lucide="droplets"></i>
        <span>{{ __('public.portal.nav_inventory_blood', [], app()->getLocale()) ?: 'Blood Bank' }}</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Supply Chain</div>
    <a href="{{ route('portals.staff.supply') }}" class="sidebar-link {{ request()->routeIs('portals.staff.supply*') ? 'active' : '' }}">
        <i data-lucide="package"></i>
        <span>Supply Chain</span>
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
@section('breadcrumb_section', 'Blood Bank')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Blood Bank Inventory</h1>
        <p class="page-subtitle">Track blood group availability and component status.</p>
    </div>
    <button type="button" class="btn btn-primary btn-sm" onclick="openUpsertModal()">
        <i data-lucide="plus-circle" style="width:14px;height:14px;"></i>
        Update Stock
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
        <div style="font-size:1.75rem;font-weight:700;color:var(--p-primary);">{{ $summary['total_units'] }}</div>
        <div style="font-size:.75rem;color:var(--p-text-secondary);">Total Units</div>
    </div>
    <div class="panel" style="text-align:center;padding:1rem .5rem;">
        <div style="font-size:1.75rem;font-weight:700;color:var(--p-success);">{{ $summary['groups_covered'] }}</div>
        <div style="font-size:.75rem;color:var(--p-text-secondary);">Groups in Stock</div>
    </div>
    <div class="panel" style="text-align:center;padding:1rem .5rem;">
        <div style="font-size:1.75rem;font-weight:700;color:var(--p-danger);">{{ $summary['expired'] }}</div>
        <div style="font-size:.75rem;color:var(--p-text-secondary);">Expired</div>
    </div>
    <div class="panel" style="text-align:center;padding:1rem .5rem;">
        <div style="font-size:1.75rem;font-weight:700;color:var(--p-danger);">{{ $summary['unsafe'] }}</div>
        <div style="font-size:.75rem;color:var(--p-text-secondary);">Unsafe</div>
    </div>
    <div class="panel" style="text-align:center;padding:1rem .5rem;">
        <div style="font-size:1.75rem;font-weight:700;color:var(--p-warning);">{{ $summary['quarantined'] }}</div>
        <div style="font-size:.75rem;color:var(--p-text-secondary);">Quarantined</div>
    </div>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('portals.staff.inventory.blood') }}" class="filter-bar" style="flex-wrap:wrap;gap:.5rem;">
    <select name="blood_group" class="form-control">
        <option value="">All Groups</option>
        @foreach(['O+','O-','A+','A-','B+','B-','AB+','AB-'] as $g)
            <option value="{{ $g }}" {{ request('blood_group') === $g ? 'selected' : '' }}>{{ $g }}</option>
        @endforeach
    </select>
    <select name="component" class="form-control">
        <option value="">All Components</option>
        @foreach(['whole_blood','packed_red_cells','fresh_frozen_plasma','platelets'] as $c)
            <option value="{{ $c }}" {{ request('component') === $c ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ',$c)) }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-primary btn-sm">
        <i data-lucide="filter" style="width:13px;height:13px;"></i> Filter
    </button>
    <a href="{{ route('portals.staff.inventory.blood') }}" class="btn btn-ghost btn-sm">Clear</a>
</form>

<div class="panel">
    <div class="panel-body" style="padding:0;">
        @if($items->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="droplets"></i></div>
                <h3>No Blood Inventory</h3>
                <p>Add blood stock records by blood group and component.</p>
                <button type="button" class="btn btn-primary btn-sm" style="margin-top:1rem;" onclick="openUpsertModal()">Update Stock</button>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Blood Group</th>
                            <th>Component</th>
                            <th>Units Available</th>
                            <th>Flags</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                        @php
                            $qtyColor = $item->available_units <= 0 ? 'var(--p-danger)' : ($item->available_units <= 5 ? 'var(--p-warning)' : 'var(--p-success)');
                        @endphp
                        <tr>
                            <td data-label="Blood Group">
                                <strong style="font-size:1.05rem;">{{ $item->blood_group }}</strong>
                            </td>
                            <td data-label="Component">
                                <span class="badge badge-neutral">{{ ucwords(str_replace('_',' ',$item->component)) }}</span>
                            </td>
                            <td data-label="Units Available">
                                <strong style="color:{{ $qtyColor }};">{{ $item->available_units }}</strong> units
                            </td>
                            <td data-label="Flags">
                                @if($item->is_expired)    <span class="badge badge-danger" style="font-size:.65rem;margin:.1rem;">Expired</span> @endif
                                @if($item->is_unsafe)     <span class="badge badge-danger" style="font-size:.65rem;margin:.1rem;">Unsafe</span> @endif
                                @if($item->is_quarantined)<span class="badge badge-warning" style="font-size:.65rem;margin:.1rem;">Quarantine</span> @endif
                                @if(!$item->is_expired && !$item->is_unsafe && !$item->is_quarantined)
                                    <span style="color:var(--p-text-secondary);font-size:var(--p-text-xs);">—</span>
                                @endif
                            </td>
                            <td data-label="Last Updated" style="font-size:var(--p-text-xs);">
                                {{ \Carbon\Carbon::parse($item->last_stock_update)->format('M d, H:i') }}
                            </td>
                            <td data-label="Actions">
                                <div style="display:flex;gap:.35rem;flex-wrap:wrap;">
                                    <button type="button" class="btn btn-primary btn-xs"
                                        onclick="openAdjustModal('{{ $item->id }}', '{{ $item->blood_group }}', '{{ addslashes($item->component) }}', 'add')">
                                        <i data-lucide="plus" style="width:11px;height:11px;"></i>
                                        Add
                                    </button>
                                    <button type="button" class="btn btn-ghost btn-xs"
                                        onclick="openAdjustModal('{{ $item->id }}', '{{ $item->blood_group }}', '{{ addslashes($item->component) }}', 'subtract')">
                                        <i data-lucide="minus" style="width:11px;height:11px;"></i>
                                        Use
                                    </button>
                                    <button type="button" class="btn btn-ghost btn-xs"
                                        onclick="openBloodFlagModal('{{ $item->id }}', '{{ $item->blood_group }} {{ addslashes($item->component) }}', {{ $item->is_expired ? 1 : 0 }}, {{ $item->is_unsafe ? 1 : 0 }}, {{ $item->is_quarantined ? 1 : 0 }})">
                                        <i data-lucide="flag" style="width:11px;height:11px;"></i>
                                        Flags
                                    </button>
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

{{-- Upsert (Add/Update) Modal --}}
<div id="upsert-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:420px;margin:1rem;">
        <h3 style="margin:0 0 1.25rem;font-size:1.1rem;">Set Blood Stock</h3>
        <p style="font-size:.8rem;color:var(--p-text-secondary);margin:-1rem 0 1rem;">Existing entry for this group+component will be updated.</p>
        <form method="POST" action="{{ route('portals.staff.inventory.blood.upsert') }}">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
                <div class="form-group">
                    <label class="form-label">Blood Group *</label>
                    <select name="blood_group" class="form-control" required>
                        @foreach(['O+','O-','A+','A-','B+','B-','AB+','AB-'] as $g)
                            <option value="{{ $g }}">{{ $g }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Component *</label>
                    <select name="component" class="form-control" required>
                        @foreach(['whole_blood','packed_red_cells','fresh_frozen_plasma','platelets'] as $c)
                            <option value="{{ $c }}">{{ ucwords(str_replace('_',' ',$c)) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Available Units *</label>
                <input type="number" name="available_units" class="form-control" required min="0" value="0">
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1rem;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeUpsertModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="save" style="width:13px;height:13px;"></i>
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Adjust Modal --}}
<div id="adjust-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:360px;margin:1rem;">
        <h3 id="adjust-title" style="margin:0 0 .25rem;font-size:1.1rem;">Adjust Units</h3>
        <p id="adjust-label" style="font-size:.85rem;color:var(--p-text-secondary);margin:0 0 1.25rem;"></p>
        <form id="adjust-form" method="POST" action="">
            @csrf
            <input type="hidden" id="adjust-direction" name="direction" value="add">
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Units *</label>
                <input type="number" name="units" class="form-control" required min="1" value="1">
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1rem;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeAdjustModal()">Cancel</button>
                <button type="submit" id="adjust-btn" class="btn btn-primary btn-sm">Confirm</button>
            </div>
        </form>
    </div>
</div>

{{-- Blood Flag Modal --}}
<div id="blood-flag-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:360px;margin:1rem;">
        <h3 style="margin:0 0 .25rem;font-size:1.1rem;">Update Flags</h3>
        <p id="blood-flag-label" style="font-size:.85rem;color:var(--p-text-secondary);margin:0 0 1.25rem;"></p>
        <form id="blood-flag-form" method="POST" action="">
            @csrf
            <div style="display:flex;flex-direction:column;gap:.75rem;margin-bottom:1rem;">
                <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;">
                    <input type="checkbox" id="bf-expired" name="is_expired" value="1"> Expired
                </label>
                <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;">
                    <input type="checkbox" id="bf-unsafe" name="is_unsafe" value="1"> Unsafe
                </label>
                <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;">
                    <input type="checkbox" id="bf-quarantined" name="is_quarantined" value="1"> Quarantined
                </label>
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeBloodFlagModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Save Flags</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    var bloodBase = '{{ url('/portals/staff/inventory/blood') }}';

    function openUpsertModal()  { document.getElementById('upsert-modal').style.display = 'flex'; }
    function closeUpsertModal() { document.getElementById('upsert-modal').style.display = 'none'; }
    document.getElementById('upsert-modal').addEventListener('click', function(e) { if(e.target===this) closeUpsertModal(); });

    function openAdjustModal(id, group, component, direction) {
        document.getElementById('adjust-label').textContent = group + ' · ' + component.replace(/_/g,' ');
        document.getElementById('adjust-title').textContent = direction === 'add' ? 'Add Units' : 'Use Units';
        document.getElementById('adjust-direction').value = direction;
        document.getElementById('adjust-form').action = bloodBase + '/' + id + '/adjust';
        var btn = document.getElementById('adjust-btn');
        btn.className = direction === 'add' ? 'btn btn-primary btn-sm' : 'btn btn-warning btn-sm';
        btn.textContent = direction === 'add' ? 'Add' : 'Use';
        document.getElementById('adjust-modal').style.display = 'flex';
    }
    function closeAdjustModal() { document.getElementById('adjust-modal').style.display = 'none'; }
    document.getElementById('adjust-modal').addEventListener('click', function(e) { if(e.target===this) closeAdjustModal(); });

    function openBloodFlagModal(id, label, expired, unsafe, quarantined) {
        document.getElementById('blood-flag-label').textContent = label;
        document.getElementById('blood-flag-form').action = bloodBase + '/' + id + '/flag';
        document.getElementById('bf-expired').checked = !!expired;
        document.getElementById('bf-unsafe').checked = !!unsafe;
        document.getElementById('bf-quarantined').checked = !!quarantined;
        document.getElementById('blood-flag-modal').style.display = 'flex';
    }
    function closeBloodFlagModal() { document.getElementById('blood-flag-modal').style.display = 'none'; }
    document.getElementById('blood-flag-modal').addEventListener('click', function(e) { if(e.target===this) closeBloodFlagModal(); });
</script>
@endsection
