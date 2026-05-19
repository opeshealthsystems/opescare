@extends('layouts.portal')

@section('title', __('public.staff_portal.btn_create_invoice', [], app()->getLocale()) ?: 'Create Invoice')

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
    <a href="{{ route('portals.staff.hr.shifts') }}" class="sidebar-link">
        <i data-lucide="clock"></i>
        <span>{{ __('public.portal.nav_staff_shifts', [], app()->getLocale()) ?: 'Shifts' }}</span>
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
    <a href="{{ route('portals.staff.inventory.blood') }}" class="sidebar-link">
        <i data-lucide="droplets"></i>
        <span>{{ __('public.portal.nav_inventory_blood', [], app()->getLocale()) ?: 'Blood Bank' }}</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Operations</div>
    <a href="{{ route('portals.staff.billing') }}" class="sidebar-link active">
        <i data-lucide="receipt"></i>
        <span>{{ __('public.portal.nav_billing', [], app()->getLocale()) ?: 'Billing' }}</span>
    </a>
    <a href="{{ route('portals.staff.support') }}" class="sidebar-link">
        <i data-lucide="headset"></i>
        <span>{{ __('public.portal.nav_support', [], app()->getLocale()) ?: 'Support' }}</span>
    </a>
    <a href="{{ route('portals.insurance.policies') }}" class="sidebar-link">
        <i data-lucide="shield-check"></i>
        <span>{{ __('public.portal.nav_insurance', [], app()->getLocale()) ?: 'Insurance' }}</span>
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
@section('breadcrumb_section', __('public.staff_portal.btn_create_invoice', [], app()->getLocale()) ?: 'Create Invoice')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">{{ __('public.staff_portal.btn_create_invoice', [], app()->getLocale()) ?: 'Create Invoice' }}</h1>
        <p class="page-subtitle">{{ __('public.staff_portal.billing_subtitle', [], app()->getLocale()) ?: 'Create a new patient invoice.' }}</p>
    </div>
    <a href="{{ route('portals.staff.billing') }}" class="btn btn-ghost btn-sm">
        <i data-lucide="arrow-left" style="width:14px;height:14px;"></i>
        Back
    </a>
</div>

@if(session('error'))
    <div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;">
        <i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div>
    </div>
@endif

<div class="panel" style="max-width:760px;">
    <div class="panel-body">
        <form method="POST" action="{{ route('portals.staff.billing.store') }}" id="invoice-form">
            @csrf

            <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label">Patient *</label>
                @if(count($patients) > 0)
                    <select name="patient_id" class="form-control" required>
                        <option value="">— Select Patient —</option>
                        @foreach($patients as $p)
                            <option value="{{ $p->id }}" {{ old('patient_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->health_id ?? $p->id }} ({{ $p->first_name ?? '' }} {{ $p->last_name ?? '' }})
                            </option>
                        @endforeach
                    </select>
                @else
                    <input type="text" name="patient_id" class="form-control" required placeholder="Patient ID">
                @endif
                @error('patient_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            {{-- Line items --}}
            <h3 style="font-size:.9rem;font-weight:700;margin:1.5rem 0 .75rem;color:var(--p-text-secondary);">
                Line Items
            </h3>
            <div id="line-items">
                <div class="line-item" style="display:grid;grid-template-columns:1fr auto auto auto;gap:.5rem;margin-bottom:.5rem;align-items:end;">
                    <div>
                        <label class="form-label" style="font-size:.75rem;">{{ __('public.staff_portal.lbl_description', [], app()->getLocale()) ?: 'Description' }} *</label>
                        <input type="text" name="items[0][description]" class="form-control" required placeholder="Service description…">
                    </div>
                    <div style="width:70px;">
                        <label class="form-label" style="font-size:.75rem;">{{ __('public.staff_portal.lbl_qty', [], app()->getLocale()) ?: 'Qty' }}</label>
                        <input type="number" name="items[0][quantity]" class="form-control" value="1" min="1" step="1" required>
                    </div>
                    <div style="width:120px;">
                        <label class="form-label" style="font-size:.75rem;">{{ __('public.staff_portal.lbl_unit_price', [], app()->getLocale()) ?: 'Unit Price' }} *</label>
                        <input type="number" name="items[0][unit_price]" class="form-control" value="0.00" min="0" step="0.01" required>
                    </div>
                    <div style="padding-bottom:2px;">
                        <button type="button" class="btn btn-ghost btn-xs" disabled style="visibility:hidden;">
                            <i data-lucide="trash-2" style="width:12px;height:12px;"></i>
                        </button>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-ghost btn-sm" onclick="addLineItem()" style="margin-bottom:1.5rem;">
                <i data-lucide="plus" style="width:13px;height:13px;"></i>
                {{ __('public.staff_portal.btn_add_item', [], app()->getLocale()) ?: 'Add Line Item' }}
            </button>

            <div style="display:flex;gap:.75rem;margin-top:.5rem;">
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="file-plus" style="width:14px;height:14px;"></i>
                    {{ __('public.staff_portal.btn_create_invoice', [], app()->getLocale()) ?: 'Create Invoice' }}
                </button>
                <a href="{{ route('portals.staff.billing') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    var lineCount = 1;
    function addLineItem() {
        var container = document.getElementById('line-items');
        var idx = lineCount++;
        var row = document.createElement('div');
        row.className = 'line-item';
        row.style.cssText = 'display:grid;grid-template-columns:1fr auto auto auto;gap:.5rem;margin-bottom:.5rem;align-items:end;';
        row.innerHTML =
            '<div><input type="text" name="items[' + idx + '][description]" class="form-control" required placeholder="Service description…"></div>' +
            '<div style="width:70px;"><input type="number" name="items[' + idx + '][quantity]" class="form-control" value="1" min="1" step="1" required></div>' +
            '<div style="width:120px;"><input type="number" name="items[' + idx + '][unit_price]" class="form-control" value="0.00" min="0" step="0.01" required></div>' +
            '<div style="padding-bottom:2px;"><button type="button" class="btn btn-ghost btn-xs" onclick="this.closest(\'.line-item\').remove()">' +
            '<i data-lucide="trash-2" style="width:12px;height:12px;"></i></button></div>';
        container.appendChild(row);
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
</script>
@endsection
