@extends('layouts.portal')
@section('title', 'Legal Documents')
@section('sidebar') @include('portals.admin.control_center._sidebar') @endsection

@section('content')

<div class="breadcrumb">
    <a href="{{ route('portals.admin.legal') }}">Legal</a>
    <i data-lucide="chevron-right"></i>
    <span>Documents</span>
</div>

<div class="page-head">
    <h2>Legal documents</h2>
    <div class="page-head__spacer"></div>
    <button type="button" onclick="opOpenModal('newDocModal')" class="btn btn-primary btn-sm">
        <i data-lucide="plus"></i> New Document
    </button>
</div>

<p class="td-muted mb-6">Terms, policies, consent forms, and partner agreements.</p>

{{-- KPI Strip --}}
<div class="stat-grid mb-6">
    <div class="stat-card stat-card--primary">
        <div class="stat-card__head"><i data-lucide="file-text"></i><span class="stat-card__label">Documents</span></div>
        <div class="stat-card__value">{{ $stats['total_documents'] }}</div>
    </div>
    <div class="stat-card stat-card--primary">
        <div class="stat-card__head"><i data-lucide="layers"></i><span class="stat-card__label">Versions</span></div>
        <div class="stat-card__value">{{ $stats['total_versions'] }}</div>
    </div>
    <div class="stat-card stat-card--success">
        <div class="stat-card__head"><i data-lucide="check-circle"></i><span class="stat-card__label">User acceptances</span></div>
        <div class="stat-card__value">{{ number_format($stats['user_acceptances']) }}</div>
    </div>
    <div class="stat-card stat-card--warning">
        <div class="stat-card__head"><i data-lucide="handshake"></i><span class="stat-card__label">Partner agreements</span></div>
        <div class="stat-card__value">{{ number_format($stats['partner_acceptances']) }}</div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h3 class="panel-title"><i data-lucide="scale"></i> All legal documents</h3>
        <div class="row-actions-inline">
            <a href="{{ route('portals.admin.legal.closures') }}" class="btn btn-secondary btn-sm">Account Closures</a>
            <a href="{{ route('portals.admin.legal.complaints') }}" class="btn btn-secondary btn-sm">Privacy Complaints</a>
            <a href="{{ route('portals.admin.legal.minor_transitions') }}" class="btn btn-secondary btn-sm">Minor Transitions</a>
            <a href="{{ route('public.legal') }}" target="_blank" class="btn btn-secondary btn-sm">
                <i data-lucide="external-link"></i> Public View
            </a>
        </div>
    </div>
    <div class="panel-body panel-body--flush">
        <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>Title</th><th>Type</th><th>Language</th><th>Current Version</th><th>Status</th><th class="row-actions"></th></tr>
            </thead>
            <tbody>
                @forelse($documents as $doc)
                    @php $ver = $doc->versions->first(); @endphp
                    <tr>
                        <td data-label="Title" class="td-strong">{{ $doc->title }}</td>
                        <td data-label="Type">
                            <span class="badge badge-primary badge-sm">{{ str_replace('_', ' ', ucfirst($doc->document_type)) }}</span>
                        </td>
                        <td data-label="Language">{{ strtoupper($doc->language) }}</td>
                        <td data-label="Current Version">
                            @if($ver)
                                <span class="mono kv-strong">v{{ $ver->version }}</span>
                                @if($ver->requires_reacceptance)
                                    <span class="badge badge-warning badge-sm">Re-accept req.</span>
                                @endif
                            @else
                                <span class="td-muted">No version yet</span>
                            @endif
                        </td>
                        <td data-label="Status">
                            <span class="badge {{ $doc->is_active ? 'badge-success' : 'badge-neutral' }} badge-sm">
                                {{ $doc->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="row-actions">
                            <a href="{{ route('portals.admin.legal.show', $doc) }}" class="btn btn-secondary btn-sm">Manage</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="td-muted empty-cell">
                        No legal documents yet. Add your Terms, Privacy Policy, and Consent Policy to get started.
                    </td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>

{{-- New Document Modal --}}
<div id="newDocModal" class="modal-fixed">
    <div class="modal-fixed__panel modal-fixed__panel--lg">
        <div class="modal-fixed__head">
            <h3 class="modal-fixed__title">New legal document</h3>
            <button type="button" class="icon-btn" aria-label="Close" onclick="opCloseModal('newDocModal')"><i data-lucide="x"></i></button>
        </div>
        <form method="POST" action="{{ route('portals.admin.legal.store') }}">
            @csrf
            <div class="form-group mb-3">
                <label class="form-label form-label-required">Slug</label>
                <input type="text" name="slug" class="form-control" placeholder="terms-of-use" required>
            </div>
            <div class="form-group mb-3">
                <label class="form-label form-label-required">Title</label>
                <input type="text" name="title" class="form-control" placeholder="Terms of Use" required>
            </div>
            <div class="form-group mb-4">
                <label class="form-label form-label-required">Type</label>
                <select name="document_type" class="form-control" required>
                    <option value="terms">Terms of Use</option>
                    <option value="privacy">Privacy Policy</option>
                    <option value="consent">Patient Consent Policy</option>
                    <option value="dpa">Data Processing Agreement</option>
                    <option value="facility_agreement">Facility Agreement</option>
                    <option value="api_terms">API / Developer Terms</option>
                </select>
            </div>
            <div class="modal__footer">
                <button type="button" onclick="opCloseModal('newDocModal')" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Document</button>
            </div>
        </form>
    </div>
</div>

@endsection
@section('scripts')
<script>
function opOpenModal(id){ document.getElementById(id).classList.add('open'); }
function opCloseModal(id){ document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-fixed').forEach(function(m){
    m.addEventListener('click', function(e){ if(e.target===m) m.classList.remove('open'); });
});
document.addEventListener('keydown', function(e){
    if(e.key==='Escape'){ document.querySelectorAll('.modal-fixed').forEach(function(m){ m.classList.remove('open'); }); }
});
</script>
@endsection
