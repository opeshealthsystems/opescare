@extends('layouts.portal')

@section('title', 'Teleconsultation')

@section('content')
@php
    $tcBadge = match($consultation->status) {
        'scheduled' => 'badge-info',
        'waiting'   => 'badge-warning',
        'active'    => 'badge-success',
        'completed' => 'badge-neutral',
        'cancelled', 'failed' => 'badge-danger',
        default     => 'badge-neutral',
    };
@endphp

<div class="breadcrumb">
    <a href="{{ route('portals.staff.telemedicine.index') }}">Telemedicine</a>
    <i data-lucide="chevron-right"></i>
    <span>Teleconsultation</span>
</div>

<div class="page-head">
    <h2>Teleconsultation</h2>
    <span class="badge {{ $tcBadge }}">{{ $consultation->status }}</span>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.staff.telemedicine.index') }}" class="btn btn-ghost btn-sm">
        <i data-lucide="arrow-left"></i> Telemedicine
    </a>
</div>

{{-- Clinical disclaimer --}}
<div class="alert alert-info mb-4">
    <i data-lucide="info"></i>
    <div>
        <strong>Clinical Note:</strong> This platform facilitates the connection and records the encounter.
        All clinical decisions remain the provider's sole responsibility.
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-4"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>
@endif
@if(session('error'))
    <div class="alert alert-danger mb-4"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>
@endif

<div class="grid-2">
    {{-- Consultation details --}}
    <div class="panel">
        <div class="panel-header"><h3 class="panel-title">Consultation Details</h3></div>
        <div class="panel-body panel-body--flush">
            <table class="kv-table">
                <tr>
                    <td class="kv-strong">Patient</td>
                    <td>
                        @if($consultation->patient)
                            {{ $consultation->patient->first_name }} {{ $consultation->patient->last_name }}
                        @else — @endif
                    </td>
                </tr>
                <tr><td class="kv-strong">Scheduled At</td><td>{{ $consultation->scheduled_at ? $consultation->scheduled_at->format('d M Y H:i') : '—' }}</td></tr>
                <tr><td class="kv-strong">Platform</td><td>{{ ucfirst($consultation->platform ?? 'own') }}</td></tr>
                <tr><td class="kv-strong">Duration</td><td>{{ $consultation->durationMinutes() ? $consultation->durationMinutes() . ' min' : '—' }}</td></tr>
            </table>
        </div>
    </div>

    {{-- Consent --}}
    <div class="panel">
        <div class="panel-header"><h3 class="panel-title">Patient Consent</h3></div>
        <div class="panel-body">
            @if($consultation->consent && $consultation->consent->isValid())
                <div class="alert alert-success">
                    <i data-lucide="check-circle"></i>
                    <div>Consent obtained via {{ $consultation->consent->consent_method }}
                    on {{ $consultation->consent->consented_at?->format('d M Y H:i') }}</div>
                </div>
            @else
                <div class="alert alert-warning mb-3">
                    <i data-lucide="alert-triangle"></i>
                    <div>Consent not yet recorded. Obtain consent before starting the call.</div>
                </div>
                @if(in_array($consultation->status, ['scheduled', 'waiting']))
                    <form action="{{ route('portals.staff.telemedicine.consent', $consultation->id) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Consent Method</label>
                            <select name="consent_method" class="form-control" required>
                                <option value="verbal">Verbal</option>
                                <option value="digital">Digital (patient confirmed online)</option>
                                <option value="written">Written</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Record Consent</button>
                    </form>
                @endif
            @endif
        </div>
    </div>
</div>

{{-- Actions --}}
@if(in_array($consultation->status, ['scheduled', 'waiting']))
<div class="panel mt-6">
    <div class="panel-header"><h3 class="panel-title">Actions</h3></div>
    <div class="panel-body">
        <div class="row-actions">
            @if($consultation->consent && $consultation->consent->isValid())
                <form action="{{ route('portals.staff.telemedicine.start', $consultation->id) }}" method="POST" class="inline-form">
                    @csrf
                    <button type="submit" class="btn btn-success">▶ Start Call</button>
                </form>
            @endif

            <button type="button" class="btn btn-danger" onclick="opOpenModal('cancel-modal')">
                Cancel Consultation
            </button>
        </div>
    </div>
</div>
@endif

@if($consultation->status === 'active')
<div class="panel mt-6">
    <div class="panel-header"><h3 class="panel-title">Active Call</h3></div>
    <div class="panel-body">
        <div class="alert alert-success mb-4"><i data-lucide="check-circle"></i><div>Call is in progress.</div></div>
        <form action="{{ route('portals.staff.telemedicine.end', $consultation->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-danger">■ End Call</button>
        </form>
    </div>
</div>
@endif

{{-- Notes --}}
@if($consultation->notes->isNotEmpty())
<div class="panel mt-6">
    <div class="panel-header"><h3 class="panel-title">Consultation Notes</h3></div>
    <div class="panel-body">
        @foreach($consultation->notes as $note)
        <div class="mb-4">
            <div class="mb-1">
                <strong>{{ ucfirst($note->note_type) }}</strong>
                @if($note->is_signed)
                    <span class="badge badge-success">Signed</span>
                @endif
            </div>
            @if($note->subjective)
                <p><strong>S:</strong> {{ $note->subjective }}</p>
            @endif
            @if($note->assessment)
                <p><strong>A:</strong> {{ $note->assessment }}</p>
            @endif
            @if($note->plan)
                <p><strong>P:</strong> {{ $note->plan }}</p>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Cancel confirm modal --}}
@if(in_array($consultation->status, ['scheduled', 'waiting']))
<div id="cancel-modal" class="modal-backdrop" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="cancel-modal-title">
        <h3 class="modal__title" id="cancel-modal-title"><i data-lucide="x-circle"></i> Cancel consultation</h3>
        <form action="{{ route('portals.staff.telemedicine.cancel', $consultation->id) }}" method="POST">
            @csrf
            <div class="modal__body">
                <div class="form-group">
                    <label class="form-label form-label-required">Cancellation Reason *</label>
                    <textarea name="reason" class="form-control" rows="2" required></textarea>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" onclick="opCloseModal('cancel-modal')">Keep consultation</button>
                <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
            </div>
        </form>
    </div>
</div>
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
