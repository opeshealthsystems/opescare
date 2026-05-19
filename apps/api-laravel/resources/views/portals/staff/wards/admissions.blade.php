@extends('layouts.portal')
@section('title', 'Admissions')
@section('breadcrumb_home', 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Admissions')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Admissions</h1>
        <p class="page-subtitle">Manage patient admissions, discharges, and bed transfers.</p>
    </div>
    <div style="display:flex;gap:.5rem;">
        <a href="{{ route('portals.staff.wards') }}" class="btn btn-ghost btn-sm">
            <i data-lucide="layout-grid" style="width:13px;height:13px;"></i> Bed Map
        </a>
        <button type="button" class="btn btn-primary btn-sm" onclick="openAdmitModal()">
            <i data-lucide="plus" style="width:13px;height:13px;"></i> Admit Patient
        </button>
    </div>
</div>

@if(session('success'))
    <div class="auth-alert auth-alert-success" style="margin-bottom:1rem;"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>
@endif
@if(session('error'))
    <div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;"><i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div></div>
@endif

{{-- Status filter --}}
<form method="GET" action="{{ route('portals.staff.wards.admissions') }}" style="margin-bottom:1rem;display:flex;gap:.5rem;">
    @foreach([''=>'All', 'active'=>'Active', 'discharged'=>'Discharged', 'transferred'=>'Transferred'] as $val => $label)
        <a href="{{ route('portals.staff.wards.admissions', $val ? ['status'=>$val] : []) }}"
           class="btn {{ request('status', '') === $val ? 'btn-primary' : 'btn-ghost' }} btn-sm">{{ $label }}</a>
    @endforeach
</form>

<div class="panel">
    <div class="panel-body" style="padding:0;">
        @if($admissions->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="bed"></i></div>
                <h3>No admissions found</h3>
                <p>Admit a patient to a bed to start tracking inpatient stays.</p>
            </div>
        @else
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr>
                    <th>Patient</th><th>Bed</th><th>Ward</th><th>Status</th><th>Admitted</th><th>LOS</th><th>Reason</th><th>Actions</th>
                </tr></thead>
                <tbody>
                    @foreach($admissions as $adm)
                    @php
                        $stBadge = match($adm->status) {
                            'active'      => 'badge-success',
                            'discharged'  => 'badge-neutral',
                            'transferred' => 'badge-primary',
                            default       => 'badge-neutral',
                        };
                    @endphp
                    <tr>
                        <td style="font-weight:500;font-size:.85rem;">
                            {{ $adm->patient?->health_id ?? substr($adm->patient_id,0,10).'…' }}
                            @if($adm->patient)
                                <div style="font-size:.75rem;color:var(--p-text-muted);">
                                    {{ $adm->patient->first_name }} {{ $adm->patient->last_name }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <code style="font-size:.78rem;">{{ $adm->bed?->bed_number ?? '—' }}</code>
                        </td>
                        <td style="font-size:.8rem;">{{ $adm->bed?->ward?->name ?? '—' }}</td>
                        <td><span class="badge {{ $stBadge }}" style="font-size:.72rem;">{{ ucfirst($adm->status) }}</span></td>
                        <td style="font-size:.78rem;color:var(--p-text-muted);">
                            {{ \Carbon\Carbon::parse($adm->admitted_at)->format('M d, Y H:i') }}
                        </td>
                        <td style="font-size:.78rem;color:var(--p-text-muted);">{{ $adm->lengthOfStay() }}d</td>
                        <td style="font-size:.78rem;color:var(--p-text-muted);">{{ Str::limit($adm->admission_reason ?? '—', 35) }}</td>
                        <td>
                            @if($adm->status === 'active')
                                <button type="button" class="btn btn-ghost btn-xs" style="margin-right:3px;"
                                    onclick="openDischargeModal('{{ $adm->id }}')">
                                    <i data-lucide="log-out" style="width:11px;height:11px;"></i> Discharge
                                </button>
                                <button type="button" class="btn btn-ghost btn-xs"
                                    onclick="openTransferModal('{{ $adm->id }}')">
                                    <i data-lucide="arrow-right-left" style="width:11px;height:11px;"></i> Transfer
                                </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding:.75rem 1.25rem;border-top:1px solid var(--p-border);">
            {{ $admissions->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Admit Modal --}}
<div id="admit-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:480px;margin:1rem;">
        <h3 style="margin:0 0 1rem;font-size:1.05rem;">Admit Patient</h3>
        <form method="POST" action="{{ route('portals.staff.wards.admit') }}">
            @csrf
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Patient ID / Health ID *</label>
                <input type="text" name="patient_id" class="form-control" required placeholder="Patient UUID or Health ID">
            </div>
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Select Bed *</label>
                <select name="bed_id" class="form-control" required>
                    <option value="">— Select available bed —</option>
                    @php
                        $availBeds = \App\Models\Bed::with('ward')
                            ->where('status','available')
                            ->whereHas('ward', fn($q) => $q->where('is_active',true))
                            ->orderBy('ward_id')
                            ->get();
                        $byWard = $availBeds->groupBy(fn($b) => $b->ward?->name ?? 'Unknown');
                    @endphp
                    @foreach($byWard as $wardName => $beds)
                        <optgroup label="{{ $wardName }}">
                            @foreach($beds as $bed)
                                <option value="{{ $bed->id }}">{{ $bed->bed_number }} ({{ $bed->bed_type }})</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Admission Reason</label>
                <textarea name="admission_reason" class="form-control" rows="2" maxlength="500" placeholder="Reason for admission…"></textarea>
            </div>
            <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label">Visit ID <span style="color:var(--p-text-muted);font-weight:400;">(optional)</span></label>
                <input type="text" name="visit_id" class="form-control" placeholder="Link to open visit UUID">
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeAdmitModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Admit Patient</button>
            </div>
        </form>
    </div>
</div>

{{-- Discharge Modal --}}
<div id="discharge-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:420px;margin:1rem;">
        <h3 style="margin:0 0 1rem;font-size:1.05rem;">Discharge Patient</h3>
        <form id="discharge-form" method="POST" action="">
            @csrf
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Discharge Destination *</label>
                <select name="discharge_destination" class="form-control" required>
                    <option value="home">Home</option>
                    <option value="referral">Referred to Another Facility</option>
                    <option value="transferred">Transferred (Internal)</option>
                    <option value="ama">Against Medical Advice</option>
                    <option value="deceased">Deceased</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label">Discharge Notes</label>
                <textarea name="discharge_reason" class="form-control" rows="2" maxlength="500"></textarea>
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeDischargeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Confirm Discharge</button>
            </div>
        </form>
    </div>
</div>

{{-- Transfer Modal --}}
<div id="transfer-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:420px;margin:1rem;">
        <h3 style="margin:0 0 1rem;font-size:1.05rem;">Transfer to Another Bed</h3>
        <form id="transfer-form" method="POST" action="">
            @csrf
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Target Bed *</label>
                <select name="to_bed_id" class="form-control" required>
                    <option value="">— Select available bed —</option>
                    @foreach($byWard ?? [] as $wardName => $beds)
                        <optgroup label="{{ $wardName }}">
                            @foreach($beds as $bed)
                                <option value="{{ $bed->id }}">{{ $bed->bed_number }} ({{ $bed->bed_type }})</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label">Transfer Reason</label>
                <input type="text" name="reason" class="form-control" maxlength="300" placeholder="e.g. Upgraded to ICU">
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeTransferModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Confirm Transfer</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openAdmitModal()    { document.getElementById('admit-modal').style.display = 'flex'; }
function closeAdmitModal()   { document.getElementById('admit-modal').style.display = 'none'; }
function openDischargeModal(id) {
    document.getElementById('discharge-form').action = '/portals/staff/wards/admissions/' + id + '/discharge';
    document.getElementById('discharge-modal').style.display = 'flex';
}
function closeDischargeModal() { document.getElementById('discharge-modal').style.display = 'none'; }
function openTransferModal(id) {
    document.getElementById('transfer-form').action = '/portals/staff/wards/admissions/' + id + '/transfer';
    document.getElementById('transfer-modal').style.display = 'flex';
}
function closeTransferModal() { document.getElementById('transfer-modal').style.display = 'none'; }

['admit-modal','discharge-modal','transfer-modal'].forEach(id => {
    document.getElementById(id).addEventListener('click', function(e) { if(e.target===this) this.style.display='none'; });
});
</script>
@endsection
