@extends('layouts.portal')

@section('title', __('staff_hr.title_leave', [], app()->getLocale()) ?: 'Leave Management')

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

{{-- Filters --}}
<form method="GET" action="{{ route('portals.staff.hr.leave') }}" class="filter-bar">
    <select name="status" class="form-control">
        <option value="">{{ __('public.stf_hr_leave_all_statuses') }}</option>
        @foreach(['pending','approved','rejected','withdrawn','cancelled'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>@enum($s)</option>
        @endforeach
    </select>
    <select name="leave_type" class="form-control">
        <option value="">{{ __('public.stf_hr_leave_all_types') }}</option>
        @foreach(['annual','sick','emergency','maternity','paternity','study','unpaid'] as $t)
            <option value="{{ $t }}" {{ request('leave_type') === $t ? 'selected' : '' }}>@enum($t, 'leave_type')</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-primary btn-sm">
        <i data-lucide="filter"></i> {{ __('public.stf_hr_leave_filter_btn') }}
    </button>
    <a href="{{ route('portals.staff.hr.leave') }}" class="btn btn-ghost btn-sm">{{ __('public.stf_hr_leave_clear_btn') }}</a>
</form>

<div class="panel">
    <div class="panel-body panel-body--flush">
        @if($requests->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="plane-takeoff"></i></div>
                <h3>{{ __('public.stf_hr_leave_empty_title') }}</h3>
                <p>{{ __('public.stf_hr_leave_empty_desc') }}</p>
                <button type="button" class="btn btn-primary btn-sm mt-6" onclick="openLeaveModal()">{{ __('public.stf_hr_leave_new_btn') }}</button>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('public.stf_hr_leave_col_staff') }}</th>
                            <th>{{ __('public.stf_hr_leave_col_type') }}</th>
                            <th>{{ __('public.stf_hr_leave_col_period') }}</th>
                            <th>{{ __('public.stf_hr_leave_col_days') }}</th>
                            <th>{{ __('public.stf_hr_leave_col_status') }}</th>
                            <th>{{ __('public.stf_hr_leave_col_reviewed_by') }}</th>
                            <th>{{ __('public.stf_hr_leave_col_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $req)
                        @php
                            $lBadge = match($req->status) {
                                'pending'   => 'badge-warning',
                                'approved'  => 'badge-success',
                                'rejected'  => 'badge-danger',
                                'withdrawn' => 'badge-neutral',
                                'cancelled' => 'badge-neutral',
                                default     => 'badge-neutral',
                            };
                        @endphp
                        <tr>
                            <td data-label="{{ __('public.stf_hr_leave_col_staff') }}">
                                <strong class="td-strong">{{ $req->staffProfile?->full_name ?? '—' }}</strong>
                            </td>
                            <td data-label="{{ __('public.stf_hr_leave_col_type') }}">
                                <span class="badge badge-neutral">@enum($req->leave_type)</span>
                            </td>
                            <td data-label="{{ __('public.stf_hr_leave_col_period') }}">
                                {{ \Carbon\Carbon::parse($req->start_date)->format('M d') }} –
                                {{ \Carbon\Carbon::parse($req->end_date)->format('M d, Y') }}
                            </td>
                            <td data-label="{{ __('public.stf_hr_leave_col_days') }}">{{ $req->days_requested ?? '—' }}</td>
                            <td data-label="{{ __('public.stf_hr_leave_col_status') }}">
                                <span class="badge {{ $lBadge }}">@enum($req->status)</span>
                            </td>
                            <td data-label="{{ __('public.stf_hr_leave_col_reviewed_by') }}">
                                <span class="td-muted">{{ $req->reviewed_by ?? '—' }}</span>
                            </td>
                            <td data-label="{{ __('public.stf_hr_leave_col_actions') }}">
                                <div class="row-actions-inline">
                                    @if($req->status === 'pending')
                                        <button type="button" class="btn btn-success btn-xs"
                                            onclick="openReviewModal('{{ $req->id }}', 'approve')">
                                            <i data-lucide="check"></i>
                                            {{ __('public.stf_hr_leave_btn_approve') }}
                                        </button>
                                        <button type="button" class="btn btn-ghost btn-xs"
                                            onclick="openReviewModal('{{ $req->id }}', 'reject')">
                                            <i data-lucide="x"></i>
                                            {{ __('public.stf_hr_leave_btn_reject') }}
                                        </button>
                                    @endif
                                    @if(in_array($req->status, ['pending','approved']))
                                        <button type="button" class="btn btn-ghost btn-xs"
                                            onclick="openWithdrawModal('{{ route('portals.staff.hr.leave.withdraw', $req->id) }}')">
                                            <i data-lucide="undo-2"></i>
                                            {{ __('public.stf_hr_leave_btn_withdraw') }}
                                        </button>
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

{{-- New Leave Request Modal --}}
<div id="leave-modal" class="modal-fixed">
    <div class="modal-fixed__panel modal-fixed__panel--md">
        <div class="modal-fixed__head">
            <h3 class="modal-fixed__title">{{ __('public.stf_hr_leave_modal_title') }}</h3>
        </div>
        <form method="POST" action="{{ route('portals.staff.hr.leave.store') }}">
            @csrf
            <div class="form-group mb-4">
                <label class="form-label">{{ __('public.stf_hr_leave_lbl_staff') }}</label>
                <select name="staff_profile_id" class="form-control" required>
                    <option value="">{{ __('staff_hr.select_placeholder', [], app()->getLocale()) ?: '— Select —' }}</option>
                    @foreach($staff as $member)
                        <option value="{{ $member->id }}">{{ $member->full_name }} ({{ $member->job_title ?? $member->staff_category }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mb-4">
                <label class="form-label">{{ __('public.stf_hr_leave_lbl_type') }}</label>
                <select name="leave_type" class="form-control" required>
                    @foreach(['annual','sick','emergency','maternity','paternity','study','unpaid'] as $t)
                        <option value="{{ $t }}">@enum($t, 'leave_type')</option>
                    @endforeach
                </select>
            </div>
            <div class="form-row mb-4">
                <div class="form-group">
                    <label class="form-label">{{ __('public.stf_hr_leave_lbl_start') }}</label>
                    <input type="date" name="start_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('public.stf_hr_leave_lbl_end') }}</label>
                    <input type="date" name="end_date" class="form-control" required>
                </div>
            </div>
            <div class="form-group mb-4">
                <label class="form-label">{{ __('public.stf_hr_leave_lbl_reason') }}</label>
                <textarea name="reason" class="form-control" rows="3" maxlength="1000"></textarea>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeLeaveModal()">{{ __('public.stf_hr_leave_cancel') }}</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="send"></i>
                    {{ __('public.stf_hr_leave_btn_submit') }}
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Review Modal (approve / reject) --}}
<div id="review-modal" class="modal-fixed">
    <div class="modal-fixed__panel modal-fixed__panel--sm">
        <div class="modal-fixed__head">
            <h3 id="review-modal-title" class="modal-fixed__title">{{ __('public.stf_hr_leave_review_title') }}</h3>
        </div>
        <form id="review-form" method="POST" action="">
            @csrf
            <div class="form-group mb-4">
                <label class="form-label">{{ __('public.stf_hr_leave_lbl_review_notes') }}</label>
                <textarea name="review_notes" class="form-control" rows="3" maxlength="500"></textarea>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeReviewModal()">{{ __('public.stf_hr_leave_cancel') }}</button>
                <button type="submit" id="review-submit-btn" class="btn btn-primary btn-sm">{{ __('public.stf_hr_leave_btn_review_submit') }}</button>
            </div>
        </form>
    </div>
</div>

{{-- Withdraw confirm modal --}}
<div id="withdraw-modal" class="modal-fixed">
    <div class="modal-fixed__panel modal-fixed__panel--sm">
        <div class="modal-fixed__head">
            <h3 class="modal-fixed__title"><i data-lucide="undo-2"></i> {{ __('public.stf_hr_leave_withdraw_title') }}</h3>
        </div>
        <div class="modal__body">{{ __('public.stf_hr_leave_withdraw_body') }}</div>
        <form id="withdraw-form" method="POST" action="">
            @csrf
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeWithdrawModal()">{{ __('public.stf_hr_leave_cancel') }}</button>
                <button type="submit" class="btn btn-danger btn-sm">
                    <i data-lucide="undo-2"></i>
                    {{ __('public.stf_hr_leave_btn_withdraw_confirm') }}
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openLeaveModal()  { document.getElementById('leave-modal').classList.add('open'); }
    function closeLeaveModal() { document.getElementById('leave-modal').classList.remove('open'); }
    document.getElementById('leave-modal').addEventListener('click', function(e) {
        if (e.target === this) closeLeaveModal();
    });

    function openReviewModal(requestId, action) {
        var base = '{{ url('/portals/staff/hr/leave') }}';
        document.getElementById('review-form').action = base + '/' + requestId + '/' + action;
        document.getElementById('review-modal-title').textContent = action === 'approve' ? @json(__('staff_hr.js_approve_leave', [], app()->getLocale()) ?: 'Approve Leave') : @json(__('staff_hr.js_reject_leave', [], app()->getLocale()) ?: 'Reject Leave');
        var btn = document.getElementById('review-submit-btn');
        btn.className = action === 'approve' ? 'btn btn-success btn-sm' : 'btn btn-danger btn-sm';
        btn.textContent = action === 'approve' ? @json(__('staff_hr.js_approve', [], app()->getLocale()) ?: 'Approve') : @json(__('staff_hr.js_reject', [], app()->getLocale()) ?: 'Reject');
        document.getElementById('review-modal').classList.add('open');
    }
    function closeReviewModal() { document.getElementById('review-modal').classList.remove('open'); }
    document.getElementById('review-modal').addEventListener('click', function(e) {
        if (e.target === this) closeReviewModal();
    });

    function openWithdrawModal(action) {
        document.getElementById('withdraw-form').action = action;
        document.getElementById('withdraw-modal').classList.add('open');
    }
    function closeWithdrawModal() { document.getElementById('withdraw-modal').classList.remove('open'); }
    document.getElementById('withdraw-modal').addEventListener('click', function(e) {
        if (e.target === this) closeWithdrawModal();
    });
</script>
@endsection
