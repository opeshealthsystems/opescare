@extends('layouts.portal')
@section('title', 'Subscription Invoices — Admin')
@section('sidebar') @include('portals.admin.control_center._sidebar') @endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">
                <i data-lucide="file-text" style="width:22px;height:22px;vertical-align:middle;margin-right:8px;color:#7c3aed;"></i>
                Subscription Invoices
            </h1>
            <p class="portal-page-subtitle">Platform billing invoices for all facilities</p>
        </div>
        <a href="{{ route('portals.admin.subscription') }}" class="btn btn--outline btn--sm">
            <i data-lucide="arrow-left" style="width:13px;height:13px;"></i> Subscriptions
        </a>
    </div>

    @if(session('success'))<div class="alert alert--success">{{ session('success') }}</div>@endif

    {{-- KPI Strip --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:20px;">
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#f0fdf4;"><i data-lucide="banknote" style="color:#16a34a;"></i></div>
            <div class="stat-card__body">
                <div class="stat-card__value" style="color:#16a34a;">NGN {{ number_format($stats['paid_this_month'] / 100, 0) }}</div>
                <div class="stat-card__label">Paid This Month</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#fffbeb;"><i data-lucide="clock" style="color:#d97706;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value" style="color:#d97706;">{{ $stats['pending_count'] }}</div><div class="stat-card__label">Pending</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#fee2e2;"><i data-lucide="alert-triangle" style="color:#dc2626;"></i></div>
            <div class="stat-card__body">
                <div class="stat-card__value" style="color:#dc2626;">{{ $stats['overdue_count'] }}</div>
                <div class="stat-card__label">Overdue</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#fee2e2;"><i data-lucide="banknote" style="color:#dc2626;"></i></div>
            <div class="stat-card__body">
                <div class="stat-card__value" style="color:#dc2626;">NGN {{ number_format($stats['overdue_amount'] / 100, 0) }}</div>
                <div class="stat-card__label">Overdue Amount</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="filter-bar" style="margin-bottom:16px;">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search invoice #…" style="max-width:200px;">
        <select name="status" class="form-control" style="max-width:160px;">
            <option value="">All Statuses</option>
            <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent / Pending</option>
            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
            <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
            <option value="void" {{ request('status') === 'void' ? 'selected' : '' }}>Void</option>
        </select>
        <button type="submit" class="btn btn--outline btn--sm">Filter</button>
        <a href="{{ route('portals.admin.subscription.invoices') }}" class="btn btn--outline btn--sm">Clear</a>
    </form>

    <div class="portal-card">
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Organization</th>
                        <th>Invoice Date</th>
                        <th>Due Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        <tr style="{{ $inv->isOverdue() ? 'background:#fff5f5;' : '' }}">
                            <td><code style="font-size:0.8rem;">{{ $inv->invoice_number }}</code></td>
                            <td style="font-size:0.84rem;">
                                {{ $inv->subscription?->organization_name ?? '—' }}
                            </td>
                            <td style="font-size:0.82rem;color:#6b7280;">{{ $inv->invoice_date->format('d M Y') }}</td>
                            <td style="font-size:0.82rem;color:{{ $inv->isOverdue() ? '#dc2626' : '#6b7280' }};font-weight:{{ $inv->isOverdue() ? '700' : '400' }};">
                                {{ $inv->due_date->format('d M Y') }}
                                @if($inv->isOverdue())<div style="font-size:0.71rem;">OVERDUE</div>@endif
                            </td>
                            <td style="font-weight:700;font-size:0.88rem;">{{ $inv->totalFormatted() }}</td>
                            <td>
                                <span class="badge badge--{{ $inv->statusColor() }}" style="font-size:0.72rem;">{{ ucfirst($inv->status) }}</span>
                            </td>
                            <td>
                                @if(in_array($inv->status, ['sent','overdue']))
                                    <button class="btn btn--sm btn--success" onclick="openPayModal('{{ $inv->id }}')">
                                        <i data-lucide="check" style="width:12px;height:12px;"></i> Mark Paid
                                    </button>
                                @endif
                                <a href="{{ route('portals.admin.subscription.detail', $inv->subscription_id) }}"
                                   class="btn btn--sm btn--outline">View Sub</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;padding:40px;color:#9ca3af;">No invoices found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())<div class="portal-card__footer">{{ $invoices->links() }}</div>@endif
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
