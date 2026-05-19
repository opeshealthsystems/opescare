@extends('layouts.lite')
@section('title', 'Offline Events')

@section('content')

<div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
    <a href="{{ route('portals.lite.devices') }}" style="color:#64748b;">
        <i data-lucide="arrow-left" style="width:18px;height:18px;"></i>
    </a>
    <div>
        <h1 class="lite-page-title">Offline Events — {{ $device->device_name }}</h1>
        <p class="lite-page-sub">
            <span class="lite-badge lite-badge--{{ $device->statusColor() }}">{{ ucfirst($device->status) }}</span>
            <span style="margin-left:8px;font-size:0.78rem;font-family:monospace;">{{ substr($device->id, 0, 8) }}…</span>
        </p>
    </div>
</div>

@if($events->isEmpty())
    <div style="text-align:center;padding:40px;color:#94a3b8;">
        <p style="margin:0;">No offline events recorded for this device.</p>
    </div>
@else
    <div class="lite-card">
        <div class="lite-card__body" style="padding:0;overflow-x:auto;">
            <table class="lite-table">
                <thead>
                    <tr>
                        <th>Event Type</th>
                        <th>Status</th>
                        <th>Captured</th>
                        <th>Received</th>
                        <th>Applied</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($events as $ev)
                    <tr>
                        <td style="font-weight:600;font-size:0.85rem;">
                            {{ ucwords(str_replace('_', ' ', $ev->event_type)) }}
                        </td>
                        <td>
                            <span class="lite-badge lite-badge--{{ match($ev->status) {
                                'applied'    => 'success',
                                'queued'     => 'warning',
                                'processing' => 'info',
                                'conflict'   => 'danger',
                                'rejected'   => 'danger',
                                default      => 'default',
                            } }}">{{ ucfirst($ev->status) }}</span>
                        </td>
                        <td style="font-size:0.79rem;color:#6b7280;">
                            {{ $ev->captured_at?->format('d M H:i') ?? '—' }}
                        </td>
                        <td style="font-size:0.79rem;color:#6b7280;">
                            {{ $ev->received_at?->format('d M H:i') ?? '—' }}
                        </td>
                        <td style="font-size:0.79rem;color:#16a34a;">
                            {{ $ev->applied_at?->format('d M H:i') ?? '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div style="margin-top:10px;">{{ $events->links() }}</div>
@endif

@endsection
