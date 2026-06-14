@extends('layouts.portal')
@section('title', $facility->name . ' — Onboarding')
@section('sidebar') @include('portals.admin.control_center._sidebar') @endsection

@section('content')

<div class="breadcrumb">
    <a href="{{ route('portals.admin.onboarding') }}">Facility Onboarding</a>
    <i data-lucide="chevron-right"></i>
    <span>{{ $facility->name }}</span>
</div>

@php
    $statusColor = match($readiness->status) {
        'approved' => 'success', 'ready_for_approval' => 'info', default => 'warning',
    };
@endphp

<div class="entity-head">
    <div class="entity-head__icon"><i data-lucide="rocket"></i></div>
    <div>
        <h2 class="entity-head__title">{{ $facility->name }}</h2>
        <div class="entity-head__sub">
            <span class="badge badge--{{ $statusColor }} badge-sm">{{ str_replace('_', ' ', ucfirst($readiness->status)) }}</span>
            <span class="td-muted text-sm">{{ $completedCount }}/{{ $totalCount }} items completed</span>
        </div>
    </div>
    <div class="entity-head__spacer"></div>
    @if($readiness->status === 'ready_for_approval' && !$readiness->can_go_live)
    <button onclick="opOpenModal('approveModal')" class="btn btn-primary btn-sm">
        <i data-lucide="check-circle"></i> Approve Go-Live
    </button>
    @elseif($readiness->status === 'approved')
    <span class="badge badge-success">
        <i data-lucide="check-circle-2"></i> Go-Live Approved
    </span>
    @endif
</div>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

{{-- Progress --}}
@php $pct = $totalCount > 0 ? round($completedCount / $totalCount * 100) : 0; @endphp
<div class="panel mb-6">
    <div class="panel-body">
        <div class="flex-between mb-3">
            <span class="kv-strong">Onboarding progress</span>
            <span class="kv-strong {{ $pct === 100 ? 'trend-up' : '' }}">{{ $pct }}%</span>
        </div>
        <div class="breakdown__track breakdown__track--lg">
            <div class="breakdown__fill {{ $pct === 100 ? 'breakdown__fill--teal' : '' }}" style="width: {{ $pct }}%"></div>
        </div>
        @if(!empty($missingItems))
        <p class="text-sm trend-down mt-3">Remaining: {{ implode(', ', array_values($missingItems)) }}</p>
        @endif
    </div>
</div>

{{-- Checklist --}}
<div class="panel">
    <div class="panel-header">
        <h3 class="panel-title"><i data-lucide="list-checks"></i> Go-live checklist</h3>
    </div>
    <div class="panel-body">
        <div class="checklist">
            @foreach($labels as $key => $label)
                @php $done = $checklist[$key] ?? false; @endphp
                <div class="checklist__item">
                    <span class="checklist__icon {{ $done ? 'ok' : '' }}">
                        <i data-lucide="{{ $done ? 'check-circle-2' : 'circle' }}"></i>
                    </span>
                    <div class="checklist__body">
                        <div class="checklist__title {{ $done ? 'checklist__title--done' : '' }}">{{ $label }}</div>
                    </div>
                    @if($readiness->status !== 'approved')
                    <form method="POST" action="{{ route('portals.admin.onboarding.mark', $facility) }}" class="inline-form">
                        @csrf
                        <input type="hidden" name="item" value="{{ $key }}">
                        <input type="hidden" name="complete" value="{{ $done ? '0' : '1' }}">
                        <button type="submit" class="btn btn-secondary btn-sm">
                            {{ $done ? 'Mark Incomplete' : 'Mark Complete' }}
                        </button>
                    </form>
                    @else
                    <span class="td-muted text-sm">Locked</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Approval info --}}
@if($readiness->approved_at)
<div class="panel mt-6">
    <div class="panel-body">
        <div class="section-head"><i data-lucide="check-circle-2" class="text-success"></i><h2 class="trend-up">Go-live approved</h2></div>
        <table class="kv-table">
            <tr><td>Approved on</td><td class="kv-strong">{{ $readiness->approved_at->format('d F Y H:i') }}</td></tr>
            @if($readiness->approval_note)
            <tr><td>Note</td><td class="kv-strong">{{ $readiness->approval_note }}</td></tr>
            @endif
        </table>
    </div>
</div>
@endif

{{-- Risks --}}
@if(!empty($risks))
<div class="alert alert-warning mt-6">
    <i data-lucide="alert-triangle"></i>
    <div>
        <strong>Risks:</strong>
        <ul class="alert-list">
            @foreach($risks as $risk)
            <li>{{ $risk }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

{{-- Approve Go-Live Modal --}}
@if($readiness->status !== 'approved')
<div id="approveModal" class="modal-fixed">
    <div class="modal-fixed__panel modal-fixed__panel--lg">
        <div class="modal-fixed__head">
            <h3 class="modal-fixed__title">Approve go-live</h3>
            <button type="button" class="icon-btn" aria-label="Close" onclick="opCloseModal('approveModal')"><i data-lucide="x"></i></button>
        </div>
        <p class="td-muted text-sm mb-4">
            Approving go-live for <strong>{{ $facility->name }}</strong> marks this facility as ready for live patient operations.
            Ensure all checklist items are completed before approving.
        </p>
        <form method="POST" action="{{ route('portals.admin.onboarding.approve', $facility) }}">
            @csrf
            <div class="form-group mb-4">
                <label class="form-label form-label-required">Approval Note</label>
                <textarea name="approval_note" rows="4" required class="form-control"
                          placeholder="Describe the approval basis, any conditions, and the approving authority..."></textarea>
            </div>
            <div class="modal__footer">
                <button type="button" onclick="opCloseModal('approveModal')" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary">Confirm Approval</button>
            </div>
        </form>
    </div>
</div>
@endif

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
