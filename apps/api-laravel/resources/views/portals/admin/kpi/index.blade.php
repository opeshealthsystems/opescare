@extends('layouts.portal')
@section('title', 'KPI Dashboard')
@section('sidebar') @include('portals.admin.control_center._sidebar') @endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">
                <i data-lucide="bar-chart-2" style="width:22px;height:22px;vertical-align:middle;margin-right:8px;color:#7c3aed;"></i>
                KPI Dashboard
            </h1>
            <p class="portal-page-subtitle">Key performance indicators — today's snapshots</p>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
            <form method="POST" action="{{ route('portals.admin.kpi.recompute') }}" style="display:inline;">
                @csrf
                <input type="hidden" name="date" value="{{ now()->toDateString() }}">
                <button type="submit" class="btn btn--outline btn--sm">
                    <i data-lucide="refresh-cw" style="width:13px;height:13px;"></i> Refresh
                </button>
            </form>
            <a href="{{ route('portals.admin.kpi.trend') }}" class="btn btn--outline btn--sm">
                <i data-lucide="trending-up" style="width:13px;height:13px;"></i> Trends
            </a>
        </div>
    </div>

    @if(session('success'))
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 16px;margin-bottom:16px;color:#166534;font-size:0.88rem;">
        ✓ {{ session('success') }}
    </div>
    @endif

    {{-- Platform Summary Strip --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:12px;margin-bottom:20px;">
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#eff6ff;"><i data-lucide="users" style="color:#2563eb;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value">{{ number_format($platformSummary['total_patients']) }}</div><div class="stat-card__label">Patients Today</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#f0fdf4;"><i data-lucide="stethoscope" style="color:#16a34a;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value">{{ number_format($platformSummary['total_visits']) }}</div><div class="stat-card__label">Visits Today</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#fff7ed;"><i data-lucide="flask-conical" style="color:#d97706;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value">{{ number_format($platformSummary['total_lab_orders']) }}</div><div class="stat-card__label">Lab Orders</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#fdf4ff;"><i data-lucide="pill" style="color:#9333ea;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value">{{ number_format($platformSummary['total_prescriptions']) }}</div><div class="stat-card__label">Prescriptions</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#f0fdf4;"><i data-lucide="receipt" style="color:#16a34a;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value">{{ number_format($platformSummary['total_invoices']) }}</div><div class="stat-card__label">Invoices</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#eff6ff;"><i data-lucide="building-2" style="color:#2563eb;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value">{{ $platformSummary['active_facilities'] }}</div><div class="stat-card__label">Active Facilities</div></div>
        </div>
    </div>

    {{-- Category Filter --}}
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
        <a href="{{ route('portals.admin.kpi.index') }}"
           class="btn {{ !$category ? 'btn--primary' : 'btn--outline' }} btn--sm">All</a>
        @foreach($categories as $cat)
        <a href="{{ route('portals.admin.kpi.index', ['category' => $cat]) }}"
           class="btn {{ $category === $cat ? 'btn--primary' : 'btn--outline' }} btn--sm" style="text-transform:capitalize;">
           {{ $cat }}
        </a>
        @endforeach
    </div>

    {{-- KPI Metric Cards --}}
    @if($snapshots->isEmpty())
    <div class="portal-card">
        <div class="portal-card__body" style="text-align:center;padding:40px;color:#9ca3af;">
            No KPI data available. Click <strong>Refresh</strong> to compute today's snapshots.
        </div>
    </div>
    @else
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px;">
        @foreach($snapshots as $snapshot)
        @php $def = $snapshot->metricDefinition; @endphp
        <div class="portal-card" style="border-left:4px solid {{ $snapshot->status === 'critical' ? '#dc2626' : ($snapshot->status === 'warning' ? '#d97706' : '#16a34a') }};">
            <div class="portal-card__body" style="padding:16px 18px;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:6px;">
                    <span style="font-size:0.78rem;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.04em;">
                        {{ $def->name }}
                    </span>
                    <span class="badge {{ $snapshot->statusBadgeClass() }}" style="font-size:0.68rem;">
                        {{ ucfirst($snapshot->status) }}
                    </span>
                </div>
                <div style="font-size:1.7rem;font-weight:800;color:#111827;line-height:1.1;margin-bottom:4px;">
                    {{ $snapshot->formattedValue() }}
                </div>
                @if($snapshot->change_pct !== null)
                <div style="font-size:0.78rem;color:{{ $snapshot->change_pct >= 0 ? '#16a34a' : '#dc2626' }};">
                    {{ $snapshot->change_pct >= 0 ? '▲' : '▼' }} {{ abs($snapshot->change_pct) }}% vs yesterday
                </div>
                @endif
                @if($def->target_value)
                <div style="font-size:0.75rem;color:#9ca3af;margin-top:4px;">
                    Target: {{ number_format($def->target_value, $def->display_format === 'percentage' ? 1 : 0) }}{{ $def->display_format === 'percentage' ? '%' : '' }}
                </div>
                @endif
                <div style="margin-top:10px;">
                    <a href="{{ route('portals.admin.kpi.trend', ['metric' => $def->slug]) }}"
                       style="font-size:0.75rem;color:#7c3aed;text-decoration:none;">View trend →</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Export Panel --}}
    <div class="portal-card" style="margin-top:24px;">
        <div class="portal-card__header">
            <h2 class="portal-card__title">Export KPI Data</h2>
        </div>
        <div class="portal-card__body" style="padding:16px 20px;">
            <form method="POST" action="{{ route('portals.admin.kpi.export') }}" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">
                @csrf
                <div>
                    <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:4px;">Metrics</label>
                    <select name="metric_slugs[]" multiple
                            style="min-width:180px;max-height:80px;padding:6px 8px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.82rem;">
                        @foreach(\App\Models\MetricDefinition::active()->orderBy('name')->get() as $def)
                        <option value="{{ $def->slug }}">{{ $def->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:4px;">From</label>
                    <input type="date" name="period_from" value="{{ now()->subDays(30)->toDateString() }}"
                           style="padding:6px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.82rem;">
                </div>
                <div>
                    <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:4px;">To</label>
                    <input type="date" name="period_to" value="{{ now()->toDateString() }}"
                           style="padding:6px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.82rem;">
                </div>
                <div>
                    <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:4px;">Format</label>
                    <select name="export_type"
                            style="padding:6px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.82rem;">
                        <option value="csv">CSV</option>
                        <option value="json">JSON</option>
                    </select>
                </div>
                <button type="submit" class="btn btn--primary btn--sm">
                    <i data-lucide="download" style="width:13px;height:13px;"></i> Request Export
                </button>
            </form>

            @if($recentExports->isNotEmpty())
            <div style="margin-top:16px;border-top:1px solid #f3f4f6;padding-top:12px;">
                <div style="font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:8px;">Recent Exports</div>
                <table class="portal-table" style="font-size:0.8rem;">
                    <thead><tr><th>Date</th><th>Type</th><th>Period</th><th>Status</th></tr></thead>
                    <tbody>
                    @foreach($recentExports as $exp)
                    <tr>
                        <td>{{ $exp->requested_at->format('d M Y H:i') }}</td>
                        <td>{{ strtoupper($exp->export_type) }}</td>
                        <td>{{ $exp->period_from->format('d M') }} – {{ $exp->period_to->format('d M Y') }}</td>
                        <td><span class="badge badge--{{ $exp->status === 'ready' ? 'success' : ($exp->status === 'failed' ? 'danger' : 'warning') }}" style="font-size:0.7rem;">{{ ucfirst($exp->status) }}</span></td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
