@extends('layouts.portal')
@section('title', 'API Usage Analytics')
@section('sidebar_nav') @include('portals.developer._sidebar') @endsection

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">API Usage Analytics</h1>
        <p class="page-subtitle">Request volume, error rates, and response times across your apps — last 30 days.</p>
    </div>
    <form method="GET" style="display:flex;gap:.5rem;align-items:center;">
        <label class="form-label" style="margin:0;white-space:nowrap;">Period</label>
        <select name="period" class="form-control form-control-sm" onchange="this.form.submit()">
            <option value="daily" {{ $period === 'daily' ? 'selected' : '' }}>Daily</option>
            <option value="hourly" {{ $period === 'hourly' ? 'selected' : '' }}>Hourly</option>
            <option value="monthly" {{ $period === 'monthly' ? 'selected' : '' }}>Monthly</option>
        </select>
    </form>
</div>

{{-- Totals --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:1rem;margin-bottom:1.5rem;">
    <div class="stat-card">
        <div class="stat-card__icon" style="background:#dbeafe;color:#1d4ed8;"><i data-lucide="zap"></i></div>
        <div class="stat-card__val">{{ number_format($totals['requests']) }}</div>
        <div class="stat-card__label">Total Requests</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__icon" style="background:#fee2e2;color:#b91c1c;"><i data-lucide="alert-triangle"></i></div>
        <div class="stat-card__val">{{ number_format($totals['errors']) }}</div>
        <div class="stat-card__label">Total Errors</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__icon" style="background:#dcfce7;color:#15803d;"><i data-lucide="percent"></i></div>
        <div class="stat-card__val">
            {{ $totals['requests'] > 0 ? round(($totals['errors'] / $totals['requests']) * 100, 1) : 0 }}%
        </div>
        <div class="stat-card__label">Error Rate</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__icon" style="background:#fef3c7;color:#b45309;"><i data-lucide="timer"></i></div>
        <div class="stat-card__val">{{ round($totals['avg_ms']) }}ms</div>
        <div class="stat-card__label">Avg Response</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__icon" style="background:#f3e8ff;color:#7c3aed;"><i data-lucide="cpu"></i></div>
        <div class="stat-card__val">{{ $clients->count() }}</div>
        <div class="stat-card__label">Active Apps</div>
    </div>
</div>

@if($metrics->isEmpty())
<div class="auth-alert" style="background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;margin-bottom:1.25rem;">
    <i data-lucide="info"></i>
    <div>No usage data yet for the selected period. Make your first API call to see metrics here.</div>
</div>
@endif

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">

    {{-- Top Endpoints --}}
    <div class="card">
        <div class="card-header" style="font-weight:700;">Top Endpoints</div>
        <div class="card-body" style="padding:0;overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Endpoint</th>
                        <th>Requests</th>
                        <th>Errors</th>
                        <th>Avg ms</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($byEndpoint as $row)
                    <tr>
                        <td style="font-family:monospace;font-size:.8rem;">{{ $row['endpoint'] }}</td>
                        <td style="font-weight:700;">{{ number_format($row['requests']) }}</td>
                        <td style="{{ $row['errors'] > 0 ? 'color:#b91c1c;font-weight:600;' : 'color:#94a3b8;' }}">
                            {{ number_format($row['errors']) }}
                        </td>
                        <td style="color:#64748b;font-size:.83rem;">{{ $row['avg_ms'] }}ms</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align:center;padding:2rem;color:#94a3b8;">No data yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Your Apps --}}
    <div class="card">
        <div class="card-header" style="font-weight:700;display:flex;justify-content:space-between;align-items:center;">
            <span>Your Apps</span>
            <a href="{{ route('portals.developer.apps') }}" class="btn btn-outline btn-sm">Manage</a>
        </div>
        <div class="card-body" style="padding:0;">
            @forelse($clients as $client)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:.75rem 1rem;border-bottom:1px solid #f1f5f9;">
                <div>
                    <div style="font-weight:600;font-size:.875rem;">{{ $client->name }}</div>
                    <div style="font-size:.75rem;color:#64748b;font-family:monospace;">{{ $client->client_id }}</div>
                </div>
                <span class="badge badge-{{ $client->status === 'active' ? 'success' : ($client->status === 'pending' ? 'warning' : 'default') }}">
                    {{ ucfirst($client->status) }}
                </span>
            </div>
            @empty
            <div style="padding:2rem;text-align:center;color:#94a3b8;font-size:.875rem;">
                No apps yet. <a href="{{ route('portals.developer.apps.create') }}">Create your first app</a>.
            </div>
            @endforelse
        </div>
    </div>

</div>

{{-- Recent metrics table --}}
@if($metrics->isNotEmpty())
<div class="card" style="margin-top:1.25rem;overflow:hidden;">
    <div class="card-header" style="font-weight:700;">Recent Metric Records</div>
    <div class="card-body" style="padding:0;overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Endpoint</th>
                    <th>Method</th>
                    <th>Period Start</th>
                    <th>Requests</th>
                    <th>Errors</th>
                    <th>Avg ms</th>
                </tr>
            </thead>
            <tbody>
                @foreach($metrics->take(20) as $m)
                <tr>
                    <td style="font-family:monospace;font-size:.8rem;">{{ $m->endpoint }}</td>
                    <td><span class="badge badge-default">{{ $m->method }}</span></td>
                    <td style="font-size:.8rem;color:#64748b;">{{ $m->period_start?->format('d M Y H:i') }}</td>
                    <td>{{ number_format($m->request_count) }}</td>
                    <td style="{{ $m->error_count > 0 ? 'color:#b91c1c;font-weight:600;' : 'color:#94a3b8;' }}">
                        {{ $m->error_count }}
                    </td>
                    <td style="color:#64748b;font-size:.83rem;">{{ round($m->avg_response_ms) }}ms</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
