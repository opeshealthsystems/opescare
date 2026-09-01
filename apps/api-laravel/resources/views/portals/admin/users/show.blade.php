@extends('layouts.portal')
@section('title', 'User: ' . $user->name)
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', __('public.adm_users_idx_breadcrumb_section'))
@section('content')

<div class="breadcrumb">
    <a href="{{ route('admin.users.index') }}">{{ __('public.adm_users_show_breadcrumb_users') }}</a>
    <i data-lucide="chevron-right"></i>
    <span>{{ $user->name }}</span>
</div>

<div class="entity-head">
    <div class="entity-head__icon"><i data-lucide="user"></i></div>
    <h2 class="entity-head__title">{{ $user->name }}</h2>
    @if(($user->status??'')==='active')<span class="badge badge-success">{{ __('public.adm_users_show_badge_active') }}</span>
    @elseif(($user->status??'')==='suspended')<span class="badge badge-danger">{{ __('public.adm_users_show_badge_suspended') }}</span>
    @else<span class="badge badge-warning">@enum($user->status ?? 'pending')</span>@endif
    <div class="entity-head__spacer"></div>
    @if(($user->status??'')==='suspended')
    <form method="POST" action="{{ route('portals.admin.users.activate', $user) }}" class="inline-form">@csrf
        <button class="btn btn-success"><i data-lucide="check-circle"></i> {{ __('public.adm_users_show_btn_activate') }}</button>
    </form>
    @else
    <button type="button" class="btn btn-warning" onclick="opOpenModal('suspend-modal')"><i data-lucide="ban"></i> {{ __('public.adm_users_show_btn_suspend') }}</button>
    @endif
    <button type="button" class="btn btn-danger" onclick="opOpenModal('delete-modal')"><i data-lucide="trash-2"></i> {{ __('public.adm_users_show_btn_delete') }}</button>
</div>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

<div class="tabs">
    <span class="tab active">{{ __('public.adm_users_show_tab_overview') }}</span>
</div>

<div class="field-grid mb-6">
    <div class="stat-card">
        <div class="stat-card__label">{{ __('public.adm_users_show_stat_email') }}</div>
        <div class="stat-card__value">{{ $user->email }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">{{ __('public.adm_users_show_stat_role') }}</div>
        <div class="stat-card__value">{{ $user->role?->name ?? 'no role' }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">{{ __('public.adm_users_show_stat_status') }}</div>
        <div class="stat-card__value">@enum($user->status ?? 'pending')</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">{{ __('public.adm_users_show_stat_joined') }}</div>
        <div class="stat-card__value">{{ $user->created_at?->format('M d, Y') ?? '—' }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">{{ __('public.adm_users_show_stat_last_login') }}</div>
        <div class="stat-card__value">{{ isset($user->last_login_at) ? $user->last_login_at->diffForHumans() : __('public.adm_users_show_stat_never') }}</div>
    </div>
</div>

<div class="panel mb-6">
    <div class="panel-header"><h3 class="panel-title"><i data-lucide="edit-2"></i> {{ __('public.adm_users_show_panel_edit') }}</h3></div>
    <div class="panel-body">
        <form method="POST" action="{{ route('portals.admin.users.update', $user) }}">
            @csrf @method('PUT')
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label form-label-required">{{ __('public.adm_users_show_lbl_name') }}</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    @error('name')<div class="form-hint">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label form-label-required">{{ __('public.adm_users_show_lbl_email') }}</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    @error('email')<div class="form-hint">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="form-group mt-6">
                <label class="form-label form-label-required">{{ __('public.adm_users_show_lbl_role') }}</label>
                <select name="role_id" class="form-control" required>
                    <option value="">{{ __('public.adm_users_show_ph_role') }}</option>
                    @foreach($roles as $role)
                    <option value="{{ $role->id }}" @selected(old('role_id',$user->role_id)==$role->id)>{{ $role->name }}</option>
                    @endforeach
                </select>
                @error('role_id')<div class="form-hint">{{ $message }}</div>@enderror
            </div>
            <div class="mt-6">
                <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> {{ __('public.adm_users_show_btn_save') }}</button>
            </div>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-header"><h3 class="panel-title"><i data-lucide="key"></i> {{ __('public.adm_users_show_panel_reset_pw') }}</h3></div>
    <div class="panel-body">
        <form method="POST" action="{{ route('portals.admin.users.reset-password', $user) }}">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label form-label-required">{{ __('public.adm_users_show_lbl_new_pw') }}</label>
                    <input type="password" name="password" class="form-control" required>
                    @error('password')<div class="form-hint">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label form-label-required">{{ __('public.adm_users_show_lbl_confirm_pw') }}</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="btn btn-warning"><i data-lucide="key"></i> {{ __('public.adm_users_show_btn_reset_pw') }}</button>
            </div>
        </form>
    </div>
</div>

<div id="suspend-modal" class="modal-backdrop mt-6" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="suspend-modal-title">
        <h3 class="modal__title" id="suspend-modal-title"><i data-lucide="ban"></i> {{ __('public.adm_users_show_modal_suspend_title') }}</h3>
        <form method="POST" action="{{ route('portals.admin.users.suspend', $user) }}">@csrf
            <div class="modal__body"><p>{{ __('public.adm_users_show_modal_suspend_body', ['name' => $user->name]) }}</p></div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" onclick="opCloseModal('suspend-modal')">{{ __('public.adm_users_show_modal_suspend_btn_cancel') }}</button>
                <button type="submit" class="btn btn-warning"><i data-lucide="ban"></i> {{ __('public.adm_users_show_modal_suspend_btn_suspend') }}</button>
            </div>
        </form>
    </div>
</div>

<div id="delete-modal" class="modal-backdrop mt-6" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="delete-modal-title">
        <h3 class="modal__title" id="delete-modal-title"><i data-lucide="alert-triangle"></i> {{ __('public.adm_users_show_modal_delete_title') }}</h3>
        <form method="POST" action="{{ route('portals.admin.users.destroy', $user) }}">@csrf @method('DELETE')
            <div class="modal__body"><p>{{ __('public.adm_users_show_modal_delete_body', ['name' => $user->name]) }}</p></div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" onclick="opCloseModal('delete-modal')">{{ __('public.adm_users_show_modal_delete_btn_cancel') }}</button>
                <button type="submit" class="btn btn-danger"><i data-lucide="trash-2"></i> {{ __('public.adm_users_show_modal_delete_btn_delete') }}</button>
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