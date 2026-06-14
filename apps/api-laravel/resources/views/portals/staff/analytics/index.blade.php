@extends('layouts.portal')

@section('title', 'Analytics Dashboard')

@section('sidebar_role_badge')
<div class="sidebar-role-badge">{{ __('public.staff_portal.role_clinical_staff', [], app()->getLocale()) ?: 'Clinical Staff' }}</div>
@endsection
@section('sidebar_user_role', __('public.staff_portal.role_clinical_staff', [], app()->getLocale()) ?: 'Clinical Staff')

@section('sidebar_nav')
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Overview</div>
    <a href="{{ route('portals.staff') }}" class="sidebar-link">
        <i data-lucide="layout-dashboard"></i>
        <span>{{ __('public.portal.nav_dashboard', [], app()->getLocale()) ?: 'Dashboard' }}</span>
    </a>
    <a href="{{ route('portals.staff.analytics') }}" class="sidebar-link active">
        <i data-lucide="bar-chart-2"></i>
        <span>{{ __('public.portal.nav_analytics', [], app()->getLocale()) ?: 'Analytics' }}</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Clinical</div>
    <a href="{{ route('portals.staff.appointments') }}" class="sidebar-link">
        <i data-lucide="calendar-check-2"></i>
        <span>{{ __('public.portal.nav_appointments', [], app()->getLocale()) ?: 'Appointments' }}</span>
    </a>
    <a href="{{ route('portals.staff.queue') }}" class="sidebar-link">
        <i data-lucide="list-ordered"></i>
        <span>{{ __('public.portal.nav_queue', [], app()->getLocale()) ?: 'Patient Queue' }}</span>
    </a>
    <a href="{{ route('portals.staff.visits') }}" class="sidebar-link">
        <i data-lucide="stethoscope"></i>
        <span>{{ __('public.portal.nav_visits', [], app()->getLocale()) ?: 'Visits' }}</span>
    </a>
    <a href="{{ route('portals.staff.cdss') }}" class="sidebar-link {{ request()->routeIs('portals.staff.cdss*') ? 'active' : '' }}">
        <i data-lucide="brain-circuit"></i>
        <span>Clinical Alerts</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">HR & Staff</div>
    <a href="{{ route('portals.staff.hr.directory') }}" class="sidebar-link">
        <i data-lucide="users"></i>
        <span>{{ __('public.portal.nav_staff_directory', [], app()->getLocale()) ?: 'Directory' }}</span>
    </a>
    <a href="{{ route('portals.staff.hr.roster') }}" class="sidebar-link">
        <i data-lucide="calendar-range"></i>
        <span>{{ __('public.portal.nav_staff_roster', [], app()->getLocale()) ?: 'Duty Roster' }}</span>
    </a>
    <a href="{{ route('portals.staff.hr.leave') }}" class="sidebar-link">
        <i data-lucide="plane-takeoff"></i>
        <span>{{ __('public.portal.nav_staff_leave', [], app()->getLocale()) ?: 'Leave' }}</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Inventory</div>
    <a href="{{ route('portals.staff.inventory.pharmacy') }}" class="sidebar-link">
        <i data-lucide="pill"></i>
        <span>{{ __('public.portal.nav_inventory_pharmacy', [], app()->getLocale()) ?: 'Pharmacy' }}</span>
    </a>
    <a href="{{ route('portals.staff.inventory.blood') }}" class="sidebar-link">
        <i data-lucide="droplets"></i>
        <span>{{ __('public.portal.nav_inventory_blood', [], app()->getLocale()) ?: 'Blood Bank' }}</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Supply Chain</div>
    <a href="{{ route('portals.staff.supply') }}" class="sidebar-link {{ request()->routeIs('portals.staff.supply*') ? 'active' : '' }}">
        <i data-lucide="package"></i>
        <span>Supply Chain</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Operations</div>
    <a href="{{ route('portals.staff.billing') }}" class="sidebar-link">
        <i data-lucide="receipt"></i>
        <span>{{ __('public.portal.nav_billing', [], app()->getLocale()) ?: 'Billing' }}</span>
    </a>
    <a href="{{ route('portals.staff.support') }}" class="sidebar-link">
        <i data-lucide="headset"></i>
        <span>{{ __('public.portal.nav_support', [], app()->getLocale()) ?: 'Support' }}</span>
    </a>
    <a href="{{ route('portals.staff.data_import.index') }}" class="sidebar-link">
        <i data-lucide="upload-cloud"></i>
        <span>{{ __('public.portal.nav_data_import', [], app()->getLocale()) ?: 'Data Import' }}</span>
    </a>
    <a href="{{ route('portals.staff.search') }}" class="sidebar-link {{ request()->routeIs('portals.staff.search') ? 'active' : '' }}">
        <i data-lucide="search"></i>
        <span>{{ __('public.portal.nav_search', [], app()->getLocale()) ?: 'Global Search' }}</span>
    </a>
    <a href="{{ route('portals.staff.files.index') }}" class="sidebar-link {{ request()->routeIs('portals.staff.files*') ? 'active' : '' }}">
        <i data-lucide="paperclip"></i>
        <span>{{ __('public.portal.nav_files', [], app()->getLocale()) ?: 'Files & Attachments' }}</span>
    </a>
    <a href="{{ route('portals.staff.wards') }}" class="sidebar-link {{ request()->routeIs('portals.staff.wards*') ? 'active' : '' }}">
        <i data-lucide="bed"></i>
        <span>{{ __('public.portal.nav_wards', [], app()->getLocale()) ?: 'Wards & Beds' }}</span>
    </a>
</div>
@endsection

@section('breadcrumb_home', __('public.staff_portal.title', [], app()->getLocale()) ?: 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Analytics')

@section('content')

@php
    $visits       = $snapshot['visits'];
    $appointments = $snapshot['appointments'];
    $revenue      = $snapshot['revenue'];
    $patients     = $snapshot['patients'];
    $staff        = $snapshot['staff'];
    $inventory    = $snapshot['inventory'];
    $visitTrend   = $snapshot['visit_trend'];
    $revTrend     = $snapshot['revenue_trend'];
    $periodFrom   = $snapshot['period']['from'];
    $periodTo     = $snapshot['period']['to'];
@endphp

@include('portals.staff.analytics._tabs')

<div class="page-head">
    <h2>Analytics dashboard</h2>
    <div class="page-head__spacer"></div>
    {{-- Period Selector --}}
    <form method="GET" action="{{ route('portals.staff.analytics') }}" class="filter-bar filter-bar--flush">
        @foreach(['7d' => '7 Days', '30d' => '30 Days', '90d' => '90 Days', '1y' => '1 Year'] as $val => $label)
            <button type="submit" name="period" value="{{ $val }}"
                class="btn btn-sm {{ $period === $val ? 'btn-primary' : 'btn-ghost' }}">
                {{ $label }}
            </button>
        @endforeach
    </form>
</div>
<p class="page-subtitle mb-6">
    {{ \Carbon\Carbon::parse($periodFrom)->format('M d') }} – {{ \Carbon\Carbon::parse($periodTo)->format('M d, Y') }}
</p>

{{-- ── Row 1: KPI Cards ───────────────────────────────────────── --}}
<div class="stat-grid mb-6">

    {{-- Visits --}}
    <div class="stat-card stat-card--primary">
        <div class="stat-card__head"><i data-lucide="stethoscope"></i> <span class="stat-card__label">Visits</span></div>
        <div class="stat-card__value">{{ number_format($visits['total']) }}</div>
        <div class="stat-card__hint">{{ $visits['completed'] }} done · {{ $visits['active'] }} active</div>
    </div>

    {{-- Appointments --}}
    <div class="stat-card stat-card--primary">
        <div class="stat-card__head"><i data-lucide="calendar-check-2"></i> <span class="stat-card__label">Appointments</span></div>
        <div class="stat-card__value">{{ number_format($appointments['total']) }}</div>
        <div class="stat-card__hint">
            @if($appointments['show_rate'] !== null)
                {{ $appointments['show_rate'] }}% show rate
            @else
                No data
            @endif
        </div>
    </div>

    {{-- Revenue --}}
    <div class="stat-card stat-card--success">
        <div class="stat-card__head"><i data-lucide="trending-up"></i> <span class="stat-card__label">Collected</span></div>
        <div class="stat-card__value">{{ number_format($revenue['total_collected'], 0) }}</div>
        <div class="stat-card__hint">
            of {{ number_format($revenue['total_invoiced'], 0) }} invoiced
            @if($revenue['collection_rate'] !== null)
                · {{ $revenue['collection_rate'] }}%
            @endif
        </div>
    </div>

    {{-- Outstanding --}}
    <div class="stat-card {{ $revenue['total_outstanding'] > 0 ? 'stat-card--warning' : 'stat-card--success' }}">
        <div class="stat-card__head"><i data-lucide="clock"></i> <span class="stat-card__label">Outstanding</span></div>
        <div class="stat-card__value">{{ number_format($revenue['total_outstanding'], 0) }}</div>
        <div class="stat-card__hint">{{ $revenue['overdue_count'] }} overdue invoices</div>
    </div>

    {{-- New Patients --}}
    <div class="stat-card stat-card--primary">
        <div class="stat-card__head"><i data-lucide="user-plus"></i> <span class="stat-card__label">New Patients</span></div>
        <div class="stat-card__value">{{ number_format($patients['new_in_period']) }}</div>
        <div class="stat-card__hint">{{ number_format($patients['total_registered']) }} total registered</div>
    </div>

    {{-- Staff on Leave --}}
    <div class="stat-card {{ $staff['on_leave'] > 0 ? 'stat-card--warning' : '' }}">
        <div class="stat-card__head"><i data-lucide="plane-takeoff"></i> <span class="stat-card__label">Staff</span></div>
        <div class="stat-card__value">{{ number_format($staff['active']) }}</div>
        <div class="stat-card__hint">
            active of {{ $staff['total'] }}
            @if($staff['on_leave'] > 0)
                · {{ $staff['on_leave'] }} on leave
            @endif
        </div>
    </div>

</div>

{{-- ── Row 2: Visit Trend + Appointment Breakdown ─────────────── --}}
<div class="grid-2 mb-6">

    {{-- Visit Trend Chart --}}
    <div class="panel">
        <div class="panel-header">
            <h3 class="panel-title">
                <i data-lucide="activity"></i>
                Visit Trend
            </h3>
        </div>
        <div class="panel-body">
            @if(empty($visitTrend))
                <div class="empty-state">
                    <div class="empty-state-icon"><i data-lucide="bar-chart-2"></i></div>
                    <p>No visit data for this period.</p>
                </div>
            @else
                <div style="height:160px;position:relative;">
                    <canvas id="visitTrendChart"></canvas>
                </div>
            @endif
        </div>
    </div>

    {{-- Appointment Breakdown --}}
    <div class="panel">
        <div class="panel-header">
            <h3 class="panel-title">
                <i data-lucide="calendar-check-2"></i>
                Appointments
            </h3>
        </div>
        <div class="panel-body">
            @if($appointments['total'] === 0)
                <p class="td-muted">No appointments.</p>
            @else
                <div class="breakdown">
                @foreach([
                    ['label'=>'Completed', 'key'=>'completed', 'fill'=>''],
                    ['label'=>'Confirmed', 'key'=>'confirmed', 'fill'=>''],
                    ['label'=>'Cancelled', 'key'=>'cancelled', 'fill'=>'breakdown__fill--danger'],
                    ['label'=>'No Show',   'key'=>'no_show',   'fill'=>'breakdown__fill--warning'],
                ] as $row)
                @php $val = $appointments[$row['key']]; $pct = $appointments['total'] > 0 ? round($val / $appointments['total'] * 100) : 0; @endphp
                <div class="breakdown__row">
                    <span class="breakdown__label">{{ $row['label'] }}</span>
                    <div class="breakdown__track"><div class="breakdown__fill {{ $row['fill'] }}" style="width:{{ $pct }}%;"></div></div>
                    <span class="breakdown__value">{{ $val }} ({{ $pct }}%)</span>
                </div>
                @endforeach
                </div>
            @endif
        </div>
    </div>

</div>

{{-- ── Row 3: Revenue Trend + Visit by Type ───────────────────── --}}
<div class="grid-2 mb-6">

    {{-- Revenue Trend --}}
    <div class="panel">
        <div class="panel-header">
            <h3 class="panel-title">
                <i data-lucide="trending-up"></i>
                Revenue Trend
            </h3>
        </div>
        <div class="panel-body">
            @if(empty($revTrend))
                <div class="empty-state">
                    <div class="empty-state-icon"><i data-lucide="trending-up"></i></div>
                    <p>No revenue data for this period.</p>
                </div>
            @else
                <div style="height:160px;position:relative;">
                    <canvas id="revenueTrendChart"></canvas>
                </div>
            @endif
        </div>
    </div>

    {{-- Visits by Type --}}
    <div class="panel">
        <div class="panel-header">
            <h3 class="panel-title">
                <i data-lucide="stethoscope"></i>
                Visit Types
            </h3>
        </div>
        <div class="panel-body">
            @if(empty($visits['by_type']))
                <p class="td-muted">No visits.</p>
            @else
                @php $maxType = max(array_values($visits['by_type'])); @endphp
                <div class="breakdown">
                @foreach($visits['by_type'] as $type => $count)
                @php $pct = $maxType > 0 ? round($count / $maxType * 100) : 0; @endphp
                <div class="breakdown__row">
                    <span class="breakdown__label">{{ ucwords(str_replace('_',' ',$type)) }}</span>
                    <div class="breakdown__track"><div class="breakdown__fill" style="width:{{ $pct }}%;"></div></div>
                    <span class="breakdown__value">{{ $count }}</span>
                </div>
                @endforeach
                </div>
            @endif
        </div>
    </div>

</div>

{{-- ── Row 4: Staff Distribution + Inventory Alerts ───────────── --}}
<div class="grid-2 mb-6">

    {{-- Staff by Category --}}
    <div class="panel">
        <div class="panel-header">
            <h3 class="panel-title">
                <i data-lucide="users"></i>
                Staff Distribution
            </h3>
        </div>
        <div class="panel-body">
            @if(empty($staff['by_category']))
                <p class="td-muted">No staff records.</p>
            @else
                @php $totalByCategory = array_sum($staff['by_category']); @endphp
                <div class="breakdown">
                @foreach($staff['by_category'] as $cat => $count)
                @php $pct = $totalByCategory > 0 ? round($count / $totalByCategory * 100) : 0; @endphp
                <div class="breakdown__row">
                    <span class="breakdown__label">{{ ucfirst($cat) }}</span>
                    <div class="breakdown__track"><div class="breakdown__fill" style="width:{{ $pct }}%;"></div></div>
                    <span class="breakdown__value">{{ $count }}</span>
                </div>
                @endforeach
                </div>
                @if($staff['pending_leaves'] > 0)
                <div class="alert alert-warning mt-6">
                    <i data-lucide="clock"></i>
                    <div>
                        {{ $staff['pending_leaves'] }} pending leave request{{ $staff['pending_leaves'] !== 1 ? 's' : '' }}
                        <a href="{{ route('portals.staff.hr.leave') }}">Review →</a>
                    </div>
                </div>
                @endif
            @endif
        </div>
    </div>

    {{-- Inventory Alerts --}}
    <div class="panel">
        <div class="panel-header">
            <h3 class="panel-title">
                <i data-lucide="package"></i>
                Inventory Alerts
            </h3>
        </div>
        <div class="panel-body">
            @php
                $alerts = [];
                if ($inventory['pharma_out'] > 0)     $alerts[] = ['type'=>'danger',  'msg'=> $inventory['pharma_out']    . ' medicine(s) out of stock', 'url'=> route('portals.staff.inventory.pharmacy')];
                if ($inventory['pharma_low'] > 0)     $alerts[] = ['type'=>'warning', 'msg'=> $inventory['pharma_low']    . ' medicine(s) low stock', 'url'=> route('portals.staff.inventory.pharmacy')];
                if ($inventory['pharma_expired'] > 0) $alerts[] = ['type'=>'danger',  'msg'=> $inventory['pharma_expired']. ' expired medicine(s)', 'url'=> route('portals.staff.inventory.pharmacy')];
                if ($inventory['blood_total_units'] === 0) $alerts[] = ['type'=>'danger', 'msg'=>'Blood bank is empty', 'url'=> route('portals.staff.inventory.blood')];
                elseif ($inventory['blood_groups'] < 4)    $alerts[] = ['type'=>'warning','msg'=> $inventory['blood_groups'] . ' blood group(s) in stock', 'url'=> route('portals.staff.inventory.blood')];
            @endphp

            @if(empty($alerts))
                <div class="empty-state">
                    <div class="empty-state-icon"><i data-lucide="check-circle"></i></div>
                    <p>All inventory levels look good.</p>
                </div>
            @else
                @foreach($alerts as $alert)
                <div class="alert {{ $alert['type']==='danger' ? 'alert-danger' : 'alert-warning' }} mb-6">
                    <i data-lucide="{{ $alert['type']==='danger' ? 'alert-circle' : 'alert-triangle' }}"></i>
                    <div class="flex-between">
                        <span>{{ $alert['msg'] }}</span>
                        <a href="{{ $alert['url'] }}">Fix →</a>
                    </div>
                </div>
                @endforeach
            @endif

            <div class="row-actions-inline mt-6">
                <a href="{{ route('portals.staff.inventory.pharmacy') }}" class="btn btn-ghost btn-xs">
                    <i data-lucide="pill"></i> Pharmacy
                </a>
                <a href="{{ route('portals.staff.inventory.blood') }}" class="btn btn-ghost btn-xs">
                    <i data-lucide="droplets"></i> Blood Bank
                </a>
            </div>
        </div>
    </div>

</div>

{{-- ── Row 5: Insurance Revenue breakdown ─────────────────────── --}}
@if($revenue['insurance_covered'] > 0)
<div class="panel mb-6">
    <div class="panel-header">
        <h3 class="panel-title">
            <i data-lucide="shield-check"></i>
            Revenue Breakdown
        </h3>
    </div>
    <div class="panel-body">
        <div class="stat-grid">
        @foreach([
            ['label'=>'Total Invoiced',    'value'=> $revenue['total_invoiced'],    'mod'=>'stat-card--primary'],
            ['label'=>'Collected',         'value'=> $revenue['total_collected'],   'mod'=>'stat-card--success'],
            ['label'=>'Insurance Covered', 'value'=> $revenue['insurance_covered'], 'mod'=>'stat-card--teal'],
            ['label'=>'Outstanding',       'value'=> $revenue['total_outstanding'], 'mod'=>'stat-card--warning'],
        ] as $item)
        <div class="stat-card {{ $item['mod'] }}">
            <div class="stat-card__value">{{ number_format($item['value'], 2) }}</div>
            <div class="stat-card__label">{{ $item['label'] }}</div>
        </div>
        @endforeach
        </div>
    </div>
</div>
@endif

@endsection

@section('scripts')
@if(!empty($visitTrend))
<script src="{{ asset('js/chart.umd.min.js') }}"></script>
<script>
(function() {
    var visitLabels = @json(array_keys($visitTrend));
    var visitData   = @json(array_values($visitTrend));

    var vCtx = document.getElementById('visitTrendChart');
    if (vCtx) {
        new Chart(vCtx, {
            type: 'bar',
            data: {
                labels: visitLabels,
                datasets: [{
                    label: 'Visits',
                    data: visitData,
                    backgroundColor: 'rgba(15,76,129,.6)',
                    borderColor: 'rgba(15,76,129,1)',
                    borderWidth: 1,
                    borderRadius: 3,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { maxTicksLimit: 10, font: { size: 10 } }, grid: { display: false } },
                    y: { beginAtZero: true, ticks: { font: { size: 10 } } }
                }
            }
        });
    }

    @if(!empty($revTrend))
    var revLabels    = @json(array_keys($revTrend));
    var revCollected = @json(array_map(fn($r) => $r['collected'], $revTrend));
    var revInvoiced  = @json(array_map(fn($r) => $r['invoiced'],  $revTrend));

    var rCtx = document.getElementById('revenueTrendChart');
    if (rCtx) {
        new Chart(rCtx, {
            type: 'line',
            data: {
                labels: revLabels,
                datasets: [
                    {
                        label: 'Collected',
                        data: revCollected,
                        borderColor: 'rgba(16,185,129,1)',
                        backgroundColor: 'rgba(16,185,129,.1)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 2,
                    },
                    {
                        label: 'Invoiced',
                        data: revInvoiced,
                        borderColor: 'rgba(15,76,129,.6)',
                        backgroundColor: 'transparent',
                        borderDash: [4,3],
                        tension: 0.3,
                        pointRadius: 2,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { font: { size: 11 } } } },
                scales: {
                    x: { ticks: { maxTicksLimit: 10, font: { size: 10 } }, grid: { display: false } },
                    y: { beginAtZero: true, ticks: { font: { size: 10 } } }
                }
            }
        });
    }
    @endif
})();
</script>
@endif
@endsection
