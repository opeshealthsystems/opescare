@extends('layouts.portal')

@section('title', __('public.staff_portal.page_heading_consult', [], app()->getLocale()) ?: 'Consultation')

@section('sidebar_role_badge')
<div class="sidebar-role-badge">{{ __('public.staff_portal.role_clinical_staff', [], app()->getLocale()) ?: 'Clinical Staff' }}</div>
@endsection
@section('sidebar_user_role', __('public.staff_portal.role_clinical_staff', [], app()->getLocale()) ?: 'Clinical Staff')

@section('sidebar_nav')
@endif

<div class="grid-main-side">

    {{-- Consultation Form --}}
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">
                <i data-lucide="file-pen"></i>
                {{ __('public.staff_portal.panel_clinical_note', [], $l) ?: 'Clinical Note' }}
            </h2>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('portals.staff.visits.consult.store', $visit->id) }}">
                @csrf
                <div class="form-group mb-4">
                    <label class="form-label form-label-required">{{ __('public.staff_portal.field_history_hpi', [], $l) ?: 'History of Present Illness' }} *</label>
                    <textarea name="history_of_present_illness" class="form-control" rows="5"
                        required minlength="10" maxlength="5000"
                        placeholder="{{ __('public.staff_portal.ph_history_hpi', [], $l) ?: 'Describe the presenting complaint, onset, duration, character, associated symptoms…' }}">{{ old('history_of_present_illness') }}</textarea>
                </div>
                <div class="form-group mb-4">
                    <label class="form-label">{{ __('public.staff_portal.field_examination', [], $l) ?: 'Examination Findings' }}</label>
                    <textarea name="examination_findings" class="form-control" rows="4"
                        maxlength="5000"
                        placeholder="{{ __('public.staff_portal.ph_examination', [], $l) ?: 'Physical examination findings, system review…' }}">{{ old('examination_findings') }}</textarea>
                </div>
                <div class="form-group mb-4">
                    <label class="form-label">{{ __('public.staff_portal.field_treatment_plan', [], $l) ?: 'Treatment Plan / Assessment' }}</label>
                    <textarea name="treatment_plan" class="form-control" rows="4"
                        maxlength="5000"
                        placeholder="{{ __('public.staff_portal.ph_treatment_plan', [], $l) ?: 'Diagnosis, management plan, prescriptions, referrals, follow-up instructions…' }}">{{ old('treatment_plan') }}</textarea>
                </div>
                <div class="form-group mb-6">
                    <label class="form-label">{{ __('public.staff_portal.field_note_status', [], $l) ?: 'Note Status' }}</label>
                    <select name="status" class="form-control">
                        <option value="draft">{{ __('public.staff_portal.opt_save_draft', [], $l) ?: 'Save as Draft' }}</option>
                        <option value="signed">{{ __('public.staff_portal.opt_sign_finalize', [], $l) ?: 'Sign & Finalize' }}</option>
                    </select>
                    <span class="form-hint">{{ __('public.staff_portal.hint_signed_notes', [], $l) ?: 'Signed notes cannot be edited — only amended.' }}</span>
                </div>
                <div class="row-actions">
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="file-pen"></i>
                        {{ __('public.staff_portal.btn_save_note', [], $l) ?: 'Save Note' }}
                    </button>
                    <a href="{{ route('portals.staff.visits') }}" class="btn btn-ghost">{{ __('public.staff_portal.filter_clear', [], $l) ?: 'Cancel' }}</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Sidebar: Previous notes + triage summary --}}
    <div>
        {{-- Triage Summary --}}
        @if($visit->triageRecords->isNotEmpty())
        <div class="panel mb-6">
            <div class="panel-header">
                <h2 class="panel-title">
                    <i data-lucide="activity"></i>
                    {{ __('public.staff_portal.panel_triage_summary', [], $l) ?: 'Triage Summary' }}
                </h2>
            </div>
            <div class="panel-body panel-body--flush">
                @php $triage = $visit->triageRecords->sortByDesc('created_at')->first(); @endphp
                <table class="kv-table">
                    <tr><td class="kv-strong">{{ __('public.staff_portal.lbl_complaint', [], $l) ?: 'Complaint' }}</td><td>{{ $triage->presenting_complaint ?? '--' }}</td></tr>
                    <tr><td class="kv-strong">{{ __('public.staff_portal.col_acuity', [], $l) ?: 'Acuity' }}</td><td>@enum($triage->acuity_score ?? '--')</td></tr>
                    <tr><td class="kv-strong">{{ __('public.staff_portal.lbl_pain', [], $l) ?: 'Pain' }}</td><td>{{ $triage->pain_score !== null ? $triage->pain_score . '/10' : '--' }}</td></tr>
                    @if($triage->vitalSigns->isNotEmpty())
                        @php $v = $triage->vitalSigns->first(); @endphp
                        <tr><td class="kv-strong">T</td><td>{{ $v->temperature ?? '--' }}°C</td></tr>
                        <tr><td class="kv-strong">BP</td><td>{{ $v->blood_pressure_systolic ?? '--' }}/{{ $v->blood_pressure_diastolic ?? '--' }} mmHg</td></tr>
                        <tr><td class="kv-strong">{{ __('public.staff_portal.field_pulse', [], $l) ?: 'Pulse' }}</td><td>{{ $v->pulse ?? '--' }} bpm</td></tr>
                        <tr><td class="kv-strong">SpO₂</td><td>{{ $v->oxygen_saturation ?? '--' }}%</td></tr>
                        @if($v->weight)<tr><td class="kv-strong">{{ __('public.staff_portal.lbl_weight', [], $l) ?: 'Weight' }}</td><td>{{ $v->weight }} kg</td></tr>@endif
                    @endif
                </table>
            </div>
        </div>
        @endif

        {{-- Previous Notes --}}
        @if($visit->clinicalNotes->isNotEmpty())
        <div class="panel">
            <div class="panel-header">
                <h2 class="panel-title">
                    <i data-lucide="notebook-text"></i>
                    {{ __('public.staff_portal.panel_prev_notes', [], $l) ?: 'Previous Notes' }} ({{ $visit->clinicalNotes->count() }})
                </h2>
            </div>
            <div class="panel-body panel-body--flush">
                <div class="table-wrapper">
                    <table class="data-table">
                        <tbody>
                        @foreach($visit->clinicalNotes->sortByDesc('created_at') as $note)
                        <tr>
                            <td data-label="{{ __('public.staff_portal.col_date', [], $l) ?: 'Date' }}">{{ \Carbon\Carbon::parse($note->created_at)->format('M d, Y H:i') }}</td>
                            <td data-label="{{ __('public.staff_portal.col_status', [], $l) ?: 'Status' }}">
                                <span class="badge {{ $note->status === 'signed' ? 'badge-success' : 'badge-neutral' }}">@enum($note->status)</span>
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_summary', [], $l) ?: 'Summary' }}" class="td-muted">{{ Str::limit($note->history_of_present_illness ?? '', 120) }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

</div>

@endsection
