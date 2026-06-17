@extends('layouts.portal')
@section('title', __('public.adm_users_idx_title'))
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', __('public.adm_users_idx_breadcrumb_section'))
@section('content')

<div class="breadcrumb">
    <a href="{{ route('admin.users.index') }}">{{ __('public.adm_users_idx_breadcrumb_users') }}</a>
    <i data-lucide="chevron-right"></i>
    <span>{{ __('public.adm_users_idx_breadcrumb_dir') }}</span>
</div>

<div class="page-head">
    <h2>{{ __('public.adm_users_idx_heading') }}</h2>
    <div class="page-head__spacer"></div>
</div>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

<form method="GET" action="{{ route('admin.users.index') }}" class="filter-bar">
    <label class="filter-search">
        <i data-lucide="search"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('public.adm_users_idx_ph_search') }}" aria-label="{{ __('public.aria_search_users') }}">
    </label>
    <select name="role_id" class="filter-select" aria-label="Role" onchange="this.form.submit()">
        <option value="">{{ __('public.adm_users_idx_filter_all_roles') }}</option>
        @foreach($roles as $role)<option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>@endforeach
    </select>
    <select name="status" class="filter-select" aria-label="{{ __('public.aria_status') }}" onchange="this.form.submit()">
        <option value="">{{ __('public.adm_users_idx_filter_all') }}</option>
        <option value="active" {{ request('status')==='active' ? 'selected' : '' }}>{{ __('public.adm_users_idx_filter_active') }}</option>
        <option value="pending" {{ request('status')==='pending' ? 'selected' : '' }}>{{ __('public.adm_users_idx_filter_pending') }}</option>
        <option value="suspended" {{ request('status')==='suspended' ? 'selected' : '' }}>{{ __('public.adm_users_idx_filter_suspended') }}</option>
    </select>
    <button type="submit" class="btn btn-secondary btn-sm"><i data-lucide="filter"></i> {{ __('public.adm_users_idx_btn_filter') }}</button>
    <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm">{{ __('public.adm_users_idx_btn_reset') }}</a>
</form>

<div class="panel">
    <div class="panel-header">
        <h3 class="panel-title"><i data-lucide="users"></i> {{ $users->total() }} users</h3>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ __('public.adm_users_idx_col_name') }}</th>
                    <th>{{ __('public.adm_users_idx_col_email') }}</th>
                    <th>{{ __('public.adm_users_idx_col_role') }}</th>
                    <th>{{ __('public.adm_users_idx_col_status') }}</th>
                    <th>{{ __('public.adm_users_idx_col_created') }}</th>
                    <th class="row-actions">{{ __('public.adm_users_idx_col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse($users as $user)
                <tr>
                    <td data-label="{{ __('public.adm_users_idx_col_name') }}"><span class="td-strong">{{ $user->name }}</span></td>
                    <td data-label="{{ __('public.adm_users_idx_col_email') }}">{{ $user->email }}</td>
                    <td data-label="{{ __('public.adm_users_idx_col_role') }}"><span class="badge badge-neutral">{{ $user->role?->name ?? 'none' }}</span></td>
                    <td data-label="{{ __('public.adm_users_idx_col_status') }}">
                        @if($user->status==='active')<span class="badge badge-success">{{ __('public.adm_users_idx_badge_active') }}</span>
                        @elseif($user->status==='suspended')<span class="badge badge-danger">{{ __('public.adm_users_idx_badge_suspended') }}</span>
                        @else<span class="badge badge-warning">{{ ucfirst($user->status) }}</span>@endif
                    </td>
                    <td data-label="{{ __('public.adm_users_idx_col_created') }}" class="td-muted">{{ $user->created_at?->format('d M Y') }}</td>
                    <td class="row-actions" data-label="{{ __('public.adm_users_idx_col_actions') }}">
                        @if($user->status!=='active')
                        <form method="POST" action="{{ route('admin.users.activate',$user->id) }}" class="inline-form">@csrf
                            <button type="submit" class="icon-btn" aria-label="{{ __('public.aria_activate_user') }}" title="{{ __('public.aria_activate') }}"><i data-lucide="check-circle"></i></button>
                        </form>
                        @endif
                        @if($user->status!=='suspended')
                        <form method="POST" action="{{ route('admin.users.suspend',$user->id) }}" class="inline-form">@csrf
                            <button type="submit" class="icon-btn" aria-label="{{ __('public.aria_suspend_user') }}" title="{{ __('public.aria_suspend') }}"><i data-lucide="ban"></i></button>
                        </form>
                        @endif
                        <button type="button" class="icon-btn" aria-label="{{ __('public.aria_delete_user') }}" title="{{ __('public.aria_delete') }}" onclick="opOpenModal('delete-user-{{ $user->id }}')"><i data-lucide="trash-2"></i></button>
                        <div id="delete-user-{{ $user->id }}" class="modal-backdrop mt-6" hidden>
                            <div class="modal" role="dialog" aria-modal="true">
                                <h3 class="modal__title"><i data-lucide="alert-triangle"></i> {{ __('public.adm_users_idx_modal_delete_title') }}</h3>
                                <form method="POST" action="{{ route('admin.users.destroy',$user->id) }}">@csrf @method('DELETE')
                                    <div class="modal__body"><p>{{ __('public.adm_users_idx_modal_delete_title') }} <strong>{{ $user->name }}</strong>?</p></div>
                                    <div class="modal__footer">
                                        <button type="button" class="btn btn-ghost" onclick="opCloseModal('delete-user-{{ $user->id }}')">{{ __('public.adm_users_idx_modal_delete_btn_cancel') }}</button>
                                        <button type="submit" class="btn btn-danger">{{ __('public.adm_users_idx_modal_delete_btn_delete') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="td-muted empty-cell">{{ __('public.adm_users_idx_empty') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="panel-body">{{ $users->links() }}</div>
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
