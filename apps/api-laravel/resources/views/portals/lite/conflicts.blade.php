@extends('layouts.lite')
@section('title', 'Sync Conflicts')

@section('content')

<h1 class="lite-page-title">Sync Conflicts</h1>
<p class="lite-page-sub">Offline events that could not be automatically applied</p>

@if($conflicts->isEmpty())
    <div style="text-align:center;padding:48px 0;color:#94a3b8;">
        <i data-lucide="check-circle" style="width:40px;height:40px;margin-bottom:12px;color:#16a34a;opacity:.5;"></i>
        <p style="margin:0;font-size:0.9rem;">No open conflicts — all syncs applied cleanly.</p>
    </div>
@else
    <div class="lite-card">
        <div class="lite-card__body" style="padding:0;overflow-x:auto;">
            <table class="lite-table">
                <thead>
                    <tr>
                        <th>Event Type</th>
                        <th>Conflict</th>
                        <th>Device</th>
                        <th>Status</th>
                        <th>Captured</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($conflicts as $c)
                    <tr>
                        <td style="font-weight:600;font-size:0.85rem;">
                            {{ ucwords(str_replace('_', ' ', $c->offlineEvent?->event_type ?? '—')) }}
                        </td>
                        <td style="font-size:0.82rem;color:#6b7280;">
                            {{ ucwords(str_replace('_', ' ', $c->conflict_type)) }}
                        </td>
                        <td style="font-size:0.82rem;">{{ $c->device?->device_name ?? '—' }}</td>
                        <td>
                            <span class="lite-badge lite-badge--{{ $c->statusColor() }}">
                                {{ ucfirst($c->status) }}
                            </span>
                        </td>
                        <td style="font-size:0.79rem;color:#6b7280;">
                            {{ $c->offlineEvent?->captured_at?->format('d M Y H:i') ?? '—' }}
                        </td>
                        <td>
                            @if($c->isOpen())
                            <div style="display:flex;gap:6px;justify-content:flex-end;">
                                <form method="POST" action="{{ route('portals.lite.conflicts.resolve', $c) }}">
                                    @csrf
                                    <input type="hidden" name="resolution" value="resolved">
                                    <input type="hidden" name="note" value="Resolved via Lite portal.">
                                    <button type="submit" class="lite-btn lite-btn--success"
                                            style="padding:4px 10px;font-size:0.75rem;">Resolve</button>
                                </form>
                                <form method="POST" action="{{ route('portals.lite.conflicts.resolve', $c) }}"
                                      onsubmit="return confirm('Dismiss this conflict?')">
                                    @csrf
                                    <input type="hidden" name="resolution" value="dismiss">
                                    <button type="submit" class="lite-btn lite-btn--outline"
                                            style="padding:4px 10px;font-size:0.75rem;">Dismiss</button>
                                </form>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div style="margin-top:10px;">{{ $conflicts->links() }}</div>
@endif

@endsection
