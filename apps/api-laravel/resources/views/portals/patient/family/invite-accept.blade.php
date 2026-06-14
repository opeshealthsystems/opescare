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
        <div class="alert alert-danger mb-6">
            <i data-lucide="alert-circle"></i> {{ $error }}
        </div>
        <a href="{{ route('login') }}" class="btn btn-primary">Go to Login</a>
        @else
        <div class="empty-state">
            <div class="empty-state-icon"><i data-lucide="users"></i></div>
            <h3>Family Access Request</h3>
            <p>
                <strong>{{ $link->guardianUser->name ?? $link->guardianUser->email }}</strong> wants to link to
                <strong>{{ $link->dependentPatient->first_name }} {{ $link->dependentPatient->last_name }}</strong>'s health records
                as <strong>{{ ucfirst(str_replace('_',' ',$link->relationship)) }}</strong>
                with <strong>{{ $link->access_level === 'full' ? 'full' : 'read-only' }}</strong> access.
            </p>
        </div>
        <form method="POST" action="{{ route('portals.patient.family.invite.confirm', $token) }}">
            @csrf
            <div class="row-actions" style="justify-content:center;">
                <button type="submit" class="btn btn-primary">Accept Invite</button>
                <a href="{{ route('login') }}" class="btn btn-secondary">Decline</a>
            </div>
        </form>
        @endif
    </div>
</div>
<script src="{{ asset('js/lucide.min.js') }}"></script>
<script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>
</body>
</html>
