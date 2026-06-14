@extends('layouts.portal')
@section('title', 'Admissions')
@section('breadcrumb_home', 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Admissions')

@section('content')
<div class="page-head">
    <h2>Admissions</h2>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.staff.wards') }}" class="btn btn-ghost btn-sm">
        <i data-lucide="layout-grid"></i> Bed Map
    </a>
    <button type="button" class="btn btn-primary btn-sm" onclick="openAdmitModal()">
        <i data-lucide="plus"></i> Admit Patient
    </button>
</div>
<p class="page-subtitle mb-6">Manage patient admissions, discharges, and bed transfers.</p>

@if(session('success'))
    <div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>
@endif
@if(session('error'))
    <div class="alert alert-danger mb-6"><i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div></div>
@endif

{{-- Status filter --}}
<div class="tabs mb-6">
    @foreach([''=>'All', 'active'=>'Active', 'discharged'=>'Discharged', 'transferred'=>'Transferred'] as $val => $label)
        <a href="{{ route('portals.staff.wards.admissions', $val ? ['status'=>$val] : []) }}"
           class="tab {{ request('status', '') === $val ? 'active' : '' }}">{{ $label }}</a>
    @endforeach
</div>

<div class="panel">
    <div class="panel-body panel-body--flush">
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
                        <td data-label="Patient">
                            <span class="td-strong">{{ $adm->patient?->health_id ?? substr($adm->patient_id,0,10).'…' }}</span>
                            @if($adm->patient)
                                <div class="td-muted">
                                    {{ $adm->patient->first_name }} {{ $adm->patient->last_name }}
                                </div>
                            @endif
                        </td>
                        <td data-label="Bed">
                            <span class="mono">{{ $adm->bed?->bed_number ?? '—' }}</span>
                        </td>
                        <td data-label="Ward">{{ $adm->bed?->ward?->name ?? '—' }}</td>
                        <td data-label="Status"><span class="badge {{ $stBadge }}">{{ ucfirst($adm->status) }}</span></td>
                        <td data-label="Admitted" class="td-muted">
                            {{ \Carbon\Carbon::parse($adm->admitted_at)->format('M d, Y H:i') }}
                        </td>
                        <td data-label="LOS" class="td-muted">{{ $adm->lengthOfStay() }}d</td>
                        <td data-label="Reason" class="td-muted">{{ Str::limit($adm->admission_reason ?? '—', 35) }}</td>
                        <td data-label="Actions">
                            @if($adm->status === 'active')
                                <div class="row-actions-inline">
                                <button type="button" class="btn btn-ghost btn-xs"
                                    onclick="openDischargeModal('{{ $adm->id }}')">
                                    <i data-lucide="log-out"></i> Discharge
                                </button>
                                <button type="button" class="btn btn-ghost btn-xs"
                                    onclick="openTransferModal('{{ $adm->id }}')">
                                    <i data-lucide="arrow-right-left"></i> Transfer
                                </button>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="panel-footer">
            {{ $admissions->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Admit Modal --}}
<div id="admit-modal" class="modal-backdrop mt-6" hidden>
    <div class="modal modal--md" role="dialog" aria-modal="true">
        <h3 class="modal__title"><i data-lucide="bed"></i> Admit Patient</h3>
        <form method="POST" action="{{ route('portals.staff.wards.admit') }}">
            @csrf
            <div class="modal__body">
                <div class="form-group">
                    <label class="form-label form-label-required">Patient ID / Health ID</label>
                    <input type="text" name="patient_id" class="form-control" required placeholder="Patient UUID or Health ID">
                </div>
                <div class="form-group">
                    <label class="form-label form-label-required">Select Bed</label>
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
                <div class="form-group">
                    <label class="form-label">Admission Reason</label>
                    <textarea name="admission_reason" class="form-control" rows="2" maxlength="500" placeholder="Reason for admission…"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Visit ID <span class="td-muted">(optional)</span></label>
                    <input type="text" name="visit_id" class="form-control" placeholder="Link to open visit UUID">
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeAdmitModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Admit Patient</button>
            </div>
        </form>
    </div>
</div>

{{-- Discharge Modal --}}
<div id="discharge-modal" class="modal-backdrop mt-6" hidden>
    <div class="modal" role="dialog" aria-modal="true">
        <h3 class="modal__title"><i data-lucide="log-out"></i> Discharge Patient</h3>
        <form id="discharge-form" method="POST" action="">
            @csrf
            <div class="modal__body">
                <div class="form-group">
                    <label class="form-label form-label-required">Discharge Destination</label>
                    <select name="discharge_destination" class="form-control" required>
                        <option value="home">Home</option>
                        <option value="referral">Referred to Another Facility</option>
                        <option value="transferred">Transferred (Internal)</option>
                        <option value="ama">Against Medical Advice</option>
                        <option value="deceased">Deceased</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Discharge Notes</label>
                    <textarea name="discharge_reason" class="form-control" rows="2" maxlength="500"></textarea>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeDischargeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Confirm Discharge</button>
            </div>
        </form>
    </div>
</div>

{{-- Transfer Modal --}}
<div id="transfer-modal" class="modal-backdrop mt-6" hidden>
    <div class="modal" role="dialog" aria-modal="true">
        <h3 class="modal__title"><i data-lucide="arrow-right-left"></i> Transfer to Another Bed</h3>
        <form id="transfer-form" method="POST" action="">
            @csrf
            <div class="modal__body">
                <div class="form-group">
                    <label class="form-label form-label-required">Target Bed</label>
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
                <div class="form-group">
                    <label class="form-label">Transfer Reason</label>
                    <input type="text" name="reason" class="form-control" maxlength="300" placeholder="e.g. Upgraded to ICU">
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeTransferModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Confirm Transfer</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openAdmitModal()    { document.getElementById('admit-modal').removeAttribute('hidden'); }
function closeAdmitModal()   { document.getElementById('admit-modal').setAttribute('hidden',''); }
function openDischargeModal(id) {
    document.getElementById('discharge-form').action = '/portals/staff/wards/admissions/' + id + '/discharge';
    document.getElementById('discharge-modal').removeAttribute('hidden');
}
function closeDischargeModal() { document.getElementById('discharge-modal').setAttribute('hidden',''); }
function openTransferModal(id) {
    document.getElementById('transfer-form').action = '/portals/staff/wards/admissions/' + id + '/transfer';
    document.getElementById('transfer-modal').removeAttribute('hidden');
}
function closeTransferModal() { document.getElementById('transfer-modal').setAttribute('hidden',''); }

['admit-modal','discharge-modal','transfer-modal'].forEach(id => {
    document.getElementById(id).addEventListener('click', function(e) { if(e.target===this) this.setAttribute('hidden',''); });
});
</script>
@endsection
