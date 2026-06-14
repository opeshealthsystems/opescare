@extends('layouts.portal')
@section('title', 'Subscription Plans — Admin')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Plans')
@section('content')

<div class="breadcrumb">
    <a href="{{ route('portals.admin.subscription') }}">Subscriptions</a>
    <i data-lucide="chevron-right"></i>
    <span>Plans</span>
</div>

<div class="page-head">
    <h2>Subscription plans</h2>
    <div class="page-head__spacer"></div>
    <button type="button" class="btn btn-primary" onclick="opOpenModal('createPlanModal')"><i data-lucide="plus"></i> New plan</button>
</div>

<p class="td-muted mb-6">OpesCare SaaS plans available to facilities.</p>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

{{-- Stats --}}
<div class="stat-grid mb-6">
    <div class="stat-card"><div class="stat-card__label">Total plans</div><div class="stat-card__value">{{ $stats['total'] }}</div></div>
    <div class="stat-card"><div class="stat-card__label">Active</div><div class="stat-card__value">{{ $stats['active'] }}</div></div>
    <div class="stat-card"><div class="stat-card__label">Public</div><div class="stat-card__value">{{ $stats['public'] }}</div></div>
</div>

{{-- Plan tier cards --}}
@if($plans->count())
<div class="plan-grid">
    @foreach($plans as $plan)
    <div class="plan-tier">
        <span class="plan-tier__name">{{ $plan->name }}</span>
        <span class="plan-tier__price">{{ $plan->priceFormatted() }}<small>/{{ $plan->billing_cycle }}</small></span>
        <div class="summary-bar">
            <span class="badge badge-{{ $plan->is_active ? 'success' : 'neutral' }}">{{ $plan->is_active ? 'Active' : 'Inactive' }}</span>
            @if($plan->is_public)<span class="badge badge-primary">Public</span>@endif
            @if($plan->trial_days > 0)<span class="badge badge-teal">{{ $plan->trial_days }}d trial</span>@endif
        </div>
        <ul class="plan-tier__features">
            <li><i data-lucide="check"></i> {{ $plan->max_facilities }} facilit{{ $plan->max_facilities > 1 ? 'ies' : 'y' }}</li>
            <li><i data-lucide="check"></i> {{ $plan->max_staff ?? '∞' }} staff</li>
            <li><i data-lucide="check"></i> {{ $plan->max_patients_per_month ? number_format($plan->max_patients_per_month) . ' pts/mo' : '∞ patients' }}</li>
        </ul>
    </div>
    @endforeach
</div>
@endif

<div class="panel">
    <div class="panel-header"><h3 class="panel-title"><i data-lucide="layers"></i> All plans</h3></div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Plan name</th>
                    <th>Cycle</th>
                    <th>Price</th>
                    <th>Features</th>
                    <th>Limits</th>
                    <th>Trial</th>
                    <th>Status</th>
                    <th class="row-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($plans as $plan)
                    <tr>
                        <td data-label="Plan name">
                            <div class="td-strong">{{ $plan->name }}</div>
                            <span class="mono td-muted">{{ $plan->slug }}</span>
                            @if($plan->description)<div class="td-muted">{{ Str::limit($plan->description, 50) }}</div>@endif
                        </td>
                        <td data-label="Cycle"><span class="badge badge-primary">{{ ucfirst($plan->billing_cycle) }}</span></td>
                        <td data-label="Price"><strong>{{ $plan->priceFormatted() }}</strong></td>
                        <td data-label="Features">
                            @if($plan->planFeatures->count())
                                @foreach($plan->planFeatures->take(3) as $f)
                                    <span class="feature-chip">{{ $f->feature_key }}</span>
                                @endforeach
                                @if($plan->planFeatures->count() > 3)
                                    <span class="td-muted">+{{ $plan->planFeatures->count() - 3 }} more</span>
                                @endif
                            @else
                                <span class="td-muted">—</span>
                            @endif
                        </td>
                        <td data-label="Limits">
                            <div>{{ $plan->max_facilities }} facilit{{ $plan->max_facilities > 1 ? 'ies' : 'y' }}</div>
                            <div>{{ $plan->max_staff ?? '∞' }} staff</div>
                            <div>{{ $plan->max_patients_per_month ? number_format($plan->max_patients_per_month) . ' pts/mo' : '∞ pts' }}</div>
                        </td>
                        <td data-label="Trial">{{ $plan->trial_days > 0 ? $plan->trial_days . ' days' : '—' }}</td>
                        <td data-label="Status">
                            <span class="badge badge-{{ $plan->is_active ? 'success' : 'neutral' }}">{{ $plan->is_active ? 'Active' : 'Inactive' }}</span>
                            @if($plan->is_public)<span class="badge badge-primary">Public</span>@endif
                        </td>
                        <td class="row-actions" data-label="Actions">
                            <button type="button" class="btn {{ $plan->is_active ? 'btn-warning' : 'btn-success' }} btn-sm" onclick="opOpenModal('toggle-modal-{{ $plan->id }}')">{{ $plan->is_active ? 'Deactivate' : 'Activate' }}</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="td-muted empty-cell">No subscription plans yet. Create your first plan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($plans->hasPages())<div class="panel-body">{{ $plans->links() }}</div>@endif
</div>

{{-- Toggle confirm modals --}}
@foreach($plans as $plan)
<div id="toggle-modal-{{ $plan->id }}" class="modal-backdrop mt-6" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="toggle-modal-title-{{ $plan->id }}">
        <h3 class="modal__title" id="toggle-modal-title-{{ $plan->id }}"><i data-lucide="alert-triangle"></i> {{ $plan->is_active ? 'Deactivate' : 'Activate' }} plan</h3>
        <form method="POST" action="{{ route('portals.admin.subscription.plans.toggle', $plan->id) }}">@csrf
            <div class="modal__body"><p>{{ $plan->is_active ? 'Deactivate' : 'Activate' }} the plan <strong>{{ $plan->name }}</strong>?</p></div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" onclick="opCloseModal('toggle-modal-{{ $plan->id }}')">Cancel</button>
                <button type="submit" class="btn {{ $plan->is_active ? 'btn-warning' : 'btn-success' }}">{{ $plan->is_active ? 'Deactivate' : 'Activate' }}</button>
            </div>
        </form>
    </div>
</div>
@endforeach

{{-- Create Plan Modal --}}
<div id="createPlanModal" class="modal-backdrop mt-6" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="createPlanModal-title">
        <h3 class="modal__title" id="createPlanModal-title"><i data-lucide="layers"></i> New subscription plan</h3>
        <form method="POST" action="{{ route('portals.admin.subscription.plans.store') }}">
            @csrf
            <div class="modal__body">
                <div class="field-grid">
                    <div class="form-group">
                        <label class="form-label form-label-required">Plan name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Starter">
                    </div>
                    <div class="form-group">
                        <label class="form-label form-label-required">Billing cycle</label>
                        <select name="billing_cycle" class="form-control" required>
                            <option value="monthly">Monthly</option>
                            <option value="annual">Annual</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label form-label-required">Price (FCFA)</label>
                        <input type="number" name="price" class="form-control" required min="0" step="1" placeholder="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Trial days</label>
                        <input type="number" name="trial_days" class="form-control" min="0" value="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Max facilities</label>
                        <input type="number" name="max_facilities" class="form-control" min="1" value="1">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Max staff (blank = unlimited)</label>
                        <input type="number" name="max_staff" class="form-control" min="1" placeholder="Unlimited">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Max patients/month (blank = unlimited)</label>
                        <input type="number" name="max_patients_per_month" class="form-control" min="1" placeholder="Unlimited">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sort order</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Brief description of this plan…"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Module features (feature keys, one per line)</label>
                    <textarea id="featureKeysRaw" class="form-control" rows="4" placeholder="MODULE_CDSS&#10;MODULE_BRIDGE&#10;API_SDK&#10;WEBHOOKS&#10;ANALYTICS_ADVANCED"></textarea>
                    <div class="form-hint">Enter one feature key per line. These control module entitlements.</div>
                </div>
                <div class="form-group">
                    <label class="form-label"><input type="checkbox" name="is_public" value="1" id="isPub" checked> Publicly visible on pricing page</label>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" onclick="opCloseModal('createPlanModal')">Cancel</button>
                <button type="submit" class="btn btn-primary" onclick="buildFeatureInputs()">Create plan</button>
            </div>
        </form>
    </div>
</div>

@endsection
@section('scripts')
<script>
function opOpenModal(id){ document.getElementById(id).removeAttribute('hidden'); }
function opCloseModal(id){ document.getElementById(id).setAttribute('hidden',''); }
function buildFeatureInputs(){
    const raw = document.getElementById('featureKeysRaw').value.trim();
    if (!raw) return;
    const form = document.querySelector('#createPlanModal form');
    raw.split('\n').forEach((line, i) => {
        const key = line.trim();
        if (!key) return;
        const kInput = document.createElement('input');
        kInput.type = 'hidden'; kInput.name = `feature_keys[${i}]`; kInput.value = key;
        const lInput = document.createElement('input');
        lInput.type = 'hidden'; lInput.name = `feature_labels[${i}]`; lInput.value = key.replace(/_/g,' ');
        form.appendChild(kInput);
        form.appendChild(lInput);
    });
}
document.addEventListener('keydown', function(e){
    if(e.key==='Escape'){ document.querySelectorAll('.modal-backdrop').forEach(function(m){ m.setAttribute('hidden',''); }); }
});
</script>
@endsection
