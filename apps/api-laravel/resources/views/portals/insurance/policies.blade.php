@extends('layouts.portal')

@section('title', 'Patient Insurance Policies')

@section('sidebar_role_badge')
<div class="sidebar-role-badge">Insurance</div>
@endsection
@section('sidebar_user_role', 'Insurance Admin')

@section('sidebar_nav')
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Insurance</div>
    <a href="{{ route('portals.insurance.dashboard') }}" class="sidebar-link">
        <i data-lucide="layout-dashboard"></i>
        <span>Dashboard</span>
    </a>
    <a href="{{ route('portals.insurance.providers') }}" class="sidebar-link">
        <i data-lucide="building-2"></i>
        <span>Providers & Plans</span>
    </a>
    <a href="{{ route('portals.insurance.policies') }}" class="sidebar-link active">
        <i data-lucide="shield-check"></i>
        <span>Patient Policies</span>
    </a>
    <a href="{{ route('portals.insurance.preauths') }}" class="sidebar-link">
        <i data-lucide="clipboard-list"></i>
        <span>Preauthorization</span>
    </a>
    <a href="{{ route('portals.insurance.claims') }}" class="sidebar-link">
        <i data-lucide="file-text"></i>
        <span>Claims</span>
    </a>
</div>
@endsection

@section('breadcrumb_home', 'Insurance Portal')
@section('breadcrumb_home_url', route('portals.insurance.providers'))
@section('breadcrumb_section', 'Patient Policies')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Patient Insurance Policies</h1>
        <p class="page-subtitle">Register and manage patient insurance coverage.</p>
    </div>
    <button type="button" class="btn btn-primary btn-sm" onclick="openPolicyModal()">
        <i data-lucide="plus-circle" style="width:14px;height:14px;"></i>
        Register Policy
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

{{-- Filters --}}
<form method="GET" action="{{ route('portals.insurance.policies') }}" class="filter-bar" style="flex-wrap:wrap;gap:.5rem;">
    <select name="status" class="form-control">
        <option value="">All Statuses</option>
        @foreach(['pending','active','inactive','expired','cancelled'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucwords($s) }}</option>
        @endforeach
    </select>
    <input type="text" name="patient_id" class="form-control" placeholder="Patient ID…" value="{{ request('patient_id') }}">
    <button type="submit" class="btn btn-primary btn-sm">
        <i data-lucide="filter" style="width:13px;height:13px;"></i> Filter
    </button>
    <a href="{{ route('portals.insurance.policies') }}" class="btn btn-ghost btn-sm">Clear</a>
</form>

<div class="panel">
    <div class="panel-body" style="padding:0;">
        @if(count($policies) === 0)
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="shield-check"></i></div>
                <h3>No Patient Policies</h3>
                <p>Register a patient insurance policy to begin tracking coverage.</p>
                <button type="button" class="btn btn-primary btn-sm" style="margin-top:1rem;" onclick="openPolicyModal()">
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
                                <span style="font-family:monospace;font-size:var(--p-text-xs);">{{ $policy->patient_id }}</span>
                            </td>
                            <td data-label="Provider / Plan">
                                <div style="font-size:var(--p-text-xs);">
                                    <strong>{{ $policy->plan->provider->name ?? '--' }}</strong><br>
                                    {{ $policy->plan->name ?? '--' }}
                                </div>
                            </td>
                            <td data-label="Policy #">
                                <span style="font-family:monospace;font-size:var(--p-text-xs);">{{ $policy->policy_number }}</span>
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
                            <td data-label="Actions">
                                <div style="display:flex;gap:.35rem;flex-wrap:wrap;">
                                    {{-- Eligibility check --}}
                                    <button type="button" class="btn btn-ghost btn-xs"
                                        onclick="openEligModal('{{ $policy->id }}')">
                                        <i data-lucide="activity" style="width:11px;height:11px;"></i>
                                        Check
                                    </button>
                                    @if($policy->status === 'pending')
                                        <form method="POST" action="{{ route('portals.insurance.policies.activate', $policy->id) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-xs">
                                                <i data-lucide="check-circle" style="width:11px;height:11px;"></i>
                                                Activate
                                            </button>
                                        </form>
                                    @elseif($policy->status === 'active')
                                        <form method="POST" action="{{ route('portals.insurance.policies.deactivate', $policy->id) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-ghost btn-xs"
                                                onclick="return confirm('Deactivate this policy?')">
                                                <i data-lucide="pause-circle" style="width:11px;height:11px;"></i>
                                                Deactivate
                                            </button>
                                        </form>
                                    @endif
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

{{-- Register Policy Modal --}}
<div id="policy-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;overflow-y:auto;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:540px;margin:1rem;">
        <h3 style="margin:0 0 1.25rem;font-size:1.1rem;">Register Patient Insurance Policy</h3>
        <form method="POST" action="{{ route('portals.insurance.policies.store') }}">
            @csrf
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Patient *</label>
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
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Insurance Plan *</label>
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
                    <p class="form-hint" style="color:var(--p-danger);">No active plans found. Add a provider and plan first.</p>
                    <input type="text" name="insurance_plan_id" class="form-control" required placeholder="Plan ID">
                @endif
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
                <div class="form-group">
                    <label class="form-label">Policy Number *</label>
                    <input type="text" name="policy_number" class="form-control" required maxlength="100">
                </div>
                <div class="form-group">
                    <label class="form-label">Member ID</label>
                    <input type="text" name="member_id" class="form-control" maxlength="100">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
                <div class="form-group">
                    <label class="form-label">Effective Date</label>
                    <input type="date" name="effective_date" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Expiry Date</label>
                    <input type="date" name="expiry_date" class="form-control">
                </div>
            </div>
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Relationship to Primary</label>
                <select name="relationship_to_primary" class="form-control">
                    <option value="self">Self</option>
                    <option value="spouse">Spouse</option>
                    <option value="child">Child</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1rem;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closePolicyModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="shield-check" style="width:13px;height:13px;"></i>
                    Register Policy
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Eligibility Check Modal --}}
<div id="elig-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:400px;margin:1rem;">
        <h3 style="margin:0 0 1.25rem;font-size:1.1rem;">Eligibility Check</h3>
        <form id="elig-form" method="POST" action="">
            @csrf
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Eligibility Result *</label>
                <select name="status" class="form-control" required>
                    <option value="eligible">Eligible</option>
                    <option value="not_eligible">Not Eligible</option>
                    <option value="unknown">Unknown</option>
                    <option value="expired">Expired</option>
                    <option value="failed">Failed to Verify</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3" maxlength="500"></textarea>
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1rem;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeEligModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="activity" style="width:13px;height:13px;"></i>
                    Save Check
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openPolicyModal() { document.getElementById('policy-modal').style.display = 'flex'; }
    function closePolicyModal() { document.getElementById('policy-modal').style.display = 'none'; }
    document.getElementById('policy-modal').addEventListener('click', function(e) {
        if (e.target === this) closePolicyModal();
    });

    function openEligModal(policyId) {
        var form = document.getElementById('elig-form');
        form.setAttribute('action', '{{ url("/portals/insurance/policies") }}/' + policyId + '/eligibility');
        document.getElementById('elig-modal').style.display = 'flex';
    }
    function closeEligModal() { document.getElementById('elig-modal').style.display = 'none'; }
    document.getElementById('elig-modal').addEventListener('click', function(e) {
        if (e.target === this) closeEligModal();
    });
</script>
@endsection
