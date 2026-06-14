@extends('layouts.portal')
@section('title', $document->title)
@section('sidebar') @include('portals.admin.control_center._sidebar') @endsection

@section('content')

<div class="breadcrumb">
    <a href="{{ route('portals.admin.legal') }}">Legal Documents</a>
    <i data-lucide="chevron-right"></i>
    <span>{{ $document->title }}</span>
</div>

<div class="entity-head">
    <div class="entity-head__icon"><i data-lucide="scale"></i></div>
    <div>
        <h2 class="entity-head__title">{{ $document->title }}</h2>
        <div class="entity-head__sub">
            <span class="badge badge-primary badge-sm">{{ str_replace('_', ' ', ucfirst($document->document_type)) }}</span>
            <span class="td-muted text-sm">{{ strtoupper($document->language) }}</span>
        </div>
    </div>
    <div class="entity-head__spacer"></div>
    <button type="button" onclick="opOpenModal('publishModal')" class="btn btn-primary btn-sm">
        <i data-lucide="upload"></i> Publish New Version
    </button>
</div>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

{{-- Versions table --}}
<div class="panel mb-6">
    <div class="panel-header"><h3 class="panel-title"><i data-lucide="layers"></i> Versions</h3></div>
    <div class="panel-body panel-body--flush">
        <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>Version</th><th>Status</th><th>Re-accept?</th><th>Published</th><th>Effective</th><th>Change Summary</th></tr>
            </thead>
            <tbody>
                @forelse($versions as $ver)
                    <tr class="{{ $ver->is_current ? 'row-emergency' : '' }}">
                        <td data-label="Version">
                            <span class="mono kv-strong">v{{ $ver->version }}</span>
                            @if($ver->is_current)
                                <span class="badge badge-success badge-sm">Current</span>
                            @endif
                        </td>
                        <td data-label="Status">
                            <span class="badge {{ $ver->isEffective() ? 'badge-success' : 'badge-warning' }} badge-sm">
                                {{ $ver->isEffective() ? 'Live' : 'Scheduled' }}
                            </span>
                        </td>
                        <td data-label="Re-accept?">
                            @if($ver->requires_reacceptance)
                                <span class="badge badge-danger badge-sm">Yes</span>
                            @else
                                <span class="td-muted">No</span>
                            @endif
                        </td>
                        <td data-label="Published" class="td-muted">
                            {{ $ver->published_at?->format('d M Y H:i') ?? '—' }}
                        </td>
                        <td data-label="Effective" class="td-muted">
                            {{ $ver->effective_at?->format('d M Y') ?? '—' }}
                        </td>
                        <td data-label="Change Summary">{{ $ver->change_summary ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="td-muted empty-cell">No versions published yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>

{{-- Current version preview --}}
@php $current = $versions->where('is_current', true)->first(); @endphp
@if($current)
<div class="panel">
    <div class="panel-header">
        <h3 class="panel-title"><i data-lucide="file-text"></i> Current content (v{{ $current->version }})</h3>
        <a href="{{ route('public.legal.show', $document->slug) }}" target="_blank" class="btn btn-secondary btn-sm">
            <i data-lucide="external-link"></i> Public View
        </a>
    </div>
    <div class="panel-body">
        <div class="legal-preview">
            {!! $current->content_html !!}
        </div>
    </div>
</div>
@endif

{{-- Publish New Version Modal --}}
<div id="publishModal" class="modal-fixed">
    <div class="modal-fixed__panel">
        <div class="modal-fixed__head">
            <h3 class="modal-fixed__title">Publish new version — {{ $document->title }}</h3>
            <button type="button" class="icon-btn" aria-label="Close" onclick="opCloseModal('publishModal')"><i data-lucide="x"></i></button>
        </div>
        <form method="POST" action="{{ route('portals.admin.legal.publish_version', $document) }}">
            @csrf
            <div class="form-row mb-3">
                <div class="form-group">
                    <label class="form-label form-label-required">Version</label>
                    <input type="text" name="version" class="form-control" placeholder="1.0" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Effective Date</label>
                    <input type="date" name="effective_at" class="form-control">
                </div>
            </div>
            <div class="form-group mb-3">
                <label class="form-label">Change Summary</label>
                <input type="text" name="change_summary" class="form-control" placeholder="What changed in this version?">
            </div>
            <div class="form-group mb-3">
                <label class="form-label form-label-required">Content (HTML)</label>
                <textarea name="content_html" rows="12" class="form-control mono" required placeholder="<h1>Terms of Use</h1><p>...</p>"></textarea>
            </div>
            <div class="form-group mb-4">
                <label class="form-check">
                    <input type="checkbox" id="reaccept" name="requires_reacceptance" value="1">
                    Require all users to re-accept this new version
                </label>
            </div>
            <div class="modal__footer">
                <button type="button" onclick="opCloseModal('publishModal')" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary">Publish Version</button>
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
