@extends('layouts.portal')

@section('title', __('staff_data.title_import', [], app()->getLocale()) ?: 'Data Import')

@section('sidebar_role_badge')
<div class="sidebar-role-badge">{{ __('public.staff_portal.cdss_sidebar_role') }}</div>
@endsection
@section('sidebar_user_role', __('public.staff_portal.cdss_sidebar_role'))

@section('sidebar_nav')

{{-- Rollback Modal --}}
<div id="rollback-modal" class="modal-backdrop mt-6" hidden>
    <div class="modal" role="dialog" aria-modal="true">
        <h3 class="modal__title"><i data-lucide="rotate-ccw"></i> {{ __('public.stf_import_rollback_modal_title') }}</h3>
        <form id="rollback-form" method="POST" action="">
            @csrf
            <div class="modal__body">
                <p>{{ __('public.stf_import_rollback_desc') }}</p>
                <div class="form-group">
                    <label class="form-label">{{ __('public.stf_import_rollback_reason') }} <span class="td-muted">{{ __('public.stf_import_rollback_optional') }}</span></label>
                    <textarea name="reason" class="form-control" rows="3" maxlength="500" placeholder="{{ __('public.stf_import_rollback_ph') }}"></textarea>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeRollbackModal()">{{ __('public.stf_import_rollback_back') }}</button>
                <button type="submit" class="btn btn-danger btn-sm">
                    <i data-lucide="rotate-ccw"></i> {{ __('public.stf_import_rollback_btn') }}
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openRollbackModal(jobId) {
        document.getElementById('rollback-form').setAttribute('action', '{{ url('/portals/staff/data-import') }}/' + jobId + '/rollback');
        document.getElementById('rollback-modal').removeAttribute('hidden');
    }
    function closeRollbackModal() { document.getElementById('rollback-modal').setAttribute('hidden',''); }
    document.getElementById('rollback-modal').addEventListener('click', function(e) {
        if (e.target === this) closeRollbackModal();
    });
</script>
@endsection
