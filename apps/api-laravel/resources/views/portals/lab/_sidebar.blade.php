@php $l = app()->getLocale(); @endphp
<div class="sidebar-role-badge">
    <i data-lucide="microscope"></i>
    {{ __('public.lab_portal.role_badge', [], $l) ?: 'Laboratory' }}
</div>
@endfeature
