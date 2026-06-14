@extends('layouts.portal')
@section('title', 'Subscription Detail — ' . $subscription->organization_name)
@section('sidebar') @include('portals.admin.control_center._sidebar') @endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">{{ $subscription->organization_name }}</h1>
            <p class="portal-page-subtitle">
                {{ $subscription->plan->name ?? '—' }} ·
                <span class="badge badge--{{ $subscription->statusColor() }}" style="font-size:0.75rem;">
                    {{ ucfirst(str_replace('_',' ',$subscription->status)) }}
                </span>
            </p>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('portals.admin.subscription') }}" class="btn btn--outline btn--sm">
                <i data-lucide="arrow-left" style="width:13px;height:13px;"></i> All Subscriptions
            </a>
            @if(!in_array($subscription->status, ['cancelled','expired']))
                <button class="btn btn--sm btn--danger" onclick="openModal('cancelModal')">
                    <i data-lucide="x-circle" style="width:13px;height:13px;"></i> Cancel
                </button>
            @endif
        </div>
    </div>

    @if(session('success'))<div class="alert alert--success">{{ session('success') }}</div>@endif

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px;">

        {{-- Subscription Info --}}
        <div class="portal-card">
            <div class="portal-card__header"><h2 class="portal-card__title">Subscription Details</h2></div>
            <div class="portal-card__body">
                <table style="width:100%;font-size:0.84rem;border-collapse:collapse;">
                    <tr><td style="padding:5px 0;color:#6b7280;width:40%;">Organization</td><td style="font-weight:600;">{{ $subscription->organization_name }}</td></tr>
                    <tr><td style="padding:5px 0;color:#6b7280;">Plan</td><td style="font-weight:600;">{{ $subscription->plan->name ?? '—' }}</td></tr>
                    <tr><td style="padding:5px 0;color:#6b7280;">Billing Cycle</td><td>{{ ucfirst($subscription->plan->billing_cycle ?? '—') }}</td></tr>
                    <tr><td style="padding:5px 0;color:#6b7280;">Price</td><td style="font-weight:600;">{{ $subscription->plan->priceFormatted() ?? '—' }}</td></tr>
                    <tr><td style="padding:5px 0;color:#6b7280;">Discount</td><td>{{ $subscription->discount_percent }}%</td></tr>
                    <tr><td style="padding:5px 0;color:#6b7280;">Period</td><td>{{ $subscription->current_period_start->format('d M Y') }} → {{ $subscription->current_period_end->format('d M Y') }}</td></tr>
                    <tr><td style="padding:5px 0;color:#6b7280;">Days Left</td><td style="font-weight:600;color:{{ $subscription->daysUntilExpiry() < 7 ? '#dc2626' : '#16a34a' }};">{{ $subscription->daysUntilExpiry() }} days</td></tr>
                    <tr><td style="padding:5px 0;color:#6b7280;">Auto-Renew</td><td>{!! $subscription->auto_renew ? '<i data-lucide="check" style="width:14px;height:14px;vertical-align:-2px;"></i> Yes' : '<i data-lucide="x" style="width:14px;height:14px;vertical-align:-2px;"></i> No' !!}</td></tr>
                    @if($subscription->billing_email)
                        <tr><td style="padding:5px 0;color:#6b7280;">Billing Contact</td><td>{{ $subscription->billing_name }}<br><span style="color:#9ca3af;font-size:0.8rem;">{{ $subscription->billing_email }}</span></td></tr>
                    @endif
                    @if($subscription->notes)
                        <tr><td style="padding:5px 0;color:#6b7280;vertical-align:top;">Notes</td><td style="font-size:0.8rem;color:#6b7280;">{{ $subscription->notes }}</td></tr>
                    @endif
                </table>

                {{-- Change Plan --}}
                @if(!in_array($subscription->status, ['cancelled','expired']))
                    <form method="POST" action="{{ route('portals.admin.subscription.change_plan', $subscription->id) }}"
                          style="display:flex;gap:8px;margin-top:16px;padding-top:14px;border-top:1px solid #f3f4f6;">
                        @csrf
                        <select name="plan_id" class="form-control" style="flex:1;">
                            @foreach($plans as $p)
                                <option value="{{ $p->id }}" {{ $p->id === $subscription->plan_id ? 'selected' : '' }}>
                                    {{ $p->name }} — {{ $p->priceFormatted() }}
                                </option>
                            @endforeach
                        </select>
                        <button class="btn btn--sm btn--outline"
                                onclick="return confirm('Change the subscription plan?')">Change Plan</button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Module Entitlements --}}
        <div class="portal-card">
            <div class="portal-card__header"><h2 class="portal-card__title">Module Entitlements</h2></div>
            <div class="portal-card__body" style="padding:0;">
                <table class="portal-table">
                    <thead><tr><th>Module</th><th>Status</th><th>Granted</th></tr></thead>
                    <tbody>
                        @forelse($subscription->moduleEntitlements as $ent)
                            <tr>
                                <td><code style="font-size:0.78rem;">{{ $ent->module_key }}</code></td>
                                <td>
                                    <span class="badge badge--{{ $ent->isActive() ? 'success' : 'default' }}" style="font-size:0.7rem;">
                                        {{ $ent->isActive() ? 'Active' : 'Revoked' }}
                                    </span>
                                </td>
                                <td style="font-size:0.78rem;color:#6b7280;">{{ $ent->granted_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" style="text-align:center;padding:16px;color:#9ca3af;">No entitlements</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- Invoices --}}
    <div class="portal-card" style="margin-bottom:16px;">
        <div class="portal-card__header"><h2 class="portal-card__title">Invoices</h2></div>
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table">
                <thead>
                    <tr><th>Invoice #</th><th>Date</th><th>Due</th><th>Amount</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($subscription->invoices->sortByDesc('invoice_date') as $inv)
                        <tr>
                            <td><code style="font-size:0.8rem;">{{ $inv->invoice_number }}</code></td>
                            <td style="font-size:0.82rem;">{{ $inv->invoice_date->format('d M Y') }}</td>
                            <td style="font-size:0.82rem;color:{{ $inv->isOverdue() ? '#dc2626' : '#6b7280' }};">{{ $inv->due_date->format('d M Y') }}</td>
                            <td style="font-weight:600;font-size:0.85rem;">{{ $inv->totalFormatted() }}</td>
                            <td>
                                <span class="badge badge--{{ $inv->statusColor() }}" style="font-size:0.72rem;">{{ ucfirst($inv->status) }}</span>
                            </td>
                            <td>
                                @if(in_array($inv->status, ['sent','overdue']))
                                    <button class="btn btn--sm btn--success" onclick="openPayModal('{{ $inv->id }}')">Mark Paid</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center;padding:16px;color:#9ca3af;">No invoices.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- Cancel Modal --}}
<div id="cancelModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)closeModal('cancelModal')">
    <div class="modal-box" style="max-width:420px;width:95%;">
        <div class="modal-header">
            <h3 class="modal-title"><i data-lucide="x-circle" style="width:16px;height:16px;"></i> Cancel Subscription</h3>
            <button class="modal-close" onclick="closeModal('cancelModal')">&times;</button>
        </div>
        <form method="POST" action="{{ route('portals.admin.subscription.cancel', $subscription->id) }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Cancellation Reason <span style="color:red">*</span></label>
                    <textarea name="reason" class="form-control" rows="3" required minlength="5" maxlength="500"
                              placeholder="Reason for cancellation…"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--outline" onclick="closeModal('cancelModal')">Abort</button>
                <button type="submit" class="btn btn--danger">Confirm Cancellation</button>
            </div>
        </form>
    </div>
</div>

{{-- Mark Paid Modal --}}
<div id="payModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)closeModal('payModal')">
    <div class="modal-box" style="max-width:380px;width:95%;">
        <div class="modal-header">
            <h3 class="modal-title">Mark Invoice Paid</h3>
            <button class="modal-close" onclick="closeModal('payModal')">&times;</button>
        </div>
        <form id="payForm" method="POST" action="">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Payment Reference <span style="color:red">*</span></label>
                    <input type="text" name="payment_reference" class="form-control" required placeholder="e.g. TRN-20260519-001">
                </div>
                <div class="form-group">
                    <label class="form-label">Payment Method <span style="color:red">*</span></label>
                    <select name="payment_method" class="form-control" required>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="card">Card</option>
                        <option value="ussd">USSD</option>
                        <option value="cash">Cash</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--outline" onclick="closeModal('payModal')">Cancel</button>
                <button type="submit" class="btn btn--primary">Confirm Payment</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id){ document.getElementById(id).style.display='flex'; lucide.createIcons(); }
function closeModal(id){ document.getElementById(id).style.display='none'; }
function openPayModal(invoiceId) {
    const base = '{{ url("portals/admin/subscription/invoices") }}';
    document.getElementById('payForm').action = base + '/' + invoiceId + '/mark-paid';
    openModal('payModal');
}
</script>
@endsection
