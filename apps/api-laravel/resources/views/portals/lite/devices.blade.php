@extends('layouts.lite')
@section('title', 'Lite Devices')

@section('content')

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;">
    <div>
        <h1 class="lite-page-title">Devices</h1>
        <p class="lite-page-sub">Manage registered OpesCare Lite devices</p>
    </div>
</div>

{{-- Stats --}}
<div class="lite-stat-row" style="margin-bottom:16px;">
    <div class="lite-stat-chip">
        <div class="lite-stat-chip__val">{{ $stats['total_devices'] }}</div>
        <div class="lite-stat-chip__label">Total</div>
    </div>
    <div class="lite-stat-chip">
        <div class="lite-stat-chip__val" style="color:#16a34a;">{{ $stats['active_devices'] }}</div>
        <div class="lite-stat-chip__label">Active</div>
    </div>
    <div class="lite-stat-chip">
        <div class="lite-stat-chip__val" style="color:#d97706;">{{ $stats['pending_devices'] }}</div>
        <div class="lite-stat-chip__label">Pending</div>
    </div>
    @if($stats['open_conflicts'] > 0)
    <div class="lite-stat-chip">
        <div class="lite-stat-chip__val" style="color:#dc2626;">{{ $stats['open_conflicts'] }}</div>
        <div class="lite-stat-chip__label">Conflicts</div>
    </div>
    @endif
</div>

@if($devices->isEmpty())
    <div class="lite-alert lite-alert--info">
        <i data-lucide="monitor-smartphone" style="width:16px;height:16px;flex-shrink:0;"></i>
        No Lite devices registered yet. Devices register via the API endpoint
        <code style="font-size:0.78rem;background:#e0e7ff;padding:1px 5px;border-radius:3px;">POST /api/v1/lite/register-device</code>.
    </div>
@else
    <div class="lite-card">
        <div class="lite-card__body" style="padding:0;overflow-x:auto;">
            <table class="lite-table">
                <thead>
                    <tr>
                        <th>Device</th>
                        <th>Platform</th>
                        <th>Status</th>
                        <th>Modules</th>
                        <th>Last Seen</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($devices as $dev)
                    <tr>
                        <td>
                            <div style="font-weight:600;font-size:0.88rem;">{{ $dev->device_name }}</div>
                            <div style="font-family:monospace;font-size:0.72rem;color:#9ca3af;">{{ substr($dev->id, 0, 8) }}…</div>
                        </td>
                        <td style="font-size:0.82rem;">{{ ucfirst($dev->platform ?? 'web') }}</td>
                        <td>
                            <span class="lite-badge lite-badge--{{ $dev->statusColor() }}">
                                {{ ucfirst($dev->status) }}
                            </span>
                        </td>
                        <td style="font-size:0.78rem;color:#64748b;">
                            {{ $dev->entitlements->where('is_enabled', true)->count() }} modules
                        </td>
                        <td style="font-size:0.79rem;color:#6b7280;">
                            {{ $dev->last_seen_at ? $dev->last_seen_at->diffForHumans() : 'Never' }}
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;justify-content:flex-end;">
                                @if($dev->status === 'pending')
                                    <form method="POST"
                                          action="{{ route('portals.lite.devices.activate', $dev) }}"
                                          onsubmit="return confirm('Activate this device?')">
                                        @csrf
                                        <button type="submit" class="lite-btn lite-btn--success"
                                                style="padding:4px 10px;font-size:0.75rem;">Activate</button>
                                    </form>
                                @endif
                                @if(!in_array($dev->status, ['revoked', 'lost']))
                                    <form method="POST"
                                          action="{{ route('portals.lite.devices.revoke', $dev) }}"
                                          onsubmit="return confirm('Revoke this device? This cannot be undone.')">
                                        @csrf
                                        <input type="hidden" name="reason" value="Revoked via Lite portal.">
                                        <button type="submit" class="lite-btn lite-btn--danger"
                                                style="padding:4px 10px;font-size:0.75rem;">Revoke</button>
                                    </form>
                                @endif
                                <a href="{{ route('portals.lite.offline_events', $dev) }}"
                                   class="lite-btn lite-btn--outline"
                                   style="padding:4px 10px;font-size:0.75rem;">Events</a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div style="margin-top:10px;">{{ $devices->links() }}</div>
@endif

<div class="lite-alert lite-alert--info" style="font-size:0.8rem;margin-top:8px;">
    <i data-lucide="info" style="width:14px;height:14px;flex-shrink:0;"></i>
    New devices must be registered via the API and then activated here. Revoked devices cannot sync.
</div>

@endsection
