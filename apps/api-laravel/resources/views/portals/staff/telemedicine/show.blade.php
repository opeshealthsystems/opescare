@extends('layouts.portal')

@section('title', 'Teleconsultation')

@section('content')
<div class="page-header">
    <div class="page-header__left">
        <a href="{{ route('portals.staff.telemedicine.index') }}" class="back-link">← Telemedicine</a>
        <h1 class="page-title">Teleconsultation</h1>
        <span class="{{ $consultation->statusBadgeClass() }} ml-2">{{ $consultation->status }}</span>
    </div>
</div>

{{-- Clinical disclaimer --}}
<div class="alert alert--info mb-4">
    <strong>Clinical Note:</strong> This platform facilitates the connection and records the encounter.
    All clinical decisions remain the provider's sole responsibility.
</div>

@if(session('success'))
    <div class="alert alert--success mb-4">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert--danger mb-4">{{ session('error') }}</div>
@endif

<div class="grid grid--2 gap-4">
    {{-- Consultation details --}}
    <div class="card">
        <div class="card__header"><h3 class="card__title">Consultation Details</h3></div>
        <div class="card__body">
            <dl class="detail-list">
                <dt>Patient</dt>
                <dd>
                    @if($consultation->patient)
                        {{ $consultation->patient->first_name }} {{ $consultation->patient->last_name }}
                    @else —
                    @endif
                </dd>
                <dt>Scheduled At</dt>
                <dd>{{ $consultation->scheduled_at ? $consultation->scheduled_at->format('d M Y H:i') : '—' }}</dd>
                <dt>Platform</dt>
                <dd>{{ ucfirst($consultation->platform ?? 'own') }}</dd>
                <dt>Duration</dt>
                <dd>{{ $consultation->durationMinutes() ? $consultation->durationMinutes() . ' min' : '—' }}</dd>
            </dl>
        </div>
    </div>

    {{-- Consent --}}
    <div class="card">
        <div class="card__header"><h3 class="card__title">Patient Consent</h3></div>
        <div class="card__body">
            @if($consultation->consent && $consultation->consent->isValid())
                <div class="alert alert--success">
                    Consent obtained via {{ $consultation->consent->consent_method }}
                    on {{ $consultation->consent->consented_at?->format('d M Y H:i') }}
                </div>
            @else
                <div class="alert alert--warning mb-3">
                    Consent not yet recorded. Obtain consent before starting the call.
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
                        <button type="submit" class="btn btn--primary btn--sm">Record Consent</button>
                    </form>
                @endif
            @endif
        </div>
    </div>
</div>

{{-- Actions --}}
@if(in_array($consultation->status, ['scheduled', 'waiting']))
<div class="card mt-4">
    <div class="card__header"><h3 class="card__title">Actions</h3></div>
    <div class="card__body flex gap-3">
        @if($consultation->consent && $consultation->consent->isValid())
            <form action="{{ route('portals.staff.telemedicine.start', $consultation->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn--success">▶ Start Call</button>
            </form>
        @endif

        <button class="btn btn--danger" onclick="document.getElementById('cancel-form').classList.toggle('hidden')">
            Cancel Consultation
        </button>
    </div>
    <div id="cancel-form" class="card__body pt-0 hidden">
        <form action="{{ route('portals.staff.telemedicine.cancel', $consultation->id) }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Cancellation Reason <span class="text-danger">*</span></label>
                <textarea name="reason" class="form-control" rows="2" required></textarea>
            </div>
            <button type="submit" class="btn btn--danger btn--sm">Confirm Cancellation</button>
        </form>
    </div>
</div>
@endif

@if($consultation->status === 'active')
<div class="card mt-4">
    <div class="card__header"><h3 class="card__title">Active Call</h3></div>
    <div class="card__body">
        <div class="alert alert--success mb-4">Call is in progress.</div>
        <form action="{{ route('portals.staff.telemedicine.end', $consultation->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn--danger">■ End Call</button>
        </form>
    </div>
</div>
@endif

{{-- Notes --}}
@if($consultation->notes->isNotEmpty())
<div class="card mt-4">
    <div class="card__header"><h3 class="card__title">Consultation Notes</h3></div>
    <div class="card__body">
        @foreach($consultation->notes as $note)
        <div class="note-block mb-4">
            <div class="note-block__header">
                <strong>{{ ucfirst($note->note_type) }}</strong>
                @if($note->is_signed)
                    <span class="badge badge--success ml-2">Signed</span>
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

<script>
function toggleSection(id) {
    document.getElementById(id).classList.toggle('hidden');
}
</script>
@endsection
