@extends('layouts.portal')
@section('title', 'Developer Production Requests — Admin')
@section('sidebar') @include('portals.admin.connect._sidebar') @endsection

@section('content')

<div class="breadcrumb">
    <a href="{{ route('portals.admin.connect') }}">Connect Suite</a>
    <i data-lucide="chevron-right"></i>
    <span>Production Requests</span>
</div>

<div class="page-head">
    <h2>Developer Production Requests</h2>
    <div class="page-head__spacer"></div>
</div>
<p class="td-muted mb-6">Review and action production access requests from external developers</p>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

{{-- Stats strip --}}
<div class="stat-grid mb-6">
    <div class="stat-card stat-card--warning">
        <div class="stat-card__value">{{ $stats['pending'] }}</div>
        <div class="stat-card__label">Pending Review</div>
    </div>
    <div class="stat-card stat-card--primary">
        <div class="stat-card__value">{{ $stats['under_review'] }}</div>
        <div class="stat-card__label">Under Review</div>
    </div>
    <div class="stat-card stat-card--success">
        <div class="stat-card__value">{{ $stats['approved'] }}</div>
        <div class="stat-card__label">Approved</div>
    </div>
    <div class="stat-card stat-card--danger">
        <div class="stat-card__value">{{ $stats['rejected'] }}</div>
        <div class="stat-card__label">Rejected</div>
    </div>
</div>

{{-- Filter tabs --}}
<div class="tabs mb-6">
    @foreach(['all'=>'All','pending'=>'Pending','under_review'=>'Under Review','approved'=>'Approved','rejected'=>'Rejected'] as $val=>$label)
    @php $target = $val === 'all' ? null : $val; $isActive = request('status', $target) === $target; @endphp
    <a href="{{ request()->fullUrlWithQuery(['status'=>$target]) }}" class="tab {{ $isActive ? 'active' : '' }}">{{ $label }}</a>
    @endforeach
</div>

@if($requests->isEmpty())
<div class="empty-state">
    <div class="empty-state-icon"><i data-lucide="clipboard-list"></i></div>
    <p>No production access requests found.</p>
</div>
@else
<div class="panel">
    <div class="table-wrapper">
        <table class="data-table">
            <thead><tr>
                <th>Developer / App</th>
                <th>Use Case</th>
                <th>Scopes</th>
                <th>Patient Data</th>
                <th>Status</th>
                <th>Submitted</th>
                <th class="row-actions">Actions</th>
            </tr></thead>
            <tbody>
            @foreach($requests as $req)
            <tr>
                <td data-label="Developer / App">
                    <div class="td-strong">{{ $req->developerAccount->display_name ?? $req->developerAccount->email ?? '—' }}</div>
                    @if($req->integration_client_id)<div class="code-token">{{ Str::limit($req->integration_client_id, 28) }}</div>@endif
                </td>
                <td data-label="Use Case">
                    <div>{{ Str::limit($req->use_case, 55) }}</div>
                    @if($req->technical_description)<div class="td-muted">{{ Str::limit($req->technical_description, 60) }}</div>@endif
                </td>
                <td data-label="Scopes"><span class="badge badge-neutral">{{ count((array)$req->requested_scopes) }} scopes</span></td>
                <td data-label="Patient Data">
                    @if($req->handles_patient_data)
                    <span class="badge badge-warning">Yes</span>
                    @else
                    <span class="badge badge-neutral">No</span>
                    @endif
                </td>
                <td data-label="Status">
                    <span class="{{ $req->statusBadgeClass() }}">{{ ucfirst(str_replace('_',' ',$req->status)) }}</span>
                    @if($req->reviewed_at)<div class="td-muted">{{ $req->reviewed_at->format('d M Y') }}</div>@endif
                </td>
                <td data-label="Submitted">
                    {{ $req->created_at->format('d M Y') }}
                    @if($req->estimated_daily_requests)<div class="td-muted">{{ $req->estimated_daily_requests }} req/day</div>@endif
                </td>
                <td class="row-actions" data-label="Actions">
                    @if(in_array($req->status, ['pending','under_review']))
                        <button type="button" class="btn btn-success btn-sm" onclick="opOpenModal('approve-{{ $req->id }}')"><i data-lucide="check"></i> Approve</button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="opOpenModal('reject-{{ $req->id }}')"><i data-lucide="x"></i> Reject</button>
                    @elseif($req->status === 'approved')
                        <span class="text-success">Approved</span>
                        @if($req->review_notes)<div class="td-muted">{{ Str::limit($req->review_notes, 30) }}</div>@endif
                    @else
                        <span class="text-danger">Rejected</span>
                        @if($req->rejected_reason)<div class="td-muted">{{ Str::limit($req->rejected_reason, 30) }}</div>@endif
                    @endif
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="panel-body">{{ $requests->links() }}</div>
</div>

{{-- Approve / Reject confirm modals --}}
@foreach($requests as $req)
    @if(in_array($req->status, ['pending','under_review']))
    <div id="approve-{{ $req->id }}" class="modal-backdrop mt-6" hidden>
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="approve-{{ $req->id }}-title">
            <h3 class="modal__title" id="approve-{{ $req->id }}-title"><i data-lucide="check-circle"></i> Approve request</h3>
            <form method="POST" action="{{ route('portals.admin.developer.production_requests.approve', $req->id) }}">
                @csrf
                <div class="modal__body"><p>Approve this production access request?</p></div>
                <div class="modal__footer">
                    <button type="button" class="btn btn-ghost" onclick="opCloseModal('approve-{{ $req->id }}')">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve</button>
                </div>
            </form>
        </div>
    </div>
    <div id="reject-{{ $req->id }}" class="modal-backdrop mt-6" hidden>
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="reject-{{ $req->id }}-title">
            <h3 class="modal__title" id="reject-{{ $req->id }}-title"><i data-lucide="x-circle"></i> Reject request</h3>
            <form method="POST" action="{{ route('portals.admin.developer.production_requests.reject', $req->id) }}">
                @csrf
                <div class="modal__body">
                    <p>Reject this request? Provide a reason for the audit trail.</p>
                    <textarea name="reason" rows="3" required placeholder="Rejection reason…" class="form-control"></textarea>
                </div>
                <div class="modal__footer">
                    <button type="button" class="btn btn-ghost" onclick="opCloseModal('reject-{{ $req->id }}')">Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm Reject</button>
                </div>
            </form>
        </div>
    </div>
    @endif
@endforeach
@endif

@endsection
@section('scripts')
<script>
function opOpenModal(id){ document.getElementById(id).removeAttribute('hidden'); }
function opCloseModal(id){ document.getElementById(id).setAttribute('hidden',''); }
document.addEventListener('keydown', function(e){
    if(e.key==='Escape'){ document.querySelectorAll('.modal-backdrop').forEach(function(m){ m.setAttribute('hidden',''); }); }
});
</script>
@endsection
