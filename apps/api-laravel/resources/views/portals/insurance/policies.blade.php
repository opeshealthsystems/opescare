@extends('layouts.portal')

@section('title', 'Patient Insurance Policies')

@section('sidebar_role_badge')
<div class="sidebar-role-badge sidebar-role-badge--primary">Insurance</div>
@endsection
@section('sidebar_user_role', 'Insurance Admin')

@section('sidebar_nav')
@include('portals.insurance._sidebar_nav')
@endsection

@section('breadcrumb_home', 'Insurance Portal')
@section('breadcrumb_home_url', route('portals.insurance.dashboard'))
@section('breadcrumb_section', 'Patient Policies')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Patient Insurance Policies</h1>
        <p class="page-subtitle">Register and manage patient insurance coverage.</p>
    </div>
    <button type="button" class="btn btn-primary btn-sm" onclick="openPolicyModal()">
        <i data-lucide="plus-circle"></i>
        Register Policy
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success mb-4">
        <i data-lucide="check-circle"></i><div>{{ session('success') }}</div>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger mb-4">
        <i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div>
    </div>
@endif

{{-- Filters --}}
<form method="GET" action="{{ route('portals.insurance.policies') }}" class="filter-bar">
    <select name="status" class="filter-select">
        <option value="">All Statuses</option>
        @foreach(['pending','active','inactive','expired','cancelled'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucwords($s) }}</option>
        @endforeach
    </select>
    <label class="filter-search">
        <i data-lucide="search"></i>
        <input type="text" name="patient_id" placeholder="Patient ID…" value="{{ request('patient_id') }}">
    </label>
    <button type="submit" class="btn btn-primary btn-sm">
        <i data-lucide="filter"></i> Filter
    </button>
    <a href="{{ route('portals.insurance.policies') }}" class="btn btn-ghost btn-sm">Clear</a>
</form>

<div class="panel">
    <div class="panel-body--flush">
        @if(count($policies) === 0)
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="shield-check"></i></div>
                <h3>No Patient Policies</h3>
                <p>Register a patient insurance policy to begin tracking coverage.</p>
                <button type="button" class="btn btn-primary btn-sm" onclick="openPolicyModal()">
                    Register Policy
                </button>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Provider / Plan</th>
                            <th>Policy #</th>
                            <th>Member ID</th>
                            <th>Expiry</th>
                            <th>Eligibility</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($policies as $policy)
                        @php
                            $statusBadge = match($policy->status) {
                                'active'   => 'badge-success',
                                'pending'  => 'badge-warning',
                                'expired'  => 'badge-danger',
                                'cancelled'=> 'badge-neutral',
                                default    => 'badge-neutral',
                            };
                            $eligibility = $policy->latestEligibility;
                            $eligBadge = match($eligibility->status ?? '') {
                                'eligible'     => 'badge-success',
                                'not_eligible' => 'badge-danger',
                                'expired'      => 'badge-warning',
                                default        => 'badge-neutral',
                            };
                        @endphp
                        <tr>
                            <td data-label="Patient">
                                <span class="td-mono">{{ $policy->patient_id }}</span>
                            </td>
                            <td data-label="Provider / Plan">
                                <span class="td-strong">{{ $policy->plan->provider->name ?? '--' }}</span>
                                <div class="td-muted">{{ $policy->plan->name ?? '--' }}</div>
                            </td>
                            <td data-label="Policy #">
                                <span class="td-mono">{{ $policy->policy_number }}</span>
                            </td>
                            <td data-label="Member ID">{{ $policy->member_id ?? '--' }}</td>
                            <td data-label="Expiry">
                                {{ $policy->expiry_date ? $policy->expiry_date->format('M d, Y') : '--' }}
                            </td>
                            <td data-label="Eligibility">
                                @if($eligibility)
                                    <span class="badge {{ $eligBadge }}">{{ ucwords(str_replace('_',' ',$eligibility->status)) }}</span>
                                @else
                                    <span class="badge badge-neutral">Not Checked</span>
                                @endif
                            </td>
                            <td data-label="Status">
                                <span class="badge {{ $statusBadge }}">{{ ucwords($policy->status) }}</span>
                            </td>
                            <td data-label="Actions" class="row-actions">
                                {{-- Eligibility check --}}
                                <button type="button" class="btn btn-ghost btn-sm" onclick="openEligModal('{{ $policy->id }}')">
                                    <i data-lucide="activity"></i> Check
                                </button>
                                @if($policy->status === 'pending')
                                    <form method="POST" action="{{ route('portals.insurance.policies.activate', $policy->id) }}" class="inline-form">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i data-lucide="check-circle"></i> Activate
                                        </button>
                                    </form>
                                @elseif($policy->status === 'active')
                                    <button type="button" class="btn btn-ghost btn-sm" onclick="openDeactivateModal('{{ $policy->id }}')">
                                        <i data-lucide="pause-circle"></i> Deactivate
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- Register Policy Modal --}}
<div id="policy-modal" class="modal-fixed">
    <div class="modal-fixed__panel modal-fixed__panel--lg">
        <div class="modal-fixed__head"><h3 class="modal-fixed__title">Register Patient Insurance Policy</h3></div>
        <form method="POST" action="{{ route('portals.insurance.policies.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label form-label-required">Patient</label>
                @if(count($patients) > 0)
                    <select name="patient_id" class="form-control" required>
                        <option value="">— Select Patient —</option>
                        @foreach($patients as $p)
                            <option value="{{ $p->id }}">
                                {{ $p->health_id ?? $p->id }} ({{ $p->first_name ?? '' }} {{ $p->last_name ?? '' }})
                            </option>
                        @endforeach
                    </select>
                @else
                    <input type="text" name="patient_id" class="form-control" required placeholder="Patient ID">
                @endif
            </div>
            <div class="form-group">
                <label class="form-label form-label-required">Insurance Plan</label>
                @if(count($plans) > 0)
                    <select name="insurance_plan_id" class="form-control" required>
                        <option value="">— Select Plan —</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}">
                                {{ $plan->provider->name ?? '' }} — {{ $plan->name }}
                            </option>
                        @endforeach
                    </select>
                @else
                    <p class="form-hint">No active plans found. Add a provider and plan first.</p>
                    <input type="text" name="insurance_plan_id" class="form-control" required placeholder="Plan ID">
                @endif
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label form-label-required">Policy Number</label>
                    <input type="text" name="policy_number" class="form-control" required maxlength="100">
                </div>
                <div class="form-group">
                    <label class="form-label">Member ID</label>
                    <input type="text" name="member_id" class="form-control" maxlength="100">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Effective Date</label>
                    <input type="date" name="effective_date" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Expiry Date</label>
                    <input type="date" name="expiry_date" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Relationship to Primary</label>
                <select name="relationship_to_primary" class="form-control">
                    <option value="self">Self</option>
                    <option value="spouse">Spouse</option>
                    <option value="child">Child</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="form-actions-end">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closePolicyModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="shield-check"></i> Register Policy
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Eligibility Check Modal --}}
<div id="elig-modal" class="modal-fixed">
    <div class="modal-fixed__panel modal-fixed__panel--sm">
        <div class="modal-fixed__head"><h3 class="modal-fixed__title">Eligibility Check</h3></div>
        <form id="elig-form" method="POST" action="">
            @csrf
            <div class="form-group">
                <label class="form-label form-label-required">Eligibility Result</label>
                <select name="status" class="form-control" required>
                    <option value="eligible">Eligible</option>
                    <option value="not_eligible">Not Eligible</option>
                    <option value="unknown">Unknown</option>
                    <option value="expired">Expired</option>
                    <option value="failed">Failed to Verify</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3" maxlength="500"></textarea>
            </div>
            <div class="form-actions-end">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeEligModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="activity"></i> Save Check
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Deactivate Policy Modal --}}
<div id="deactivate-modal" class="modal-fixed">
    <div class="modal-fixed__panel modal-fixed__panel--sm">
        <div class="modal-fixed__head"><h3 class="modal-fixed__title"><i data-lucide="alert-triangle"></i> Deactivate policy</h3></div>
        <form id="deactivate-form" method="POST" action="">
            @csrf
            <p>Deactivate this policy?</p>
            <div class="form-actions-end">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeDeactivateModal()">Cancel</button>
                <button type="submit" class="btn btn-danger btn-sm">Deactivate</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openPolicyModal() { document.getElementById('policy-modal').classList.add('open'); }
    function closePolicyModal() { document.getElementById('policy-modal').classList.remove('open'); }
    document.getElementById('policy-modal').addEventListener('click', function(e) {
        if (e.target === this) closePolicyModal();
    });

    function openEligModal(policyId) {
        var form = document.getElementById('elig-form');
        form.setAttribute('action', '{{ url("/portals/insurance/policies") }}/' + policyId + '/eligibility');
        document.getElementById('elig-modal').classList.add('open');
    }
    function closeEligModal() { document.getElementById('elig-modal').classList.remove('open'); }
    document.getElementById('elig-modal').addEventListener('click', function(e) {
        if (e.target === this) closeEligModal();
    });

    function openDeactivateModal(policyId) {
        document.getElementById('deactivate-form').setAttribute('action',
            '{{ url("/portals/insurance/policies") }}/' + policyId + '/deactivate');
        document.getElementById('deactivate-modal').classList.add('open');
    }
    function closeDeactivateModal() { document.getElementById('deactivate-modal').classList.remove('open'); }
    document.getElementById('deactivate-modal').addEventListener('click', function(e) {
        if (e.target === this) closeDeactivateModal();
    });
</script>
@endsection
