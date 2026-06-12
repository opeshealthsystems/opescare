@extends('layouts.portal')
@section('title', 'Patient — ' . $patient->health_id)
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Patients')
@section('content')

<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
    <div>
        <a href="{{ route('admin.patients.index') }}" style="font-size:.82rem;color:var(--p-text-muted);display:inline-flex;align-items:center;gap:.3rem;margin-bottom:.4rem;">
            <i data-lucide="arrow-left" style="width:13px;height:13px;"></i> Patients
        </a>
        <h1 class="page-title">{{ $patient->first_name ?? '' }} {{ $patient->last_name ?? '' }}</h1>
        <p class="page-subtitle" style="font-family:monospace;">{{ $patient->health_id }}</p>
    </div>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
        @if(($patient->identity_status??'')==='active')
        <button onclick="document.getElementById('suspend-modal').style.display='flex'" class="btn btn-warning btn-sm">
            <i data-lucide="pause-circle"></i> Suspend
        </button>
        @else
        <button onclick="document.getElementById('activate-modal').style.display='flex'" class="btn btn-success btn-sm">
            <i data-lucide="check-circle"></i> Activate
        </button>
        @endif
        @if($patient->entered_in_error ?? false)
        <button onclick="document.getElementById('delete-modal').style.display='flex'" class="btn btn-danger btn-sm">
            <i data-lucide="trash-2"></i> Delete
        </button>
        @endif
    </div>
</div>

@if(session('success'))<div class="auth-alert auth-alert-success" style="margin-bottom:1rem;"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;align-items:start;">
    <div>
        <div class="panel" style="margin-bottom:1.25rem;">
            <div class="panel-header"><h2 class="panel-title"><i data-lucide="user"></i> Patient Identity</h2></div>
            <div style="padding:1.25rem;">
                <dl style="display:grid;grid-template-columns:auto 1fr;gap:.5rem .75rem;font-size:.875rem;">
                    <dt style="color:var(--p-text-muted);">Health ID</dt>
                    <dd style="margin:0;font-family:monospace;font-weight:700;color:var(--p-primary);">{{ $patient->health_id }}</dd>
                    <dt style="color:var(--p-text-muted);">Full Name</dt>
                    <dd style="margin:0;font-weight:600;">{{ $patient->first_name ?? '' }} {{ $patient->last_name ?? '' }}</dd>
                    <dt style="color:var(--p-text-muted);">Date of Birth</dt>
                    <dd style="margin:0;">{{ $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->format('d M Y') : '—' }}</dd>
                    <dt style="color:var(--p-text-muted);">Sex</dt>
                    <dd style="margin:0;">{{ ucfirst($patient->sex ?? '—') }}</dd>
                    <dt style="color:var(--p-text-muted);">Status</dt>
                    <dd style="margin:0;">
                        @if(($patient->identity_status??'')==='active')<span class="badge badge-success">Active</span>
                        @elseif(($patient->identity_status??'')==='suspended')<span class="badge badge-danger">Suspended</span>
                        @else<span class="badge badge-warning">{{ ucfirst($patient->identity_status??'provisional') }}</span>@endif
                    </dd>
                    <dt style="color:var(--p-text-muted);">Registered</dt>
                    <dd style="margin:0;">{{ $patient->created_at?->format('d M Y, H:i') }}</dd>
                </dl>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header"><h2 class="panel-title"><i data-lucide="phone"></i> Contact</h2></div>
            <div style="padding:1.25rem;">
                <dl style="display:grid;grid-template-columns:auto 1fr;gap:.5rem .75rem;font-size:.875rem;">
                    <dt style="color:var(--p-text-muted);">Phone</dt>
                    <dd style="margin:0;">{{ $patient->phone ?? $patient->phone_number ?? '—' }}</dd>
                    <dt style="color:var(--p-text-muted);">Email</dt>
                    <dd style="margin:0;">{{ $patient->email ?? '—' }}</dd>
                    <dt style="color:var(--p-text-muted);">Address</dt>
                    <dd style="margin:0;">{{ $patient->address ?? '—' }}</dd>
                    <dt style="color:var(--p-text-muted);">Facility</dt>
                    <dd style="margin:0;">{{ $patient->facility?->name ?? '—' }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header"><h2 class="panel-title"><i data-lucide="edit"></i> Quick Edit</h2></div>
        <div style="padding:1.25rem;">
            <form method="POST" action="{{ route('portals.admin.patients.update', $patient) }}">
                @csrf @method('PUT')
                <div class="form-group" style="margin-bottom:1rem;">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $patient->phone ?? $patient->phone_number ?? '') }}">
                    @error('phone')<div style="font-size:.78rem;color:var(--p-danger);">{{ $message }}</div>@enderror
                </div>
                <div class="form-group" style="margin-bottom:1rem;">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $patient->email ?? '') }}">
                    @error('email')<div style="font-size:.78rem;color:var(--p-danger);">{{ $message }}</div>@enderror
                </div>
                <div class="form-group" style="margin-bottom:1rem;">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2">{{ old('address', $patient->address ?? '') }}</textarea>
                </div>
                <div class="form-group" style="margin-bottom:1rem;">
                    <label class="form-label">Facility</label>
                    <select name="facility_id" class="form-control">
                        <option value="">— Select Facility —</option>
                        @foreach($facilities as $facility)
                        <option value="{{ $facility->id }}" @selected(old('facility_id',$patient->facility_id)==$facility->id)>{{ $facility->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm" style="width:100%;"><i data-lucide="save"></i> Save Changes</button>
            </form>
        </div>
    </div>
</div>

{{-- Suspend Modal --}}
<div id="suspend-modal" style="display:none;position:fixed;inset:0;z-index:60;align-items:center;justify-content:center;background:rgba(15,23,42,.7);backdrop-filter:blur(4px);padding:1rem;" role="dialog">
    <div style="background:var(--p-surface);border:1px solid var(--p-border);border-radius:var(--p-radius-xl);width:100%;max-width:440px;overflow:hidden;">
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--p-border);display:flex;justify-content:space-between;align-items:center;">
            <h3 style="margin:0;font-size:1rem;font-weight:700;">Suspend Patient</h3>
            <button onclick="document.getElementById('suspend-modal').style.display='none'" class="topbar-icon-btn"><i data-lucide="x"></i></button>
        </div>
        <div style="padding:1.5rem;">
            <p>Suspend <strong>{{ $patient->first_name ?? '' }} {{ $patient->last_name ?? '' }}</strong>? They will no longer be able to access services.</p>
            <div style="display:flex;gap:.75rem;justify-content:flex-end;">
                <button onclick="document.getElementById('suspend-modal').style.display='none'" class="btn btn-ghost">Cancel</button>
                <form method="POST" action="{{ route('portals.admin.patients.suspend', $patient) }}">@csrf @method('PATCH')
                    <button type="submit" class="btn btn-warning">Suspend</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Activate Modal --}}
<div id="activate-modal" style="display:none;position:fixed;inset:0;z-index:60;align-items:center;justify-content:center;background:rgba(15,23,42,.7);backdrop-filter:blur(4px);padding:1rem;" role="dialog">
    <div style="background:var(--p-surface);border:1px solid var(--p-border);border-radius:var(--p-radius-xl);width:100%;max-width:440px;overflow:hidden;">
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--p-border);display:flex;justify-content:space-between;align-items:center;">
            <h3 style="margin:0;font-size:1rem;font-weight:700;">Activate Patient</h3>
            <button onclick="document.getElementById('activate-modal').style.display='none'" class="topbar-icon-btn"><i data-lucide="x"></i></button>
        </div>
        <div style="padding:1.5rem;">
            <p>Activate <strong>{{ $patient->first_name ?? '' }} {{ $patient->last_name ?? '' }}</strong> and restore their access?</p>
            <div style="display:flex;gap:.75rem;justify-content:flex-end;">
                <button onclick="document.getElementById('activate-modal').style.display='none'" class="btn btn-ghost">Cancel</button>
                <form method="POST" action="{{ route('portals.admin.patients.activate', $patient) }}">@csrf @method('PATCH')
                    <button type="submit" class="btn btn-success">Activate</button>
                </form>
            </div>
        </div>
    </div>
</div>

@if($patient->entered_in_error ?? false)
<div id="delete-modal" style="display:none;position:fixed;inset:0;z-index:60;align-items:center;justify-content:center;background:rgba(15,23,42,.7);backdrop-filter:blur(4px);padding:1rem;" role="dialog">
    <div style="background:var(--p-surface);border:1px solid var(--p-border);border-radius:var(--p-radius-xl);width:100%;max-width:480px;overflow:hidden;">
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--p-border);display:flex;justify-content:space-between;align-items:center;">
            <h3 style="margin:0;font-size:1rem;font-weight:700;color:var(--p-danger);">Delete Patient Record</h3>
            <button onclick="document.getElementById('delete-modal').style.display='none'" class="topbar-icon-btn"><i data-lucide="x"></i></button>
        </div>
        <div style="padding:1.5rem;">
            <div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;"><i data-lucide="alert-triangle"></i><div><strong>Irreversible.</strong> The record for <strong>{{ $patient->first_name ?? '' }} {{ $patient->last_name ?? '' }}</strong> ({{ $patient->health_id }}) will be permanently deleted.</div></div>
            <div style="display:flex;gap:.75rem;justify-content:flex-end;">
                <button onclick="document.getElementById('delete-modal').style.display='none'" class="btn btn-ghost">Cancel</button>
                <form method="POST" action="{{ route('portals.admin.patients.destroy', $patient) }}">@csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Permanently</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@endsection
@section('scripts')
<script>document.addEventListener('keydown',e=>{if(e.key==='Escape'){document.querySelectorAll('[id$="-modal"]').forEach(m=>m.style.display='none');}});</script>
@endsection
