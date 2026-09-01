@extends('layouts.portal')

@section('title', __('public.staff_portal.hr_shifts_title', [], app()->getLocale()) ?: 'Shift Definitions')

@section('sidebar_role_badge')
<div class="sidebar-role-badge">{{ __('public.staff_portal.role_clinical_staff', [], app()->getLocale()) ?: 'Clinical Staff' }}</div>
@endsection
@section('sidebar_user_role', __('public.staff_portal.role_clinical_staff', [], app()->getLocale()) ?: 'Clinical Staff')

@section('sidebar_nav')
@endif
@if(session('error'))
    <div class="auth-alert auth-alert-danger mb-4">
        <i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div>
    </div>
@endif

<div class="panel">
    <div class="panel-body panel-body--flush">
        @if($shifts->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="clock"></i></div>
                <h3>{{ __('public.staff_portal.hr_shifts_empty_title', [], app()->getLocale()) ?: 'No Shifts Defined' }}</h3>
                <p>{{ __('public.staff_portal.hr_shifts_empty_desc', [], app()->getLocale()) ?: 'Create shift templates like Morning, Afternoon, Night, or On-Call.' }}</p>
                <button type="button" class="btn btn-primary btn-sm mt-6" onclick="openShiftModal()">
                    {{ __('public.staff_portal.hr_shifts_btn_new', [], app()->getLocale()) ?: 'New Shift' }}
                </button>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('public.staff_portal.hr_shifts_col_name', [], app()->getLocale()) ?: 'Name' }}</th>
                            <th>{{ __('public.staff_portal.hr_shifts_col_dept', [], app()->getLocale()) ?: 'Department' }}</th>
                            <th>{{ __('public.staff_portal.hr_shifts_col_start', [], app()->getLocale()) ?: 'Start' }}</th>
                            <th>{{ __('public.staff_portal.hr_shifts_col_end', [], app()->getLocale()) ?: 'End' }}</th>
                            <th>{{ __('public.staff_portal.hr_shifts_col_duration', [], app()->getLocale()) ?: 'Duration' }}</th>
                            <th>{{ __('public.staff_portal.hr_shifts_col_midnight', [], app()->getLocale()) ?: 'Crosses Midnight' }}</th>
                            <th>{{ __('public.staff_portal.hr_shifts_col_status', [], app()->getLocale()) ?: 'Status' }}</th>
                            <th>{{ __('public.staff_portal.hr_shifts_col_actions', [], app()->getLocale()) ?: 'Actions' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($shifts as $shift)
                        <tr>
                            <td data-label="{{ __('public.staff_portal.hr_shifts_col_name', [], app()->getLocale()) ?: 'Name' }}"><strong class="td-strong">{{ $shift->name }}</strong></td>
                            <td data-label="{{ __('public.staff_portal.hr_shifts_col_dept', [], app()->getLocale()) ?: 'Department' }}">{{ $shift->department ?? '—' }}</td>
                            <td data-label="{{ __('public.staff_portal.hr_shifts_col_start', [], app()->getLocale()) ?: 'Start' }}">{{ $shift->start_time }}</td>
                            <td data-label="{{ __('public.staff_portal.hr_shifts_col_end', [], app()->getLocale()) ?: 'End' }}">{{ $shift->end_time }}</td>
                            <td data-label="{{ __('public.staff_portal.hr_shifts_col_duration', [], app()->getLocale()) ?: 'Duration' }}">{{ $shift->duration_hours ? $shift->duration_hours . 'h' : '—' }}</td>
                            <td data-label="{{ __('public.staff_portal.hr_shifts_col_midnight', [], app()->getLocale()) ?: 'Crosses Midnight' }}">
                                @if($shift->crosses_midnight)
                                    <span class="badge badge-warning">{{ __('public.staff_portal.hr_shifts_yes', [], app()->getLocale()) ?: 'Yes' }}</span>
                                @else
                                    <span class="badge badge-neutral">{{ __('public.staff_portal.hr_shifts_no', [], app()->getLocale()) ?: 'No' }}</span>
                                @endif
                            </td>
                            <td data-label="{{ __('public.staff_portal.hr_shifts_col_status', [], app()->getLocale()) ?: 'Status' }}">
                                <span class="badge {{ $shift->status === 'active' ? 'badge-success' : 'badge-neutral' }}">
                                    @enum($shift->status)
                                </span>
                            </td>
                            <td data-label="{{ __('public.staff_portal.hr_shifts_col_actions', [], app()->getLocale()) ?: 'Actions' }}">
                                <form method="POST" action="{{ route('portals.staff.hr.shifts.toggle', $shift->id) }}" class="inline-form">
                                    @csrf
                                    <button type="submit" class="btn btn-ghost btn-xs">
                                        <i data-lucide="{{ $shift->status === 'active' ? 'pause-circle' : 'play-circle' }}"></i>
                                        {{ $shift->status === 'active' ? (__('public.staff_portal.hr_shifts_deactivate', [], app()->getLocale()) ?: 'Deactivate') : (__('public.staff_portal.hr_shifts_activate', [], app()->getLocale()) ?: 'Activate') }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- New Shift Modal --}}
<div id="shift-modal" class="modal-fixed">
    <div class="modal-fixed__panel modal-fixed__panel--md">
        <div class="modal-fixed__head">
            <h3 class="modal-fixed__title">{{ __('public.staff_portal.hr_shifts_modal_title', [], app()->getLocale()) ?: 'New Shift' }}</h3>
        </div>
        <form method="POST" action="{{ route('portals.staff.hr.shifts.store') }}">
            @csrf
            <div class="form-group mb-4">
                <label class="form-label">{{ __('public.staff_portal.hr_shifts_lbl_name', [], app()->getLocale()) ?: 'Shift Name *' }}</label>
                <input type="text" name="name" class="form-control" required maxlength="100" placeholder="{{ __('public.staff_portal.hr_shifts_ph_name', [], app()->getLocale()) ?: 'e.g. Morning, Night, On-Call' }}">
            </div>
            <div class="form-group mb-4">
                <label class="form-label">{{ __('public.staff_portal.hr_shifts_lbl_dept', [], app()->getLocale()) ?: 'Department' }}</label>
                <input type="text" name="department" class="form-control" maxlength="100" placeholder="{{ __('public.staff_portal.hr_shifts_ph_dept', [], app()->getLocale()) ?: 'Leave blank for all departments' }}">
            </div>
            <div class="form-row mb-4">
                <div class="form-group">
                    <label class="form-label">{{ __('public.staff_portal.hr_shifts_lbl_start', [], app()->getLocale()) ?: 'Start Time *' }}</label>
                    <input type="time" name="start_time" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('public.staff_portal.hr_shifts_lbl_end', [], app()->getLocale()) ?: 'End Time *' }}</label>
                    <input type="time" name="end_time" class="form-control" required>
                </div>
            </div>
            <div class="form-row mb-4">
                <div class="form-group">
                    <label class="form-label">{{ __('public.staff_portal.hr_shifts_lbl_duration', [], app()->getLocale()) ?: 'Duration (hours)' }}</label>
                    <input type="number" name="duration_hours" class="form-control" min="1" max="24" placeholder="8">
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('public.staff_portal.hr_shifts_lbl_midnight', [], app()->getLocale()) ?: 'Crosses Midnight?' }}</label>
                    <select name="crosses_midnight" class="form-control">
                        <option value="0">{{ __('public.staff_portal.hr_shifts_no', [], app()->getLocale()) ?: 'No' }}</option>
                        <option value="1">{{ __('public.staff_portal.hr_shifts_yes', [], app()->getLocale()) ?: 'Yes' }}</option>
                    </select>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeShiftModal()">{{ __('public.staff_portal.hr_shifts_btn_cancel', [], app()->getLocale()) ?: 'Cancel' }}</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="clock"></i>
                    {{ __('public.staff_portal.hr_shifts_btn_create', [], app()->getLocale()) ?: 'Create Shift' }}
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openShiftModal()  { document.getElementById('shift-modal').classList.add('open'); }
    function closeShiftModal() { document.getElementById('shift-modal').classList.remove('open'); }
    document.getElementById('shift-modal').addEventListener('click', function(e) {
        if (e.target === this) closeShiftModal();
    });
</script>
@endsection
