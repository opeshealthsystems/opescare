@extends('layouts.lite')
@section('title', 'Offline Events')

@section('content')

<div class="lite-page-head--plain">
    <a href="{{ route('portals.lite.devices') }}" class="lite-back">
        <i data-lucide="arrow-left"></i>
    </a>
    <div>
        <h1 class="lite-page-title">Offline events — {{ $device->device_name }}</h1>
        <p class="lite-page-sub">
            <span class="lite-badge lite-badge--{{ $device->statusColor() }}">{{ ucfirst($device->status) }}</span>
            <span class="lite-sub-id">{{ substr($device->id, 0, 8) }}…</span>
        </p>
    </div>
</div>

@if($events->isEmpty())
    <div class="lite-empty">
        <p>No offline events recorded for this device.</p>
    </div>
@else
    <div class="lite-card">
        <div class="lite-card__body lite-card__body--scroll">
            <table class="lite-table">
                <thead>
                    <tr><th>Event type</th><th>Status</th><th>Captured</th><th>Received</th><th>Applied</th></tr>
                </thead>
                <tbody>
                    @foreach($events as $ev)
                    <tr>
                        <td class="lite-td-strong">{{ ucwords(str_replace('_', ' ', $ev->event_type)) }}</td>
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
                        <td>{{ $ev->captured_at?->format('d M H:i') ?? '—' }}</td>
                        <td>{{ $ev->received_at?->format('d M H:i') ?? '—' }}</td>
                        <td class="lite-applied">{{ $ev->applied_at?->format('d M H:i') ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="lite-mt">{{ $events->links() }}</div>
@endif

@endsection
