@extends('layouts.portal')

@section('title', __('staff_data.title_audit', [], app()->getLocale()) ?: 'Import Audit Log')

@section('sidebar_role_badge')
<div class="sidebar-role-badge">{{ __('public.staff_portal.cdss_sidebar_role') }}</div>
@endsection
@section('sidebar_user_role', __('public.staff_portal.cdss_sidebar_role'))

@section('sidebar_nav')
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
