@extends('layouts.portal')
@section('title', __('public.adm_cdss_allergy_title'))
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', __('public.adm_breadcrumb_home'))
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'CDSS')
@section('content')

<div class="breadcrumb">
    <a href="{{ route('portals.admin.cdss.index') }}">{{ __('public.adm_cdss_page_title') }}</a>
    <i data-lucide="chevron-right"></i>
    <span>{{ __('public.adm_cdss_allergy_h2') }}</span>
</div>

<div class="page-head">
    <h2>{{ __('public.adm_cdss_allergy_h2') }}</h2>
    <div class="page-head__spacer"></div>
    <button onclick="opOpenModal('add-allergy-modal')" class="btn btn-warning"><i data-lucide="plus"></i> {{ __('public.adm_cdss_btn_add_rule') }}</button>
</div>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

<div class="tabs mb-6">
    <a href="{{ route('portals.admin.cdss.drug-interactions') }}" class="tab">{{ __('public.adm_cdss_tab_drug') }}</a>
    <a href="{{ route('portals.admin.cdss.allergy-alerts') }}" class="tab active">{{ __('public.adm_cdss_tab_allergy') }}</a>
    <a href="{{ route('portals.admin.cdss.lab-alerts') }}" class="tab">{{ __('public.adm_cdss_tab_lab') }}</a>
</div>

<div class="alert alert-warning mb-6">
    <i data-lucide="alert-triangle"></i>
    <div>{{ __('public.adm_cdss_caution_msg') }}</div>
</div>

<div class="panel">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ __('public.adm_cdss_col_drug_name') }}</th>
                    <th>{{ __('public.adm_cdss_col_allergen_class') }}</th>
                    <th>{{ __('public.adm_cdss_col_severity') }}</th>
                    <th>{{ __('public.adm_cdss_col_reaction_type') }}</th>
                    <th>{{ __('public.adm_cdss_col_created') }}</th>
                    <th class="row-actions">{{ __('public.adm_cdss_col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rules as $rule)
                @php $sBadge=match(strtolower($rule->severity??'')){'mild'=>'badge-primary','moderate'=>'badge-warning','severe'=>'badge-danger',default=>'badge-neutral'}; @endphp
                <tr>
                    <td data-label="Drug Name"><span class="td-strong">{{ $rule->drug_name }}</span></td>
                    <td data-label="Allergen Class">{{ $rule->allergen_class }}</td>
                    <td data-label="Severity"><span class="badge {{ $sBadge }}">@enum($rule->severity, 'severity')</span></td>
                    <td data-label="Reaction Type">{{ $rule->reaction_type }}</td>
                    <td data-label="Created">{{ $rule->created_at->format('d M Y') }}</td>
                    <td class="row-actions" data-label="Actions">
                        <button type="button" class="btn btn-danger btn-sm" onclick="opOpenModal('delete-allergy-{{ $rule->id }}')"><i data-lucide="trash-2"></i></button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="td-muted empty-cell">{{ __('public.adm_cdss_allergy_empty') }}</td></tr>
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
<div id="delete-allergy-{{ $rule->id }}" class="modal-backdrop mt-6" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="delete-allergy-{{ $rule->id }}-title">
        <h3 class="modal__title" id="delete-allergy-{{ $rule->id }}-title"><i data-lucide="trash-2"></i> {{ __('public.adm_cdss_modal_delete_allergy_title') }}</h3>
        <form action="{{ route('portals.admin.cdss.destroy-allergy', $rule->id) }}" method="POST">
            @csrf @method('DELETE')
            <div class="modal__body"><p>{{ __('public.adm_cdss_confirm_delete_allergy') }} <strong>{{ $rule->drug_name }}</strong>?</p></div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" onclick="opCloseModal('delete-allergy-{{ $rule->id }}')">{{ __('public.adm_cdss_btn_cancel') }}</button>
                <button type="submit" class="btn btn-danger">{{ __('public.adm_cdss_btn_delete') }}</button>
            </div>
        </form>
    </div>
</div>
@endforeach

{{-- Add Allergy Modal --}}
<div id="add-allergy-modal" class="modal-backdrop mt-6" hidden>
    <div class="modal modal--md" role="dialog" aria-modal="true" aria-labelledby="add-allergy-title">
        <h3 class="modal__title" id="add-allergy-title"><i data-lucide="shield-alert"></i> {{ __('public.adm_cdss_modal_add_allergy_title') }}</h3>
        <form action="{{ route('portals.admin.cdss.store-allergy') }}" method="POST">
            @csrf
            <div class="modal__body">
                <div class="form-group">
                    <label class="form-label form-label-required">{{ __('public.adm_cdss_lbl_drug_name') }}</label>
                    <input type="text" name="drug_name" class="form-control" placeholder="{{ __('public.adm_cdss_ph_drug_name') }}" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label form-label-required">{{ __('public.adm_cdss_lbl_allergen_class') }}</label>
                        <input type="text" name="allergen_class" class="form-control" placeholder="{{ __('public.adm_cdss_ph_allergen_class') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label form-label-required">{{ __('public.adm_cdss_lbl_severity') }}</label>
                        <select name="severity" class="form-control" required>
                            <option value="">{{ __('public.adm_cdss_opt_select') }}</option>
                            <option value="mild">{{ __('public.adm_cdss_opt_mild') }}</option>
                            <option value="moderate">{{ __('public.adm_cdss_opt_moderate') }}</option>
                            <option value="severe">{{ __('public.adm_cdss_opt_severe') }}</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('public.adm_cdss_lbl_reaction_type') }}</label>
                    <input type="text" name="reaction_type" class="form-control" placeholder="{{ __('public.adm_cdss_ph_reaction_type') }}">
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" onclick="opCloseModal('add-allergy-modal')" class="btn btn-ghost">{{ __('public.adm_cdss_btn_cancel') }}</button>
                <button type="submit" class="btn btn-warning"><i data-lucide="plus"></i> {{ __('public.adm_cdss_btn_add_rule') }}</button>
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
