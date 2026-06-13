@extends('layouts.portal')
@section('title', 'Subscription Plans — Admin')
@section('sidebar') @include('portals.admin.control_center._sidebar') @endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">
                <i data-lucide="layers" style="width:22px;height:22px;vertical-align:middle;margin-right:8px;color:#7c3aed;"></i>
                Subscription Plans
            </h1>
            <p class="portal-page-subtitle">OpesCare SaaS plans available to facilities</p>
        </div>
        <button class="btn btn--primary" onclick="openModal('createPlanModal')">
            <i data-lucide="plus" style="width:14px;height:14px;"></i> New Plan
        </button>
    </div>

    @if(session('success'))<div class="alert alert--success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert--danger">{{ session('error') }}</div>@endif

    {{-- Stats --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:14px;margin-bottom:20px;">
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#ede9fe;"><i data-lucide="layers" style="color:#7c3aed;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value">{{ $stats['total'] }}</div><div class="stat-card__label">Total Plans</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#f0fdf4;"><i data-lucide="check-circle" style="color:#16a34a;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value">{{ $stats['active'] }}</div><div class="stat-card__label">Active</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#eff6ff;"><i data-lucide="eye" style="color:#2563eb;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value">{{ $stats['public'] }}</div><div class="stat-card__label">Public</div></div>
        </div>
    </div>

    <div class="portal-card">
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table">
                <thead>
                    <tr>
                        <th>Plan Name</th>
                        <th>Cycle</th>
                        <th>Price</th>
                        <th>Features</th>
                        <th>Limits</th>
                        <th>Trial</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $plan)
                        <tr>
                            <td>
                                <div style="font-weight:700;font-size:0.88rem;">{{ $plan->name }}</div>
                                <code style="font-size:0.72rem;color:#9ca3af;">{{ $plan->slug }}</code>
                                @if($plan->description)
                                    <div style="font-size:0.73rem;color:#9ca3af;">{{ Str::limit($plan->description, 50) }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge--info" style="font-size:0.72rem;">{{ ucfirst($plan->billing_cycle) }}</span>
                            </td>
                            <td style="font-weight:700;font-size:0.88rem;">{{ $plan->priceFormatted() }}</td>
                            <td>
                                @if($plan->planFeatures->count())
                                    <div style="font-size:0.78rem;">
                                        @foreach($plan->planFeatures->take(3) as $f)
                                            <span style="display:inline-block;background:#ede9fe;color:#7c3aed;padding:1px 6px;border-radius:3px;font-size:0.7rem;margin:1px;">{{ $f->feature_key }}</span>
                                        @endforeach
                                        @if($plan->planFeatures->count() > 3)
                                            <span style="font-size:0.72rem;color:#9ca3af;">+{{ $plan->planFeatures->count() - 3 }} more</span>
                                        @endif
                                    </div>
                                @else
                                    <span style="color:#9ca3af;font-size:0.8rem;">—</span>
                                @endif
                            </td>
                            <td style="font-size:0.79rem;color:#6b7280;">
                                <div>{{ $plan->max_facilities }} facilit{{ $plan->max_facilities > 1 ? 'ies' : 'y' }}</div>
                                <div>{{ $plan->max_staff ?? '∞' }} staff</div>
                                <div>{{ $plan->max_patients_per_month ? number_format($plan->max_patients_per_month) . ' pts/mo' : '∞ pts' }}</div>
                            </td>
                            <td style="font-size:0.82rem;">
                                {{ $plan->trial_days > 0 ? $plan->trial_days . ' days' : '—' }}
                            </td>
                            <td>
                                <div style="display:flex;gap:4px;flex-direction:column;align-items:flex-start;">
                                    <span class="badge badge--{{ $plan->is_active ? 'success' : 'default' }}" style="font-size:0.72rem;">
                                        {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    @if($plan->is_public)
                                        <span class="badge badge--info" style="font-size:0.68rem;">Public</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('portals.admin.subscription.plans.toggle', $plan->id) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn--sm {{ $plan->is_active ? 'btn--warning' : 'btn--success' }}"
                                            onclick="return confirm('{{ $plan->is_active ? 'Deactivate' : 'Activate' }} this plan?')">
                                        {{ $plan->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center;padding:40px;color:#9ca3af;">
                                <i data-lucide="layers" style="width:32px;height:32px;display:block;margin:0 auto 10px;"></i>
                                No subscription plans yet. Create your first plan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($plans->hasPages())<div class="portal-card__footer">{{ $plans->links() }}</div>@endif
    </div>

</div>

{{-- Create Plan Modal --}}
<div id="createPlanModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)closeModal('createPlanModal')">
    <div class="modal-box" style="max-width:560px;width:95%;max-height:90vh;overflow-y:auto;">
        <div class="modal-header">
            <h3 class="modal-title"><i data-lucide="layers" style="width:16px;height:16px;"></i> New Subscription Plan</h3>
            <button class="modal-close" onclick="closeModal('createPlanModal')">&times;</button>
        </div>
        <form method="POST" action="{{ route('portals.admin.subscription.plans.store') }}">
            @csrf
            <div class="modal-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Plan Name <span style="color:red">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Starter">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Billing Cycle <span style="color:red">*</span></label>
                        <select name="billing_cycle" class="form-control" required>
                            <option value="monthly">Monthly</option>
                            <option value="annual">Annual</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Price (FCFA) <span style="color:red">*</span></label>
                        <input type="number" name="price" class="form-control" required min="0" step="1" placeholder="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Trial Days</label>
                        <input type="number" name="trial_days" class="form-control" min="0" value="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Max Facilities</label>
                        <input type="number" name="max_facilities" class="form-control" min="1" value="1">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Max Staff (blank = unlimited)</label>
                        <input type="number" name="max_staff" class="form-control" min="1" placeholder="Unlimited">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Max Patients/Month (blank = unlimited)</label>
                        <input type="number" name="max_patients_per_month" class="form-control" min="1" placeholder="Unlimited">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Brief description of this plan…"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Module Features (feature keys, one per line)</label>
                    <textarea id="featureKeysRaw" class="form-control" rows="4"
                              placeholder="MODULE_CDSS&#10;MODULE_BRIDGE&#10;API_SDK&#10;WEBHOOKS&#10;ANALYTICS_ADVANCED"></textarea>
                    <p style="font-size:0.75rem;color:#9ca3af;margin-top:4px;">Enter one feature key per line. These control module entitlements.</p>
                </div>
                <div style="display:flex;align-items:center;gap:10px;">
                    <input type="checkbox" name="is_public" value="1" id="isPub" checked>
                    <label for="isPub" style="font-size:0.85rem;cursor:pointer;">Publicly visible on pricing page</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--outline" onclick="closeModal('createPlanModal')">Cancel</button>
                <button type="submit" class="btn btn--primary" onclick="buildFeatureInputs()">Create Plan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id){ document.getElementById(id).style.display='flex'; lucide.createIcons(); }
function closeModal(id){ document.getElementById(id).style.display='none'; }

function buildFeatureInputs() {
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
</script>
@endsection
