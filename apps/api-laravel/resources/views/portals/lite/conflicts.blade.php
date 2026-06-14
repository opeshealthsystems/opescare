@extends('layouts.lite')
@section('title', 'Sync Conflicts')

@section('content')

<h1 class="lite-page-title">Sync conflicts</h1>
<p class="lite-page-sub">Offline events that could not be automatically applied</p>

@if($conflicts->isEmpty())
    <div class="lite-empty lite-empty--success">
        <i data-lucide="check-circle"></i>
        <p>No open conflicts — all syncs applied cleanly.</p>
    </div>
@else
    <div class="lite-card">
        <div class="lite-card__body lite-card__body--scroll">
            <table class="lite-table">
                <thead>
                    <tr><th>Event type</th><th>Conflict</th><th>Device</th><th>Status</th><th>Captured</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach($conflicts as $c)
                    <tr>
                        <td class="lite-td-strong">{{ ucwords(str_replace('_', ' ', $c->offlineEvent?->event_type ?? '—')) }}</td>
                        <td>{{ ucwords(str_replace('_', ' ', $c->conflict_type)) }}</td>
                        <td>{{ $c->device?->device_name ?? '—' }}</td>
                        <td><span class="lite-badge lite-badge--{{ $c->statusColor() }}">{{ ucfirst($c->status) }}</span></td>
                        <td>{{ $c->offlineEvent?->captured_at?->format('d M Y H:i') ?? '—' }}</td>
                        <td>
                            @if($c->isOpen())
                            <div class="lite-row lite-row--end">
                                <form method="POST" action="{{ route('portals.lite.conflicts.resolve', $c) }}">
                                    @csrf
                                    <input type="hidden" name="resolution" value="resolved">
                                    <input type="hidden" name="note" value="Resolved via Lite portal.">
                                    <button type="submit" class="lite-btn lite-btn--success lite-btn--xs">Resolve</button>
                                </form>
                                <form method="POST" action="{{ route('portals.lite.conflicts.resolve', $c) }}" onsubmit="return confirm('Dismiss this conflict?')">
                                    @csrf
                                    <input type="hidden" name="resolution" value="dismiss">
                                    <button type="submit" class="lite-btn lite-btn--outline lite-btn--xs">Dismiss</button>
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
    <div class="lite-mt">{{ $conflicts->links() }}</div>
@endif

@endsection
