<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Family Invite — OpesCare</title>
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
</head>
<body class="portal-body" style="display:flex;align-items:center;justify-content:center;min-height:100vh;">
<div class="panel" style="max-width:480px;width:100%;margin:2rem;">
    <div class="panel-body" style="text-align:center;">
        @if($error)
        <div class="alert alert-danger" style="margin-bottom:var(--p-space-5);">
            <i data-lucide="alert-circle"></i> {{ $error }}
        </div>
        <a href="{{ route('login') }}" class="btn btn-primary">Go to Login</a>
        @else
        <div style="margin-bottom:var(--p-space-5);">
            <div style="width:3rem;height:3rem;background:var(--p-primary-soft);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto var(--p-space-4);">
                <i data-lucide="users" style="color:var(--p-primary);"></i>
            </div>
            <h2 style="font-size:1.125rem;font-weight:700;margin-bottom:0.5rem;">Family Access Request</h2>
            <p style="font-size:0.875rem;color:var(--p-text-muted);">
                <strong>{{ $link->guardianUser->name ?? $link->guardianUser->email }}</strong> wants to link to
                <strong>{{ $link->dependentPatient->first_name }} {{ $link->dependentPatient->last_name }}</strong>'s health records
                as <strong>{{ ucfirst(str_replace('_',' ',$link->relationship)) }}</strong>
                with <strong>{{ $link->access_level === 'full' ? 'full' : 'read-only' }}</strong> access.
            </p>
        </div>
        <form method="POST" action="{{ route('portals.patient.family.invite.confirm', $token) }}">
            @csrf
            <div style="display:flex;gap:var(--p-space-3);justify-content:center;">
                <button type="submit" class="btn btn-primary">Accept Invite</button>
                <a href="{{ route('login') }}" class="btn" style="background:var(--p-surface-2);color:var(--p-text);">Decline</a>
            </div>
        </form>
        @endif
    </div>
</div>
<script src="{{ asset('js/lucide.min.js') }}"></script>
<script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>
</body>
</html>
