@extends('layouts.portal')

@section('title', 'Insurance Claims')

@section('sidebar_role_badge')
<div class="sidebar-role-badge">Insurance</div>
@endsection
@section('sidebar_user_role', 'Insurance Admin')

@section('sidebar_nav')
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Insurance</div>
    <a href="{{ route('portals.insurance.providers') }}" class="sidebar-link">
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
    <a href="{{ route('portals.insurance.claims') }}" class="sidebar-link active">
        <i data-lucide="file-text"></i>
        <span>Claims</span>
    </a>
</div>
@endsection

@section('breadcrumb_home', 'Insurance Portal')
@section('breadcrumb_home_url', route('portals.insurance.providers'))
@section('breadcrumb_section', 'Claims')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Insurance Claims</h1>
        <p class="page-subtitle">Create, submit, and track insurance claims with payers.</p>
    </div>
    <button type="button" class="btn btn-primary btn-sm" onclick="openClaimModal()">
        <i data-lucide="plus-circle" style="width:14px;height:14px;"></i>
        New Claim
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
<form method="GET" action="{{ route('portals.insurance.claims') }}" class="filter-bar" style="flex-wrap:wrap;gap:.5rem;">
    <select name="status" class="form-control">
        <option value="">All Statuses</option>
        @foreach(['draft','submitted','under_review','more_information_required','approved','partially_approved','rejected','paid','partially_paid','cancelled','disputed'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-primary btn-sm">
        <i data-lucide="filter" style="width:13px;height:13px;"></i> Filter
    </button>
    <a href="{{ route('portals.insurance.claims') }}" class="btn btn-ghost btn-sm">Clear</a>
</form>

<div class="panel">
    <div class="panel-body" style="padding:0;">
        @if(count($claims) === 0)
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="file-text"></i></div>
                <h3>No Claims</h3>
                <p>Create an insurance claim from a patient invoice to begin the reimbursement process.</p>
                <button type="button" class="btn btn-primary btn-sm" style="margin-top:1rem;" onclick="openClaimModal()">
                    New Claim
                </button>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Claim #</th>
                            <th>Policy / Payer</th>
                            <th>Claimed</th>
                            <th>Approved</th>
                            <th>Paid</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($claims as $claim)
                        @php
                            $statusBadge = match($claim->status) {
                                'approved','paid'          => 'badge-success',
                                'partially_approved','partially_paid' => 'badge-teal',
                                'rejected','cancelled'     => 'badge-danger',
                                'submitted','under_review' => 'badge-primary',
                                'more_information_required','disputed' => 'badge-warning',
                                default                    => 'badge-neutral',
                            };
                        @endphp
                        <tr>
                            <td data-label="Claim #">
                                <span style="font-family:monospace;font-size:var(--p-text-xs);">{{ $claim->claim_number }}</span>
                            </td>
                            <td data-label="Policy / Payer">
                                <div style="font-size:var(--p-text-xs);">
                                    <strong>{{ $claim->policy->plan->provider->name ?? '--' }}</strong><br>
                                    {{ $claim->policy->policy_number ?? '--' }}
                                </div>
                            </td>
                            <td data-label="Claimed">{{ number_format($claim->claimed_amount, 2) }}</td>
                            <td data-label="Approved">
                                {{ $claim->approved_amount !== null ? number_format($claim->approved_amount, 2) : '--' }}
                            </td>
                            <td data-label="Paid">
                                {{ $claim->paid_amount !== null ? number_format($claim->paid_amount, 2) : '--' }}
                            </td>
                            <td data-label="Submitted">
                                {{ $claim->submitted_at ? \Carbon\Carbon::parse($claim->submitted_at)->format('M d, Y') : '--' }}
                            </td>
                            <td data-label="Status">
                                <span class="badge {{ $statusBadge }}">{{ ucwords(str_replace('_',' ',$claim->status)) }}</span>
                            </td>
                            <td data-label="Actions">
                                <div style="display:flex;gap:.35rem;flex-wrap:wrap;">
                                    @if($claim->isDraft())
                                        <form method="POST" action="{{ route('portals.insurance.claims.submit', $claim->id) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-xs">
                                                <i data-lucide="send" style="width:11px;height:11px;"></i>
                                                Submit
                                            </button>
                                        </form>
                                    @endif
                                    @if($claim->canReceiveDecision())
                                        <button type="button" class="btn btn-teal btn-xs"
                                            onclick="openDecideModal('{{ $claim->id }}')">
                                            <i data-lucide="gavel" style="width:11px;height:11px;"></i>
                                            Decide
                                        </button>
                                    @endif
                                    @if($claim->canReceivePayment())
                                        <button type="button" class="btn btn-success btn-xs"
                                            onclick="openPayModal('{{ $claim->id }}', {{ $claim->approved_amount ?? $claim->claimed_amount }})">
                                            <i data-lucide="banknote" style="width:11px;height:11px;"></i>
                                            Pay
                                        </button>
                                    @endif
                                    @if(in_array($claim->status, ['draft','submitted','under_review','more_information_required']))
                                        <form method="POST" action="{{ route('portals.insurance.claims.cancel', $claim->id) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-ghost btn-xs"
                                                onclick="return confirm('Cancel this claim?')">
                                                Cancel
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

{{-- New Claim Modal --}}
<div id="claim-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;overflow-y:auto;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:600px;margin:1rem;">
        <h3 style="margin:0 0 1.25rem;font-size:1.1rem;">Create Insurance Claim</h3>
        <form method="POST" action="{{ route('portals.insurance.claims.store') }}">
            @csrf
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Patient Policy *</label>
                @if(count($policies) > 0)
                    <select name="policy_id" class="form-control" required>
                        <option value="">— Select Policy —</option>
                        @foreach($policies as $policy)
                            <option value="{{ $policy->id }}">
                                {{ $policy->plan->provider->name ?? '' }} — {{ $policy->plan->name ?? '' }}
                                ({{ $policy->policy_number }})
                            </option>
                        @endforeach
                    </select>
                @else
                    <input type="text" name="policy_id" class="form-control" required placeholder="Policy ID">
                @endif
            </div>

            <h3 style="font-size:.9rem;font-weight:700;margin:1.25rem 0 .75rem;color:var(--p-text-secondary);">
                Claim Line Items
            </h3>
            <div id="claim-items">
                <div class="line-item" style="display:grid;grid-template-columns:1fr auto auto auto;gap:.5rem;margin-bottom:.5rem;align-items:end;">
                    <div>
                        <label class="form-label" style="font-size:.75rem;">Description *</label>
                        <input type="text" name="items[0][description]" class="form-control" required placeholder="Service or procedure…">
                    </div>
                    <div style="width:60px;">
                        <label class="form-label" style="font-size:.75rem;">Qty</label>
                        <input type="number" name="items[0][quantity]" class="form-control" value="1" min="1" step="1" required>
                    </div>
                    <div style="width:110px;">
                        <label class="form-label" style="font-size:.75rem;">Unit Price *</label>
                        <input type="number" name="items[0][unit_price]" class="form-control" value="0.00" min="0" step="0.01" required>
                    </div>
                    <div style="padding-bottom:2px;">
                        <button type="button" class="btn btn-ghost btn-xs" disabled style="visibility:hidden;">
                            <i data-lucide="trash-2" style="width:12px;height:12px;"></i>
                        </button>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-ghost btn-sm" onclick="addClaimItem()" style="margin-bottom:1.25rem;">
                <i data-lucide="plus" style="width:13px;height:13px;"></i>
                Add Item
            </button>

            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2" maxlength="1000"></textarea>
            </div>

            <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1rem;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeClaimModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="file-plus" style="width:13px;height:13px;"></i>
                    Create Claim
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Decide Modal --}}
<div id="decide-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:460px;margin:1rem;">
        <h3 style="margin:0 0 1.25rem;font-size:1.1rem;">Record Claim Decision</h3>
        <form id="decide-form" method="POST" action="">
            @csrf
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Decision *</label>
                <select name="decision" class="form-control" required>
                    <option value="approved">Approved</option>
                    <option value="partially_approved">Partially Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="more_information_required">More Information Required</option>
                    <option value="disputed">Disputed</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Approved Amount</label>
                <input type="number" name="approved_amount" class="form-control" min="0" step="0.01" placeholder="0.00">
            </div>
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Reason *</label>
                <textarea name="reason" class="form-control" rows="3" required maxlength="1000"></textarea>
            </div>
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Missing Information (if applicable)</label>
                <textarea name="missing_information" class="form-control" rows="2" maxlength="1000"></textarea>
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1rem;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeDecideModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="gavel" style="width:13px;height:13px;"></i>
                    Record Decision
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Payment Modal --}}
<div id="pay-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:400px;margin:1rem;">
        <h3 style="margin:0 0 1.25rem;font-size:1.1rem;">Record Claim Payment</h3>
        <form id="pay-form" method="POST" action="">
            @csrf
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Amount *</label>
                <input type="number" id="pay-amount" name="amount" class="form-control" min="0.01" step="0.01" required>
            </div>
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Payment Method *</label>
                <select name="payment_method" class="form-control" required>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="cheque">Cheque</option>
                    <option value="eft">EFT</option>
                    <option value="cash">Cash</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Reference Number</label>
                <input type="text" name="reference_number" class="form-control" maxlength="100">
            </div>
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2" maxlength="500"></textarea>
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1rem;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closePayModal()">Cancel</button>
                <button type="submit" class="btn btn-success btn-sm">
                    <i data-lucide="banknote" style="width:13px;height:13px;"></i>
                    Record Payment
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    var claimItemCount = 1;

    function addClaimItem() {
        var container = document.getElementById('claim-items');
        var idx = claimItemCount++;
        var row = document.createElement('div');
        row.className = 'line-item';
        row.style.cssText = 'display:grid;grid-template-columns:1fr auto auto auto;gap:.5rem;margin-bottom:.5rem;align-items:end;';
        row.innerHTML =
            '<div><input type="text" name="items[' + idx + '][description]" class="form-control" required placeholder="Service or procedure…"></div>' +
            '<div style="width:60px;"><input type="number" name="items[' + idx + '][quantity]" class="form-control" value="1" min="1" step="1" required></div>' +
            '<div style="width:110px;"><input type="number" name="items[' + idx + '][unit_price]" class="form-control" value="0.00" min="0" step="0.01" required></div>' +
            '<div style="padding-bottom:2px;"><button type="button" class="btn btn-ghost btn-xs" onclick="this.closest(\'.line-item\').remove()">' +
            '<i data-lucide="trash-2" style="width:12px;height:12px;"></i></button></div>';
        container.appendChild(row);
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function openClaimModal() { document.getElementById('claim-modal').style.display = 'flex'; }
    function closeClaimModal() { document.getElementById('claim-modal').style.display = 'none'; }
    document.getElementById('claim-modal').addEventListener('click', function(e) {
        if (e.target === this) closeClaimModal();
    });

    function openDecideModal(id) {
        document.getElementById('decide-form').setAttribute('action',
            '{{ url("/portals/insurance/claims") }}/' + id + '/decide');
        document.getElementById('decide-modal').style.display = 'flex';
    }
    function closeDecideModal() { document.getElementById('decide-modal').style.display = 'none'; }
    document.getElementById('decide-modal').addEventListener('click', function(e) {
        if (e.target === this) closeDecideModal();
    });

    function openPayModal(id, amount) {
        document.getElementById('pay-form').setAttribute('action',
            '{{ url("/portals/insurance/claims") }}/' + id + '/pay');
        document.getElementById('pay-amount').value = amount;
        document.getElementById('pay-modal').style.display = 'flex';
    }
    function closePayModal() { document.getElementById('pay-modal').style.display = 'none'; }
    document.getElementById('pay-modal').addEventListener('click', function(e) {
        if (e.target === this) closePayModal();
    });
</script>
@endsection
