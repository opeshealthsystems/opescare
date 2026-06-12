@extends('layouts.portal')
@section('title', $facility->name)
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Facilities')
@section('content')

<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
    <div>
        <a href="{{ route('admin.facilities.index') }}" style="font-size:.82rem;color:var(--p-text-muted);display:inline-flex;align-items:center;gap:.3rem;margin-bottom:.4rem;">
            <i data-lucide="arrow-left" style="width:13px;height:13px;"></i> Facilities
        </a>
        <h1 class="page-title">{{ $facility->name }}</h1>
        <p class="page-subtitle">{{ ucfirst($facility->type ?? '') }} &mdash; {{ $facility->region ?? '' }}</p>
    </div>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
        @if(($facility->status ?? '') === 'pending')
        <form method="POST" action="{{ route('admin.facilities.approve', $facility->id) }}">@csrf
            <button class="btn btn-success btn-sm"><i data-lucide="check-circle"></i> Approve</button>
        </form>
        @endif
        @if(($facility->status ?? '') !== 'suspended')
        <form method="POST" action="{{ route('admin.facilities.suspend', $facility->id) }}">@csrf
            <button class="btn btn-warning btn-sm" onclick="return confirm('Suspend this facility?')"><i data-lucide="pause-circle"></i> Suspend</button>
        </form>
        @endif
        @if(($facility->status ?? '') === 'suspended')
        <form method="POST" action="{{ route('admin.facilities.approve', $facility->id) }}">@csrf
            <button class="btn btn-success btn-sm"><i data-lucide="play-circle"></i> Activate</button>
        </form>
        @endif
        <button onclick="document.getElementById('delete-modal').style.display='flex'" class="btn btn-danger btn-sm">
            <i data-lucide="trash-2"></i> Delete
        </button>
    </div>
</div>

@if(session('success'))<div class="auth-alert auth-alert-success" style="margin-bottom:1rem;"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

<div class="kpi-grid" style="margin-bottom:1.5rem;">
    <div class="kpi-card">
        <div class="kpi-icon blue"><i data-lucide="users"></i></div>
        <div class="kpi-body">
            <div class="kpi-label">Patients</div>
            <div class="kpi-value">{{ number_format($facility->patients_count ?? 0) }}</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon teal"><i data-lucide="stethoscope"></i></div>
        <div class="kpi-body">
            <div class="kpi-label">Staff</div>
            <div class="kpi-value" style="color:var(--p-teal);">{{ number_format($facility->staff_count ?? 0) }}</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon purple"><i data-lucide="calendar"></i></div>
        <div class="kpi-body">
            <div class="kpi-label">Appointments</div>
            <div class="kpi-value" style="color:#7C3AED;">{{ number_format($facility->appointments_count ?? 0) }}</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:rgba(16,185,129,.1);"><i data-lucide="receipt" style="color:#10b981;"></i></div>
        <div class="kpi-body">
            <div class="kpi-label">Status</div>
            <div class="kpi-value" style="font-size:1rem;margin-top:.25rem;">
                @if(($facility->status??'')=='active')<span class="badge badge-success">Active</span>
                @elseif(($facility->status??'')=='suspended')<span class="badge badge-danger">Suspended</span>
                @else<span class="badge badge-warning">{{ ucfirst($facility->status??'pending') }}</span>@endif
            </div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 2fr;gap:1.5rem;align-items:start;">
    <div class="panel">
        <div class="panel-header"><h2 class="panel-title"><i data-lucide="building-2"></i> Facility Info</h2></div>
        <div class="card-body" style="padding:1.25rem;">
            <dl style="display:grid;grid-template-columns:auto 1fr;gap:.5rem .75rem;font-size:.875rem;">
                <dt style="color:var(--p-text-muted);">License</dt>
                <dd style="margin:0;font-family:monospace;font-size:.8rem;">{{ $facility->license_number ?? '—' }}</dd>
                <dt style="color:var(--p-text-muted);">Type</dt>
                <dd style="margin:0;">{{ ucfirst($facility->type ?? '—') }}</dd>
                <dt style="color:var(--p-text-muted);">Region</dt>
                <dd style="margin:0;">{{ $facility->region ?? '—' }}</dd>
                <dt style="color:var(--p-text-muted);">Country</dt>
                <dd style="margin:0;">{{ strtoupper($facility->country_code ?? '—') }}</dd>
                <dt style="color:var(--p-text-muted);">Created</dt>
                <dd style="margin:0;">{{ $facility->created_at?->format('d M Y') }}</dd>
            </dl>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header"><h2 class="panel-title"><i data-lucide="edit"></i> Edit Facility</h2></div>
        <div class="card-body" style="padding:1.25rem;">
            <form method="POST" action="{{ route('admin.facilities.update', $facility->id) }}">
                @csrf @method('PUT')
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Name <span style="color:var(--p-danger);">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $facility->name) }}" required>
                        @error('name')<div style="font-size:.78rem;color:var(--p-danger);">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Type <span style="color:var(--p-danger);">*</span></label>
                        <select name="type" class="form-control" required>
                            @foreach(['hospital','clinic','pharmacy','laboratory','radiology','specialist','other'] as $t)
                            <option value="{{ $t }}" @selected(old('type',$facility->type)===$t)>{{ ucfirst($t) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Region <span style="color:var(--p-danger);">*</span></label>
                        <input type="text" name="region" class="form-control" value="{{ old('region', $facility->region) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Country Code</label>
                        <input type="text" name="country_code" class="form-control" value="{{ old('country_code', $facility->country_code) }}" maxlength="3">
                    </div>
                </div>
                <div style="margin-top:1rem;">
                    <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="delete-modal" style="display:none;position:fixed;inset:0;z-index:60;align-items:center;justify-content:center;background:rgba(15,23,42,.7);backdrop-filter:blur(4px);padding:1rem;" role="dialog">
    <div style="background:var(--p-surface);border:1px solid var(--p-border);border-radius:var(--p-radius-xl);width:100%;max-width:420px;overflow:hidden;box-shadow:var(--p-shadow-lg);">
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--p-border);display:flex;align-items:center;justify-content:space-between;">
            <h3 style="font-size:1rem;font-weight:700;margin:0;color:var(--p-danger);">Delete Facility</h3>
            <button onclick="document.getElementById('delete-modal').style.display='none'" class="topbar-icon-btn"><i data-lucide="x"></i></button>
        </div>
        <div style="padding:1.5rem;">
            <p>Permanently delete <strong>{{ $facility->name }}</strong>? This cannot be undone.</p>
            <div style="display:flex;gap:.75rem;justify-content:flex-end;">
                <button onclick="document.getElementById('delete-modal').style.display='none'" class="btn btn-ghost">Cancel</button>
                <form method="POST" action="{{ route('admin.facilities.destroy', $facility->id) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
@section('scripts')
<script>
document.addEventListener('keydown',e=>{if(e.key==='Escape'){document.querySelectorAll('[id$="-modal"]').forEach(m=>m.style.display='none');}});
</script>
@endsection
