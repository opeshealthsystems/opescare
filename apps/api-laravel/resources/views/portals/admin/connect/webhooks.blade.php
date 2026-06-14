@extends('layouts.portal')

@section('title', 'Webhooks — Connect Suite')

@section('sidebar')
    @include('portals.admin.connect._sidebar')
@endsection

@section('content')

<div class="page-head">
    <h2><i data-lucide="webhook"></i> Webhooks</h2>
    <div class="page-head__spacer"></div>
</div>
<p class="td-muted mb-6">Monitor webhook subscriptions and delivery activity</p>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

{{-- Delivery Stats --}}
<div class="stat-grid mb-6">
    <div class="stat-card stat-card--success">
        <div class="stat-card__head"><i data-lucide="check-circle"></i></div>
        <div class="stat-card__value">{{ $stats['delivered'] }}</div>
        <div class="stat-card__label">Delivered</div>
    </div>
    <div class="stat-card stat-card--danger">
        <div class="stat-card__head"><i data-lucide="x-circle"></i></div>
        <div class="stat-card__value">{{ $stats['failed'] }}</div>
        <div class="stat-card__label">Failed</div>
    </div>
    <div class="stat-card stat-card--warning">
        <div class="stat-card__head"><i data-lucide="clock"></i></div>
        <div class="stat-card__value">{{ $stats['pending'] }}</div>
        <div class="stat-card__label">Pending</div>
    </div>
</div>

{{-- Subscriptions --}}
<div class="panel mb-6">
    <div class="panel-header"><h3 class="panel-title"><i data-lucide="rss"></i> Webhook Subscriptions</h3></div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Client ID</th>
                    <th>Endpoint URL</th>
                    <th>Events</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th class="row-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subscriptions as $sub)
                    <tr>
                        <td data-label="Client ID"><span class="code-token">{{ $sub->client_id }}</span></td>
                        <td data-label="Endpoint URL"><span class="td-muted" title="{{ $sub->endpoint_url }}">{{ Str::limit($sub->endpoint_url, 60) }}</span></td>
                        <td data-label="Events">
                            @foreach(array_slice($sub->events ?? [], 0, 3) as $event)
                                <span class="badge badge-success">{{ $event }}</span>
                            @endforeach
                            @if(count($sub->events ?? []) > 3)<span class="td-muted">+{{ count($sub->events) - 3 }} more</span>@endif
                        </td>
                        <td data-label="Status">
                            <span class="badge badge-{{ $sub->status === 'active' ? 'success' : 'warning' }}">{{ $sub->status }}</span>
                        </td>
                        <td data-label="Created">{{ $sub->created_at->format('d M Y') }}</td>
                        <td class="row-actions" data-label="Actions">
                            <form method="POST" action="{{ route('portals.admin.connect.webhooks.toggle', $sub->id) }}" class="inline-form">
                                @csrf
                                <button class="btn btn-{{ $sub->status === 'active' ? 'warning' : 'success' }} btn-sm" title="{{ $sub->status === 'active' ? 'Pause' : 'Resume' }}">
                                    <i data-lucide="{{ $sub->status === 'active' ? 'pause' : 'play' }}"></i> {{ $sub->status === 'active' ? 'Pause' : 'Resume' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="td-muted empty-cell">No webhook subscriptions found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($subscriptions->hasPages())
        <div class="panel-body">{{ $subscriptions->links() }}</div>
    @endif
</div>

{{-- Delivery Log --}}
<div class="panel">
    <div class="panel-header">
        <h3 class="panel-title"><i data-lucide="activity"></i> Recent Delivery Log <span class="td-muted">(Last 30)</span></h3>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Endpoint</th>
                    <th>Status</th>
                    <th>HTTP</th>
                    <th>Attempts</th>
                    <th>Delivered At</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deliveryLogs as $log)
                    <tr>
                        <td data-label="Event"><span class="code-token">{{ $log->event_type ?? '—' }}</span></td>
                        <td data-label="Endpoint"><span class="td-muted">{{ Str::limit($log->endpoint_url ?? '', 40) }}</span></td>
                        <td data-label="Status">
                            @php
                                $statusBadge = match($log->status ?? '') {
                                    'delivered' => 'success',
                                    'failed'    => 'danger',
                                    default     => 'warning',
                                };
                            @endphp
                            <span class="badge badge-{{ $statusBadge }}">{{ $log->status ?? 'pending' }}</span>
                        </td>
                        <td data-label="HTTP">
                            @if($log->http_status_code ?? null)
                                <span class="badge badge-{{ ($log->http_status_code >= 200 && $log->http_status_code < 300) ? 'success' : 'danger' }}">{{ $log->http_status_code }}</span>
                            @else
                                <span class="td-muted">—</span>
                            @endif
                        </td>
                        <td data-label="Attempts">{{ $log->attempt_count ?? 1 }}</td>
                        <td data-label="Delivered At">{{ $log->delivered_at ? \Carbon\Carbon::parse($log->delivered_at)->diffForHumans() : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="td-muted empty-cell">No delivery logs yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
