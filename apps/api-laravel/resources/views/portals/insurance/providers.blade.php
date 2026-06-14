@extends('layouts.portal')

@section('title', 'Insurance Providers & Plans')

@section('sidebar_role_badge')
<div class="sidebar-role-badge sidebar-role-badge--primary">Insurance</div>
@endsection
@section('sidebar_user_role', 'Insurance Admin')

@section('sidebar_nav')
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Insurance</div>
    <a href="{{ route('portals.insurance.dashboard') }}" class="sidebar-link">
        <i data-lucide="layout-dashboard"></i>
        <span>Dashboard</span>
    </a>
    <a href="{{ route('portals.insurance.providers') }}" class="sidebar-link active">
        <i data-lucide="building-2"></i>
        <span>Providers & Plans</span>
    </a>
    <a href="{{ route('portals.insurance.policies') }}" class="sidebar-link">
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
@section('breadcrumb_home_url', route('portals.insurance.dashboard'))
@section('breadcrumb_section', 'Providers & Plans')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Insurance Providers & Plans</h1>
        <p class="page-subtitle">Manage insurance companies and their coverage plans.</p>
    </div>
    <button type="button" class="btn btn-primary btn-sm" onclick="openProviderModal()">
        <i data-lucide="plus-circle"></i>
        Add Provider
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

@if(count($providers) === 0)
    <div class="panel">
        <div class="panel-body">
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="building-2"></i></div>
                <h3>No Insurance Providers</h3>
                <p>Add insurance companies to start managing patient policies and claims.</p>
                <button type="button" class="btn btn-primary btn-sm" onclick="openProviderModal()">
                    Add Provider
                </button>
            </div>
        </div>
    </div>
@else
    @foreach($providers as $provider)
    <div class="panel mb-6">
        <div class="panel-header">
            <div>
                <h2 class="panel-title">
                    <i data-lucide="building-2"></i>
                    {{ $provider->name }}
                    @if($provider->code)
                        <span class="badge badge-neutral">{{ $provider->code }}</span>
                    @endif
                    <span class="badge {{ $provider->status === 'active' ? 'badge-success' : 'badge-neutral' }}">
                        {{ ucwords($provider->status) }}
                    </span>
                </h2>
                @if($provider->contact_email || $provider->contact_phone)
                <p class="text-sm text-muted">
                    {{ $provider->contact_email }} {{ $provider->contact_phone ? '· ' . $provider->contact_phone : '' }}
                </p>
                @endif
            </div>
            <button type="button" class="btn btn-ghost btn-sm" onclick="openPlanModal('{{ $provider->id }}')">
                <i data-lucide="plus"></i> Add Plan
            </button>
        </div>
        <div class="panel-body--flush">
            @if($provider->activePlans->isEmpty())
                <p class="text-sm text-muted" style="padding:1rem;">No plans yet.</p>
            @else
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Plan Name</th>
                                <th>Code</th>
                                <th>Type</th>
                                <th>Pre-auth Required</th>
                                <th>Cashless</th>
                                <th>Copay %</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($provider->activePlans as $plan)
                            <tr>
                                <td data-label="Plan Name"><span class="td-strong">{{ $plan->name }}</span></td>
                                <td data-label="Code"><span class="td-mono">{{ $plan->plan_code ?? '--' }}</span></td>
                                <td data-label="Type">{{ ucwords($plan->plan_type ?? '--') }}</td>
                                <td data-label="Pre-auth Required">
                                    <span class="badge {{ $plan->requires_preauthorization ? 'badge-warning' : 'badge-neutral' }}">
                                        {{ $plan->requires_preauthorization ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td data-label="Cashless">
                                    <span class="badge {{ $plan->cashless_available ? 'badge-success' : 'badge-neutral' }}">
                                        {{ $plan->cashless_available ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td data-label="Copay %">{{ $plan->copay_percentage ? $plan->copay_percentage . '%' : '--' }}</td>
                                <td data-label="Status"><span class="badge badge-success">{{ ucwords($plan->status) }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
    @endforeach
@endif

{{-- Add Provider Modal --}}
<div id="provider-modal" class="modal-fixed">
    <div class="modal-fixed__panel modal-fixed__panel--md">
        <div class="modal-fixed__head"><h3 class="modal-fixed__title">Add Insurance Provider</h3></div>
        <form method="POST" action="{{ route('portals.insurance.providers.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label form-label-required">Provider Name</label>
                <input type="text" name="name" class="form-control" required maxlength="200" placeholder="e.g. NHIA, Activa, Sunu">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Code</label>
                    <input type="text" name="code" class="form-control" maxlength="50" placeholder="NHIA">
                </div>
                <div class="form-group">
                    <label class="form-label">Country Code</label>
                    <input type="text" name="country_code" class="form-control" maxlength="3" value="CM">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Contact Email</label>
                <input type="email" name="contact_email" class="form-control" maxlength="200">
            </div>
            <div class="form-group">
                <label class="form-label">Contact Phone</label>
                <input type="text" name="contact_phone" class="form-control" maxlength="30">
            </div>
            <div class="form-actions-end">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeProviderModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="save"></i> Save Provider
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Add Plan Modal --}}
<div id="plan-modal" class="modal-fixed">
    <div class="modal-fixed__panel modal-fixed__panel--md">
        <div class="modal-fixed__head"><h3 class="modal-fixed__title">Add Insurance Plan</h3></div>
        <form id="plan-form" method="POST" action="">
            @csrf
            <div class="form-group">
                <label class="form-label form-label-required">Plan Name</label>
                <input type="text" name="name" class="form-control" required maxlength="200">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Plan Code</label>
                    <input type="text" name="plan_code" class="form-control" maxlength="50">
                </div>
                <div class="form-group">
                    <label class="form-label">Plan Type</label>
                    <select name="plan_type" class="form-control">
                        <option value="">Select…</option>
                        <option value="nhia">NHIA</option>
                        <option value="private">Private</option>
                        <option value="employer">Employer</option>
                        <option value="mutual">Mutual</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Pre-auth Required</label>
                    <select name="requires_preauthorization" class="form-control">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Cashless Available</label>
                    <select name="cashless_available" class="form-control">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Co-pay %</label>
                <input type="number" name="copay_percentage" class="form-control" min="0" max="100" step="0.01" placeholder="0.00">
            </div>
            <div class="form-actions-end">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closePlanModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="save"></i> Save Plan
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openProviderModal() { document.getElementById('provider-modal').classList.add('open'); }
    function closeProviderModal() { document.getElementById('provider-modal').classList.remove('open'); }
    document.getElementById('provider-modal').addEventListener('click', function(e) {
        if (e.target === this) closeProviderModal();
    });

    function openPlanModal(providerId) {
        var form = document.getElementById('plan-form');
        form.setAttribute('action', '{{ url("/portals/insurance/providers") }}/' + providerId + '/plans');
        document.getElementById('plan-modal').classList.add('open');
    }
    function closePlanModal() { document.getElementById('plan-modal').classList.remove('open'); }
    document.getElementById('plan-modal').addEventListener('click', function(e) {
        if (e.target === this) closePlanModal();
    });
</script>
@endsection
