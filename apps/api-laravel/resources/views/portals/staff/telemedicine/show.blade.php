@extends('layouts.portal')

@section('title', __('public.stf_tele_show_title'))

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
    <a href="{{ route('portals.staff.telemedicine.index') }}">{{ __('public.stf_tele_index_heading') }}</a>
    <i data-lucide="chevron-right"></i>
    <span>{{ __('public.stf_tele_show_breadcrumb') }}</span>
</div>

<div class="page-head">
    <h2>{{ __('public.stf_tele_show_heading') }}</h2>
    <span class="badge {{ $tcBadge }}">{{ $consultation->status }}</span>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.staff.telemedicine.index') }}" class="btn btn-ghost btn-sm">
        <i data-lucide="arrow-left"></i> {{ __('public.stf_tele_show_btn_back') }}
    </a>
</div>

{{-- Clinical disclaimer --}}
<div class="alert alert-info mb-4">
    <i data-lucide="info"></i>
    <div>
        <strong>{{ __('public.stf_tele_show_clinical_note_title') }}:</strong> {{ __('public.stf_tele_show_clinical_note_body') }}
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
        <div class="panel-header"><h3 class="panel-title">{{ __('public.stf_tele_show_details_title') }}</h3></div>
        <div class="panel-body panel-body--flush">
            <table class="kv-table">
                <tr>
                    <td class="kv-strong">{{ __('public.stf_tele_show_kv_patient') }}</td>
                    <td>
                        @if($consultation->patient)
                            {{ $consultation->patient->first_name }} {{ $consultation->patient->last_name }}
                        @else — @endif
                    </td>
                </tr>
                <tr><td class="kv-strong">{{ __('public.stf_tele_show_kv_scheduled_at') }}</td><td>{{ $consultation->scheduled_at ? $consultation->scheduled_at->format('d M Y H:i') : '—' }}</td></tr>
                <tr><td class="kv-strong">{{ __('public.stf_tele_show_kv_platform') }}</td><td>@enum($consultation->platform ?? 'own', 'platform')</td></tr>
                <tr><td class="kv-strong">{{ __('public.stf_tele_show_kv_duration') }}</td><td>{{ $consultation->durationMinutes() ? $consultation->durationMinutes() . ' min' : '—' }}</td></tr>
            </table>
        </div>
    </div>

    {{-- Consent --}}
    <div class="panel">
        <div class="panel-header"><h3 class="panel-title">{{ __('public.stf_tele_show_consent_title') }}</h3></div>
        <div class="panel-body">
            @if($consultation->consent && $consultation->consent->isValid())
                <div class="alert alert-success">
                    <i data-lucide="check-circle"></i>
                    <div>{{ __('public.stf_tele_show_consent_obtained') }} {{ $consultation->consent->consent_method }}
                    {{ __('public.stf_tele_show_consent_obtained_on') }} {{ $consultation->consent->consented_at?->format('d M Y H:i') }}</div>
                </div>
            @else
                <div class="alert alert-warning mb-3">
                    <i data-lucide="alert-triangle"></i>
                    <div>{{ __('public.stf_tele_show_consent_missing') }}</div>
                </div>
                @if(in_array($consultation->status, ['scheduled', 'waiting']))
                    <form action="{{ route('portals.staff.telemedicine.consent', $consultation->id) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">{{ __('public.stf_tele_show_consent_label') }}</label>
                            <select name="consent_method" class="form-control" required>
                                <option value="verbal">{{ __('public.stf_tele_show_consent_verbal') }}</option>
                                <option value="digital">{{ __('public.stf_tele_show_consent_digital') }}</option>
                                <option value="written">{{ __('public.stf_tele_show_consent_written') }}</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">{{ __('public.stf_tele_show_btn_record_consent') }}</button>
                    </form>
                @endif
            @endif
        </div>
    </div>
</div>

{{-- Actions --}}
@if(in_array($consultation->status, ['scheduled', 'waiting']))
<div class="panel mt-6">
    <div class="panel-header"><h3 class="panel-title">{{ __('public.stf_tele_show_actions_title') }}</h3></div>
    <div class="panel-body">
        <div class="row-actions">
            @if($consultation->consent && $consultation->consent->isValid())
                <form action="{{ route('portals.staff.telemedicine.start', $consultation->id) }}" method="POST" class="inline-form">
                    @csrf
                    <button type="submit" class="btn btn-success">{{ __('public.stf_tele_show_btn_start_call') }}</button>
                </form>
            @endif

            <button type="button" class="btn btn-danger" onclick="opOpenModal('cancel-modal')">
                {{ __('public.stf_tele_show_btn_cancel_consult') }}
            </button>
        </div>
    </div>
</div>
@endif

@if($consultation->status === 'active')
<div class="panel mt-6">
    <div class="panel-header"><h3 class="panel-title">{{ __('public.stf_tele_show_active_title') }}</h3></div>
    <div class="panel-body">
        <div class="alert alert-success mb-4"><i data-lucide="check-circle"></i><div>{{ __('public.stf_tele_show_call_in_progress') }}</div></div>
        <form action="{{ route('portals.staff.telemedicine.end', $consultation->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-danger">{{ __('public.stf_tele_show_btn_end_call') }}</button>
        </form>
    </div>
</div>
@endif

{{-- Notes --}}
@if($consultation->notes->isNotEmpty())
<div class="panel mt-6">
    <div class="panel-header"><h3 class="panel-title">{{ __('public.stf_tele_show_notes_title') }}</h3></div>
    <div class="panel-body">
        @foreach($consultation->notes as $note)
        <div class="mb-4">
            <div class="mb-1">
                <strong>@enum($note->note_type)</strong>
                @if($note->is_signed)
                    <span class="badge badge-success">{{ __('public.stf_tele_show_badge_signed') }}</span>
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
        <h3 class="modal__title" id="cancel-modal-title"><i data-lucide="x-circle"></i> {{ __('public.stf_tele_show_cancel_modal_title') }}</h3>
        <form action="{{ route('portals.staff.telemedicine.cancel', $consultation->id) }}" method="POST">
            @csrf
            <div class="modal__body">
                <div class="form-group">
                    <label class="form-label form-label-required">{{ __('public.stf_tele_show_cancel_reason_label') }}</label>
                    <textarea name="reason" class="form-control" rows="2" required></textarea>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" onclick="opCloseModal('cancel-modal')">{{ __('public.stf_tele_show_btn_keep') }}</button>
                <button type="submit" class="btn btn-danger">{{ __('public.stf_tele_show_btn_confirm_cancel') }}</button>
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
