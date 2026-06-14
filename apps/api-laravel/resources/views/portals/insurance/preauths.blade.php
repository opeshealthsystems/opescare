@extends('layouts.portal')

@section('title', 'Preauthorization Requests')

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
    <a href="{{ route('portals.insurance.providers') }}" class="sidebar-link">
        <i data-lucide="building-2"></i>
        <span>Providers & Plans</span>
    </a>
    <a href="{{ route('portals.insurance.policies') }}" class="sidebar-link">
        <i data-lucide="shield-check"></i>
        <span>Patient Policies</span>
    </a>
    <a href="{{ route('portals.insurance.preauths') }}" class="sidebar-link active">
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
@section('breadcrumb_section', 'Preauthorization')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Preauthorization Requests</h1>
        <p class="page-subtitle">Request and track prior approvals for services and procedures.</p>
    </div>
    <button type="button" class="btn btn-primary btn-sm" onclick="openPreauthModal()">
        <i data-lucide="plus-circle"></i>
        New Request
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
<form method="GET" action="{{ route('portals.insurance.preauths') }}" class="filter-bar">
    <select name="status" class="filter-select">
        <option value="">All Statuses</option>
        @foreach(['draft','submitted','under_review','approved','rejected','more_information_required','expired','cancelled'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-primary btn-sm">
        <i data-lucide="filter"></i> Filter
    </button>
    <a href="{{ route('portals.insurance.preauths') }}" class="btn btn-ghost btn-sm">Clear</a>
</form>

<div class="panel">
    <div class="panel-body--flush">
        @if(count($preauths) === 0)
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="clipboard-list"></i></div>
                <h3>No Preauthorization Requests</h3>
                <p>Create a preauthorization request for services that require prior approval.</p>
                <button type="button" class="btn btn-primary btn-sm" onclick="openPreauthModal()">
                    New Request
                </button>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Policy</th>
                            <th>Estimated</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th>Decision</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($preauths as $pa)
                        @php
                            $statusBadge = match($pa->status) {
                                'approved'                  => 'badge-success',
                                'rejected'                  => 'badge-danger',
                                'submitted','under_review'  => 'badge-primary',
                                'more_information_required' => 'badge-warning',
                                'expired','cancelled'       => 'badge-neutral',
                                default                     => 'badge-neutral',
                            };
                            $decision = $pa->latestDecision;
                        @endphp
                        <tr>
                            <td data-label="Service">
                                <span class="td-strong">{{ Str::limit($pa->service_description, 60) }}</span>
                            </td>
                            <td data-label="Policy">
                                <span class="td-strong">{{ $pa->policy->plan->provider->name ?? '--' }}</span>
                                <div class="td-muted">{{ $pa->policy->policy_number ?? '--' }}</div>
                            </td>
                            <td data-label="Estimated">
                                {{ $pa->estimated_amount ? number_format($pa->estimated_amount, 2) : '--' }}
                            </td>
                            <td data-label="Submitted">
                                {{ $pa->submitted_at ? \Carbon\Carbon::parse($pa->submitted_at)->format('M d, Y') : '--' }}
                            </td>
                            <td data-label="Status">
                                <span class="badge {{ $statusBadge }}">{{ ucwords(str_replace('_',' ',$pa->status)) }}</span>
                            </td>
                            <td data-label="Decision">
                                @if($decision)
                                    <span class="td-strong">{{ ucwords($decision->decision) }}</span>
                                    @if($decision->approved_amount)
                                        <div class="td-muted">{{ number_format($decision->approved_amount, 2) }}</div>
                                    @endif
                                @else
                                    <span class="badge badge-neutral">Pending</span>
                                @endif
                            </td>
                            <td data-label="Actions" class="row-actions">
                                @if($pa->status === 'draft')
                                    <form method="POST" action="{{ route('portals.insurance.preauths.submit', $pa->id) }}" class="inline-form">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i data-lucide="send"></i> Submit
                                        </button>
                                    </form>
                                @endif
                                @if(in_array($pa->status, ['submitted','under_review','more_information_required']))
                                    <button type="button" class="btn btn-teal btn-sm" onclick="openDecideModal('{{ $pa->id }}')">
                                        <i data-lucide="gavel"></i> Decide
                                    </button>
                                @endif
                                @if($pa->isPending())
                                    <button type="button" class="btn btn-ghost btn-sm" onclick="openCancelModal('{{ $pa->id }}')">Cancel</button>
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

{{-- New Preauth Modal --}}
<div id="preauth-modal" class="modal-fixed">
    <div class="modal-fixed__panel modal-fixed__panel--lg">
        <div class="modal-fixed__head"><h3 class="modal-fixed__title">New Preauthorization Request</h3></div>
        <form method="POST" action="{{ route('portals.insurance.preauths.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label form-label-required">Patient Policy</label>
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
            <div class="form-group">
                <label class="form-label form-label-required">Service / Procedure Description</label>
                <input type="text" name="service_description" class="form-control" required maxlength="500"
                    placeholder="e.g. MRI Brain Scan, Surgical Procedure…">
            </div>
            <div class="form-group">
                <label class="form-label">Clinical Justification</label>
                <textarea name="clinical_justification" class="form-control" rows="3" maxlength="2000"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Estimated Amount</label>
                <input type="number" name="estimated_amount" class="form-control" min="0" step="0.01" placeholder="0.00">
            </div>
            <div class="form-actions-end">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closePreauthModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="clipboard-list"></i> Create Request
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Decide Modal --}}
<div id="decide-modal" class="modal-fixed">
    <div class="modal-fixed__panel modal-fixed__panel--md">
        <div class="modal-fixed__head"><h3 class="modal-fixed__title">Record Decision</h3></div>
        <form id="decide-form" method="POST" action="">
            @csrf
            <div class="form-group">
                <label class="form-label form-label-required">Decision</label>
                <select name="decision" class="form-control" required>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="more_information_required">More Information Required</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Approved Amount</label>
                <input type="number" name="approved_amount" class="form-control" min="0" step="0.01" placeholder="0.00">
            </div>
            <div class="form-group">
                <label class="form-label">Authorization Number</label>
                <input type="text" name="authorization_number" class="form-control" maxlength="100">
            </div>
            <div class="form-group">
                <label class="form-label form-label-required">Reason</label>
                <textarea name="reason" class="form-control" rows="3" required maxlength="1000"></textarea>
            </div>
            <div class="form-actions-end">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeDecideModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="gavel"></i> Record Decision
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Cancel Request Modal --}}
<div id="cancel-modal" class="modal-fixed">
    <div class="modal-fixed__panel modal-fixed__panel--sm">
        <div class="modal-fixed__head"><h3 class="modal-fixed__title"><i data-lucide="alert-triangle"></i> Cancel request</h3></div>
        <form id="cancel-form" method="POST" action="">
            @csrf
            <p>Cancel this request?</p>
            <div class="form-actions-end">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeCancelModal()">Keep request</button>
                <button type="submit" class="btn btn-danger btn-sm">Cancel request</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openPreauthModal() { document.getElementById('preauth-modal').classList.add('open'); }
    function closePreauthModal() { document.getElementById('preauth-modal').classList.remove('open'); }
    document.getElementById('preauth-modal').addEventListener('click', function(e) {
        if (e.target === this) closePreauthModal();
    });

    function openDecideModal(id) {
        document.getElementById('decide-form').setAttribute('action',
            '{{ url("/portals/insurance/preauths") }}/' + id + '/decide');
        document.getElementById('decide-modal').classList.add('open');
    }
    function closeDecideModal() { document.getElementById('decide-modal').classList.remove('open'); }
    document.getElementById('decide-modal').addEventListener('click', function(e) {
        if (e.target === this) closeDecideModal();
    });

    function openCancelModal(id) {
        document.getElementById('cancel-form').setAttribute('action',
            '{{ url("/portals/insurance/preauths") }}/' + id + '/cancel');
        document.getElementById('cancel-modal').classList.add('open');
    }
    function closeCancelModal() { document.getElementById('cancel-modal').classList.remove('open'); }
    document.getElementById('cancel-modal').addEventListener('click', function(e) {
        if (e.target === this) closeCancelModal();
    });
</script>
@endsection
