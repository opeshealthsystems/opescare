@extends('layouts.portal')

@section('title', __('public.stf_visits_index_page_title'))

@section('sidebar_role_badge')
<div class="sidebar-role-badge">{{ __('public.staff_portal.role_clinical_staff', [], app()->getLocale()) ?: 'Clinical Staff' }}</div>
@endsection
@section('sidebar_user_role', __('public.staff_portal.role_clinical_staff', [], app()->getLocale()) ?: 'Clinical Staff')

@section('sidebar_nav')
@endif
@if(session('error'))
    <div class="alert alert-danger mb-4">
        <i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div>
    </div>
@endif

{{-- Filters --}}
<form method="GET" action="{{ route('portals.staff.visits') }}" class="filter-bar">
    <select name="status" class="filter-select">
        <option value="">{{ __('public.stf_visits_filter_status_all') }}</option>
        @foreach(['open','in_triage','in_consultation','awaiting_lab','awaiting_pharmacy','awaiting_billing','awaiting_discharge','completed','cancelled'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
        @endforeach
    </select>
    <input type="text" name="patient_id" class="filter-search" placeholder="{{ __('public.stf_visits_filter_ph_patient_id') }}" value="{{ request('patient_id') }}">
    <button type="submit" class="btn btn-primary btn-sm">
        <i data-lucide="filter"></i> {{ __('public.stf_visits_filter_btn_filter') }}
    </button>
    <a href="{{ route('portals.staff.visits') }}" class="btn btn-ghost btn-sm">{{ __('public.stf_visits_filter_btn_clear') }}</a>
</form>

<div class="panel">
    <div class="panel-body panel-body--flush">
        @if(count($visits) === 0)
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="stethoscope"></i></div>
                <h3>{{ __('public.stf_visits_empty_h3') }}</h3>
                <p>{{ __('public.stf_visits_empty_p') }}</p>
                <button type="button" class="btn btn-primary btn-sm mt-3" onclick="openVisitModal()">
                    {{ __('public.stf_visits_empty_btn_new') }}
                </button>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('public.stf_visits_col_visit_id_hdr') }}</th>
                            <th>{{ __('public.stf_visits_col_patient_hdr') }}</th>
                            <th>{{ __('public.stf_visits_col_type_hdr') }}</th>
                            <th>{{ __('public.stf_visits_col_status_hdr') }}</th>
                            <th>{{ __('public.stf_visits_col_started_hdr') }}</th>
                            <th>{{ __('public.stf_visits_col_duration_hdr') }}</th>
                            <th>{{ __('public.stf_visits_col_actions_hdr') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($visits as $visit)
                        @php
                            $statusBadge = match($visit->status) {
                                'open'                => 'badge-neutral',
                                'in_triage'           => 'badge-warning',
                                'in_consultation'     => 'badge-primary',
                                'awaiting_lab'        => 'badge-teal',
                                'awaiting_pharmacy'   => 'badge-teal',
                                'awaiting_billing'    => 'badge-warning',
                                'awaiting_discharge'  => 'badge-primary',
                                'completed'           => 'badge-success',
                                'cancelled'           => 'badge-danger',
                                default               => 'badge-neutral',
                            };
                            $durationMin = \Carbon\Carbon::parse($visit->started_at)->diffInMinutes(now());
                        @endphp
                        <tr>
                            <td data-label="{{ __('public.stf_visits_col_visit_id_hdr') }}">
                                <span class="mono">{{ substr($visit->id, 0, 8) }}…</span>
                            </td>
                            <td data-label="{{ __('public.stf_visits_col_patient_hdr') }}">
                                <span class="mono">{{ $visit->patient?->health_id ?? $visit->patient_id }}</span>
                            </td>
                            <td data-label="{{ __('public.stf_visits_col_type_hdr') }}">
                                <span class="badge badge-neutral">@enum($visit->visit_type ?? '--')</span>
                            </td>
                            <td data-label="{{ __('public.stf_visits_col_status_hdr') }}">
                                <span class="badge {{ $statusBadge }}">@enum($visit->status)</span>
                            </td>
                            <td data-label="{{ __('public.stf_visits_col_started_hdr') }}">
                                {{ \Carbon\Carbon::parse($visit->started_at)->format('M d, H:i') }}
                            </td>
                            <td data-label="{{ __('public.stf_visits_col_duration_hdr') }}">
                                {{ $durationMin }} min
                            </td>
                            <td data-label="{{ __('public.stf_visits_col_actions_hdr') }}">
                                <div class="row-actions">
                                    {{-- Triage --}}
                                    @if(in_array($visit->status, ['open','in_triage']))
                                        <a href="{{ route('portals.staff.visits.triage', $visit->id) }}"
                                            class="btn btn-warning btn-xs">
                                            <i data-lucide="activity"></i>
                                            {{ __('public.stf_visits_btn_triage_lbl') }}
                                        </a>
                                    @endif

                                    {{-- Consult --}}
                                    @if(in_array($visit->status, ['open','in_triage','in_consultation','awaiting_lab']))
                                        <a href="{{ route('portals.staff.visits.consult', $visit->id) }}"
                                            class="btn btn-primary btn-xs">
                                            <i data-lucide="stethoscope"></i>
                                            {{ __('public.stf_visits_btn_consult_lbl') }}
                                        </a>
                                    @endif

                                    {{-- Status advance --}}
                                    @if(!in_array($visit->status, ['completed','cancelled']))
                                        <button type="button" class="btn btn-ghost btn-xs"
                                            onclick="openTransitionModal('{{ $visit->id }}', '{{ $visit->status }}')">
                                            <i data-lucide="arrow-right-circle"></i>
                                            {{ __('public.stf_visits_btn_advance_lbl') }}
                                        </button>
                                    @endif

                                    {{-- Complete --}}
                                    @if(!in_array($visit->status, ['completed','cancelled']))
                                        <form method="POST" action="{{ route('portals.staff.visits.complete', $visit->id) }}" class="inline-form">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-xs"
                                                onclick="return confirm('{{ __('public.stf_visits_confirm_complete_msg') }}')">
                                                <i data-lucide="check-check"></i>
                                                {{ __('public.stf_visits_btn_done_lbl') }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('portals.staff.visits.cancel', $visit->id) }}" class="inline-form">
                                            @csrf
                                            <button type="submit" class="btn btn-ghost btn-xs"
                                                onclick="return confirm('{{ __('public.stf_visits_confirm_cancel_msg') }}')">
                                                {{ __('public.stf_visits_btn_cancel_lbl') }}
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

{{-- New Visit Modal --}}
<div id="visit-modal" class="modal-backdrop" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="visit-modal-title">
        <h3 class="modal__title" id="visit-modal-title">{{ __('public.stf_visits_modal_title_new') }}</h3>
        <form method="POST" action="{{ route('portals.staff.visits.store') }}">
            @csrf
            <div class="modal__body">
                <div class="form-group">
                    <label class="form-label">{{ __('public.stf_visits_modal_lbl_patient') }}</label>
                    @if(count($patients) > 0)
                        <select name="patient_id" class="form-control" required>
                            <option value="">{{ __('public.stf_visits_modal_ph_select_pt') }}</option>
                            @foreach($patients as $p)
                                <option value="{{ $p->id }}">
                                    {{ $p->health_id ?? $p->id }} ({{ $p->first_name ?? '' }} {{ $p->last_name ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    @else
                        <input type="text" name="patient_id" class="form-control" required placeholder="{{ __('public.stf_visits_modal_ph_patient_id') }}">
                    @endif
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('public.stf_visits_modal_lbl_type') }}</label>
                    <select name="visit_type" class="form-control" required>
                        <option value="general">{{ __('public.stf_visits_modal_opt_general') }}</option>
                        <option value="followup">{{ __('public.stf_visits_modal_opt_followup') }}</option>
                        <option value="specialist">{{ __('public.stf_visits_modal_opt_specialist') }}</option>
                        <option value="emergency">{{ __('public.stf_visits_modal_opt_emergency') }}</option>
                        <option value="lab">{{ __('public.stf_visits_modal_opt_lab') }}</option>
                        <option value="pharmacy">{{ __('public.stf_visits_modal_opt_pharmacy') }}</option>
                    </select>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeVisitModal()">{{ __('public.stf_visits_modal_btn_cancel2') }}</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="stethoscope"></i>
                    {{ __('public.stf_visits_modal_btn_start2') }}
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Transition Modal --}}
<div id="transition-modal" class="modal-backdrop" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="transition-modal-title">
        <h3 class="modal__title" id="transition-modal-title">{{ __('public.stf_visits_modal_title_transition') }}</h3>
        <form id="transition-form" method="POST" action="">
            @csrf
            <div class="modal__body">
                <div class="form-group">
                    <label class="form-label">{{ __('public.stf_visits_modal_lbl_status') }}</label>
                    <select id="transition-status" name="status" class="form-control" required></select>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeTransitionModal()">{{ __('public.stf_visits_modal_btn_cancel3') }}</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="arrow-right-circle"></i>
                    {{ __('public.stf_visits_modal_btn_advance2') }}
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    var visitTransitions = {
        'open':               ['in_triage','in_consultation','awaiting_billing','awaiting_discharge'],
        'in_triage':          ['in_consultation','awaiting_billing','awaiting_discharge'],
        'in_consultation':    ['awaiting_lab','awaiting_billing','awaiting_pharmacy','awaiting_discharge'],
        'awaiting_lab':       ['in_consultation','awaiting_billing','awaiting_discharge'],
        'awaiting_pharmacy':  ['awaiting_billing','awaiting_discharge'],
        'awaiting_billing':   ['awaiting_discharge'],
        'awaiting_discharge': [],
    };

    function openVisitModal() { document.getElementById('visit-modal').removeAttribute('hidden'); }
    function closeVisitModal() { document.getElementById('visit-modal').setAttribute('hidden',''); }
    document.getElementById('visit-modal').addEventListener('click', function(e) {
        if (e.target === this) closeVisitModal();
    });

    function openTransitionModal(visitId, currentStatus) {
        var form = document.getElementById('transition-form');
        form.setAttribute('action', '{{ url("/portals/staff/visits") }}/' + visitId + '/transition');

        var select = document.getElementById('transition-status');
        select.innerHTML = '';

        var options = visitTransitions[currentStatus] || [];
        if (options.length === 0) {
            closeTransitionModal();
            return;
        }
        options.forEach(function(s) {
            var opt = document.createElement('option');
            opt.value = s;
            opt.textContent = s.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            select.appendChild(opt);
        });

        document.getElementById('transition-modal').removeAttribute('hidden');
    }
    function closeTransitionModal() { document.getElementById('transition-modal').setAttribute('hidden',''); }
    document.getElementById('transition-modal').addEventListener('click', function(e) {
        if (e.target === this) closeTransitionModal();
    });
</script>
@endsection
