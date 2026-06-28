@extends('layouts.portal')
@section('title', __('public.adm_cdss_lab_title'))
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', __('public.adm_breadcrumb_home'))
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'CDSS')
@section('content')

<div class="breadcrumb">
    <a href="{{ route('portals.admin.cdss.index') }}">{{ __('public.adm_cdss_page_title') }}</a>
    <i data-lucide="chevron-right"></i>
    <span>{{ __('public.adm_cdss_lab_h2') }}</span>
</div>

<div class="page-head">
    <h2>{{ __('public.adm_cdss_lab_h2') }}</h2>
    <div class="page-head__spacer"></div>
    <button onclick="opOpenModal('add-lab-modal')" class="btn btn-primary"><i data-lucide="plus"></i> {{ __('public.adm_cdss_btn_add_rule') }}</button>
</div>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

<div class="tabs mb-6">
    <a href="{{ route('portals.admin.cdss.drug-interactions') }}" class="tab">{{ __('public.adm_cdss_tab_drug') }}</a>
    <a href="{{ route('portals.admin.cdss.allergy-alerts') }}" class="tab">{{ __('public.adm_cdss_tab_allergy') }}</a>
    <a href="{{ route('portals.admin.cdss.lab-alerts') }}" class="tab active">{{ __('public.adm_cdss_tab_lab') }}</a>
</div>

<div class="alert alert-info mb-6">
    <i data-lucide="flask-conical"></i>
    <div>{{ __('public.adm_cdss_lab_info_msg') }}</div>
</div>

<div class="panel">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ __('public.adm_cdss_col_test_name') }}</th>
                    <th>{{ __('public.adm_cdss_col_condition') }}</th>
                    <th>{{ __('public.adm_cdss_col_threshold') }}</th>
                    <th>{{ __('public.adm_cdss_col_unit') }}</th>
                    <th>{{ __('public.adm_cdss_col_severity') }}</th>
                    <th>{{ __('public.adm_cdss_col_message') }}</th>
                    <th>{{ __('public.adm_cdss_col_created') }}</th>
                    <th class="row-actions">{{ __('public.adm_cdss_col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rules as $rule)
                @php $sBadge=match(strtolower($rule->severity??'')){'info'=>'badge-neutral','warning'=>'badge-warning','critical'=>'badge-danger',default=>'badge-neutral'}; @endphp
                <tr>
                    <td data-label="Test Name"><span class="td-strong">{{ $rule->test_name }}</span></td>
                    <td data-label="Condition">{{ $rule->condition }}</td>
                    <td data-label="Threshold"><span class="mono td-strong">{{ $rule->threshold_value }}</span></td>
                    <td data-label="Unit">{{ $rule->unit ?? '—' }}</td>
                    <td data-label="Severity"><span class="badge {{ $sBadge }}">@enum($rule->severity, 'severity')</span></td>
                    <td data-label="Message">{{ Str::limit($rule->alert_message, 60) }}</td>
                    <td data-label="Created">{{ $rule->created_at->format('d M Y') }}</td>
                    <td class="row-actions" data-label="Actions">
                        <button type="button" class="btn btn-danger btn-sm" onclick="opOpenModal('delete-lab-{{ $rule->id }}')"><i data-lucide="trash-2"></i></button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="td-muted empty-cell">{{ __('public.adm_cdss_lab_empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(isset($rules) && $rules->hasPages())
    <div class="panel-body">{{ $rules->links() }}</div>
    @endif
</div>

{{-- Delete confirm modals --}}
@foreach($rules as $rule)
<div id="delete-lab-{{ $rule->id }}" class="modal-backdrop mt-6" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="delete-lab-{{ $rule->id }}-title">
        <h3 class="modal__title" id="delete-lab-{{ $rule->id }}-title"><i data-lucide="trash-2"></i> {{ __('public.adm_cdss_modal_delete_lab_title') }}</h3>
        <form action="{{ route('portals.admin.cdss.destroy-lab', $rule->id) }}" method="POST">
            @csrf @method('DELETE')
            <div class="modal__body"><p>{{ __('public.adm_cdss_confirm_delete_lab') }} <strong>{{ $rule->test_name }}</strong>?</p></div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" onclick="opCloseModal('delete-lab-{{ $rule->id }}')">{{ __('public.adm_cdss_btn_cancel') }}</button>
                <button type="submit" class="btn btn-danger">{{ __('public.adm_cdss_btn_delete') }}</button>
            </div>
        </form>
    </div>
</div>
@endforeach

{{-- Add Lab Alert Modal --}}
<div id="add-lab-modal" class="modal-backdrop mt-6" hidden>
    <div class="modal modal--lg" role="dialog" aria-modal="true" aria-labelledby="add-lab-title">
        <h3 class="modal__title" id="add-lab-title"><i data-lucide="flask-conical"></i> {{ __('public.adm_cdss_modal_add_lab_title') }}</h3>
        <form action="{{ route('portals.admin.cdss.store-lab') }}" method="POST">
            @csrf
            <div class="modal__body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label form-label-required">{{ __('public.adm_cdss_lbl_test_name') }}</label>
                        <input type="text" name="test_name" class="form-control" placeholder="{{ __('public.adm_cdss_ph_test_name') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('public.adm_cdss_lbl_unit') }}</label>
                        <input type="text" name="unit" class="form-control" placeholder="{{ __('public.adm_cdss_ph_unit') }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label form-label-required">{{ __('public.adm_cdss_lbl_condition') }}</label>
                        <select name="condition" class="form-control" required>
                            <option value="">{{ __('public.adm_cdss_opt_select') }}</option>
                            <option value="greater_than">{{ __('public.adm_cdss_opt_greater_than') }}</option>
                            <option value="less_than">{{ __('public.adm_cdss_opt_less_than') }}</option>
                            <option value="greater_than_or_equal">≥</option>
                            <option value="less_than_or_equal">≤</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label form-label-required">{{ __('public.adm_cdss_lbl_threshold') }}</label>
                        <input type="number" name="threshold_value" step="any" class="form-control" placeholder="{{ __('public.adm_cdss_ph_threshold') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label form-label-required">{{ __('public.adm_cdss_lbl_severity') }}</label>
                        <select name="severity" class="form-control" required>
                            <option value="">{{ __('public.adm_cdss_opt_select') }}</option>
                            <option value="info">@enum('info', 'severity')</option>
                            <option value="warning">@enum('warning', 'severity')</option>
                            <option value="critical">@enum('critical', 'severity')</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label form-label-required">{{ __('public.adm_cdss_lbl_alert_message') }}</label>
                    <input type="text" name="alert_message" class="form-control" placeholder="{{ __('public.adm_cdss_ph_alert_message') }}" required>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" onclick="opCloseModal('add-lab-modal')" class="btn btn-ghost">{{ __('public.adm_cdss_btn_cancel') }}</button>
                <button type="submit" class="btn btn-primary"><i data-lucide="plus"></i> {{ __('public.adm_cdss_btn_add_rule') }}</button>
            </div>
        </form>
    </div>
</div>
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
