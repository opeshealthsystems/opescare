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
    <div class="auth-alert auth-alert-success" style="margin-bottom:1rem;"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>
@endif
@if(session('error'))
    <div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;"><i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div></div>
@endif

<div class="panel">
    <div class="panel-body" style="padding:0;">
        @if($flags->count() === 0)
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="toggle-right"></i></div>
                <h3>No feature flags</h3>
                <p>Visit the Control Center dashboard to seed default flags.</p>
            </div>
        @else
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr>
                    <th>Feature</th><th>Key</th><th>Scope</th><th>Status</th><th>Updated By</th><th>Actions</th>
                </tr></thead>
                <tbody>
                    @foreach($flags as $flag)
                    <tr>
                        <td style="font-weight:500;">{{ $flag->label }}</td>
                        <td><code style="font-size:.78rem;">{{ $flag->key }}</code></td>
                        <td><span class="badge badge-neutral" style="font-size:.72rem;">{{ $flag->scope }}</span></td>
                        <td>
                            <span class="badge {{ $flag->enabled ? 'badge-success' : 'badge-neutral' }}">
                                {{ $flag->enabled ? 'Enabled' : 'Disabled' }}
                            </span>
                        </td>
                        <td style="font-size:.78rem;color:var(--p-text-muted);">{{ $flag->updated_by ?? '—' }}</td>
                        <td>
                            <form method="POST" action="{{ route('portals.admin.cc.feature_flags.toggle', urlencode($flag->key)) }}" style="display:inline;">
                                @csrf
                                <input type="hidden" name="enabled" value="{{ $flag->enabled ? '0' : '1' }}">
                                <button type="submit" class="btn {{ $flag->enabled ? 'btn-ghost' : 'btn-success' }} btn-xs">
                                    {{ $flag->enabled ? 'Disable' : 'Enable' }}
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
@endsection
