@extends('layouts.portal')
@section('title', 'Feature Flags')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin Portal')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Feature Flags')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Feature Flags</h1>
        <p class="page-subtitle">Enable or disable product features globally or by scope.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>
@endif
@if(session('error'))
    <div class="alert alert-danger mb-6"><i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div></div>
@endif

<div class="panel">
    @if($flags->count() === 0)
        <div class="empty-state">
            <div class="empty-state-icon"><i data-lucide="toggle-right"></i></div>
            <h3>No feature flags</h3>
            <p>Visit the Control Center dashboard to seed default flags.</p>
        </div>
    @else
    <div class="panel-body">
        @foreach($flags as $flag)
        <div class="toggle-row">
            <div class="toggle-row__body">
                <div class="toggle-row__title">{{ $flag->label }}</div>
                <div class="toggle-row__desc">
                    <span class="code-token">{{ $flag->key }}</span>
                    <span class="badge badge-neutral badge-sm">{{ $flag->scope }}</span>
                    @if($flag->updated_by)<span class="td-muted">· Updated by {{ $flag->updated_by }}</span>@endif
                </div>
            </div>
            <span class="badge {{ $flag->enabled ? 'badge-success' : 'badge-neutral' }} badge-sm">
                {{ $flag->enabled ? 'Enabled' : 'Disabled' }}
            </span>
            <form method="POST" action="{{ route('portals.admin.cc.feature_flags.toggle', urlencode($flag->key)) }}" class="inline-form">
                @csrf
                <input type="hidden" name="enabled" value="{{ $flag->enabled ? '0' : '1' }}">
                <label class="switch">
                    <input type="checkbox" {{ $flag->enabled ? 'checked' : '' }} onchange="this.form.submit()" aria-label="Toggle {{ $flag->label }}">
                    <span class="switch__track"></span>
                </label>
            </form>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
