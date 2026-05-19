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

<div class="page-header">
    <div>
        <h1 class="page-title">Analytics Dashboard</h1>
        <p class="page-subtitle">
            {{ \Carbon\Carbon::parse($periodFrom)->format('M d') }} – {{ \Carbon\Carbon::parse($periodTo)->format('M d, Y') }}
        </p>
    </div>
    {{-- Period Selector --}}
    <form method="GET" action="{{ route('portals.staff.analytics') }}" style="display:flex;gap:.4rem;align-items:center;">
        @foreach(['7d' => '7 Days', '30d' => '30 Days', '90d' => '90 Days', '1y' => '1 Year'] as $val => $label)
            <button type="submit" name="period" value="{{ $val }}"
                class="btn btn-sm {{ $period === $val ? 'btn-primary' : 'btn-ghost' }}">
                {{ $label }}
            </button>
        @endforeach
    </form>
</div>

{{-- ── Row 1: KPI Cards ───────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(155px,1fr));gap:.75rem;margin-bottom:1.25rem;">

    {{-- Visits --}}
    <div class="panel" style="padding:1.1rem 1rem;">
        <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem;">
            <i data-lucide="stethoscope" style="width:16px;height:16px;color:var(--p-primary);"></i>
            <span style="font-size:.75rem;color:var(--p-text-secondary);font-weight:600;text-transform:uppercase;letter-spacing:.04em;">Visits</span>
        </div>
        <div style="font-size:2rem;font-weight:700;line-height:1;">{{ number_format($visits['total']) }}</div>
        <div style="font-size:.75rem;color:var(--p-text-secondary);margin-top:.25rem;">
            <span style="color:var(--p-success);">{{ $visits['completed'] }} done</span>
            · {{ $visits['active'] }} active
        </div>
    </div>

    {{-- Appointments --}}
    <div class="panel" style="padding:1.1rem 1rem;">
        <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem;">
            <i data-lucide="calendar-check-2" style="width:16px;height:16px;color:var(--p-primary);"></i>
            <span style="font-size:.75rem;color:var(--p-text-secondary);font-weight:600;text-transform:uppercase;letter-spacing:.04em;">Appointments</span>
        </div>
        <div style="font-size:2rem;font-weight:700;line-height:1;">{{ number_format($appointments['total']) }}</div>
        <div style="font-size:.75rem;color:var(--p-text-secondary);margin-top:.25rem;">
            @if($appointments['show_rate'] !== null)
                <span style="color:var(--p-success);">{{ $appointments['show_rate'] }}% show rate</span>
            @else
                <span>No data</span>
            @endif
        </div>
    </div>

    {{-- Revenue --}}
    <div class="panel" style="padding:1.1rem 1rem;">
        <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem;">
            <i data-lucide="trending-up" style="width:16px;height:16px;color:var(--p-success);"></i>
            <span style="font-size:.75rem;color:var(--p-text-secondary);font-weight:600;text-transform:uppercase;letter-spacing:.04em;">Collected</span>
        </div>
        <div style="font-size:2rem;font-weight:700;line-height:1;">{{ number_format($revenue['total_collected'], 0) }}</div>
        <div style="font-size:.75rem;color:var(--p-text-secondary);margin-top:.25rem;">
            of {{ number_format($revenue['total_invoiced'], 0) }} invoiced
            @if($revenue['collection_rate'] !== null)
                · <span style="color:var(--p-success);">{{ $revenue['collection_rate'] }}%</span>
            @endif
        </div>
    </div>

    {{-- Outstanding --}}
    <div class="panel" style="padding:1.1rem 1rem;">
        <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem;">
            <i data-lucide="clock" style="width:16px;height:16px;color:var(--p-warning);"></i>
            <span style="font-size:.75rem;color:var(--p-text-secondary);font-weight:600;text-transform:uppercase;letter-spacing:.04em;">Outstanding</span>
        </div>
        <div style="font-size:2rem;font-weight:700;line-height:1;color:{{ $revenue['total_outstanding'] > 0 ? 'var(--p-warning)' : 'var(--p-success)' }};">
            {{ number_format($revenue['total_outstanding'], 0) }}
        </div>
        <div style="font-size:.75rem;color:var(--p-text-secondary);margin-top:.25rem;">
            {{ $revenue['overdue_count'] }} overdue invoices
        </div>
    </div>

    {{-- New Patients --}}
    <div class="panel" style="padding:1.1rem 1rem;">
        <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem;">
            <i data-lucide="user-plus" style="width:16px;height:16px;color:var(--p-primary);"></i>
            <span style="font-size:.75rem;color:var(--p-text-secondary);font-weight:600;text-transform:uppercase;letter-spacing:.04em;">New Patients</span>
        </div>
        <div style="font-size:2rem;font-weight:700;line-height:1;">{{ number_format($patients['new_in_period']) }}</div>
        <div style="font-size:.75rem;color:var(--p-text-secondary);margin-top:.25rem;">
            {{ number_format($patients['total_registered']) }} total registered
        </div>
    </div>

    {{-- Staff on Leave --}}
    <div class="panel" style="padding:1.1rem 1rem;">
        <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem;">
            <i data-lucide="plane-takeoff" style="width:16px;height:16px;color:var(--p-warning);"></i>
            <span style="font-size:.75rem;color:var(--p-text-secondary);font-weight:600;text-transform:uppercase;letter-spacing:.04em;">Staff</span>
        </div>
        <div style="font-size:2rem;font-weight:700;line-height:1;">{{ number_format($staff['active']) }}</div>
        <div style="font-size:.75rem;color:var(--p-text-secondary);margin-top:.25rem;">
            active of {{ $staff['total'] }}
            @if($staff['on_leave'] > 0)
                · <span style="color:var(--p-warning);">{{ $staff['on_leave'] }} on leave</span>
            @endif
        </div>
    </div>

</div>

{{-- ── Row 2: Visit Trend + Appointment Breakdown ─────────────── --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:.75rem;margin-bottom:1.25rem;">

    {{-- Visit Trend Chart --}}
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">
                <i data-lucide="activity" style="width:14px;height:14px;"></i>
                Visit Trend
            </h2>
        </div>
        <div class="panel-body" style="padding:.75rem;">
            @if(empty($visitTrend))
                <div class="empty-state" style="padding:2rem 0;">
                    <div class="empty-state-icon" style="width:36px;height:36px;"><i data-lucide="bar-chart-2"></i></div>
                    <p style="margin:.5rem 0 0;font-size:.85rem;color:var(--p-text-secondary);">No visit data for this period.</p>
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
            <h2 class="panel-title">
                <i data-lucide="calendar-check-2" style="width:14px;height:14px;"></i>
                Appointments
            </h2>
        </div>
        <div class="panel-body" style="padding:.75rem;">
            @if($appointments['total'] === 0)
                <p style="text-align:center;color:var(--p-text-secondary);font-size:.85rem;padding:1.5rem 0;">No appointments.</p>
            @else
                @foreach([
                    ['label'=>'Completed', 'key'=>'completed', 'color'=>'var(--p-success)'],
                    ['label'=>'Confirmed', 'key'=>'confirmed', 'color'=>'var(--p-primary)'],
                    ['label'=>'Cancelled', 'key'=>'cancelled', 'color'=>'var(--p-danger)'],
                    ['label'=>'No Show',   'key'=>'no_show',   'color'=>'var(--p-warning)'],
                ] as $row)
                @php $val = $appointments[$row['key']]; $pct = $appointments['total'] > 0 ? round($val / $appointments['total'] * 100) : 0; @endphp
                <div style="margin-bottom:.6rem;">
                    <div style="display:flex;justify-content:space-between;font-size:.75rem;margin-bottom:.2rem;">
                        <span>{{ $row['label'] }}</span>
                        <span style="font-weight:600;">{{ $val }} <span style="color:var(--p-text-secondary);">({{ $pct }}%)</span></span>
                    </div>
                    <div style="height:6px;background:var(--p-surface-alt);border-radius:3px;overflow:hidden;">
                        <div style="height:100%;width:{{ $pct }}%;background:{{ $row['color'] }};border-radius:3px;transition:width .3s;"></div>
                    </div>
                </div>
                @endforeach
            @endif
        </div>
    </div>

</div>

{{-- ── Row 3: Revenue Trend + Visit by Type ───────────────────── --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:.75rem;margin-bottom:1.25rem;">

    {{-- Revenue Trend --}}
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">
                <i data-lucide="trending-up" style="width:14px;height:14px;"></i>
                Revenue Trend
            </h2>
        </div>
        <div class="panel-body" style="padding:.75rem;">
            @if(empty($revTrend))
                <div class="empty-state" style="padding:2rem 0;">
                    <div class="empty-state-icon" style="width:36px;height:36px;"><i data-lucide="trending-up"></i></div>
                    <p style="margin:.5rem 0 0;font-size:.85rem;color:var(--p-text-secondary);">No revenue data for this period.</p>
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
            <h2 class="panel-title">
                <i data-lucide="stethoscope" style="width:14px;height:14px;"></i>
                Visit Types
            </h2>
        </div>
        <div class="panel-body" style="padding:.75rem;">
            @if(empty($visits['by_type']))
                <p style="text-align:center;color:var(--p-text-secondary);font-size:.85rem;padding:1.5rem 0;">No visits.</p>
            @else
                @php $maxType = max(array_values($visits['by_type'])); @endphp
                @foreach($visits['by_type'] as $type => $count)
                @php $pct = $maxType > 0 ? round($count / $maxType * 100) : 0; @endphp
                <div style="margin-bottom:.6rem;">
                    <div style="display:flex;justify-content:space-between;font-size:.75rem;margin-bottom:.2rem;">
                        <span>{{ ucwords(str_replace('_',' ',$type)) }}</span>
                        <span style="font-weight:600;">{{ $count }}</span>
                    </div>
                    <div style="height:6px;background:var(--p-surface-alt);border-radius:3px;overflow:hidden;">
                        <div style="height:100%;width:{{ $pct }}%;background:var(--p-primary);border-radius:3px;"></div>
                    </div>
                </div>
                @endforeach
            @endif
        </div>
    </div>

</div>

{{-- ── Row 4: Staff Distribution + Inventory Alerts ───────────── --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1.25rem;">

    {{-- Staff by Category --}}
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">
                <i data-lucide="users" style="width:14px;height:14px;"></i>
                Staff Distribution
            </h2>
        </div>
        <div class="panel-body" style="padding:.75rem;">
            @if(empty($staff['by_category']))
                <p style="text-align:center;color:var(--p-text-secondary);font-size:.85rem;padding:1.5rem 0;">No staff records.</p>
            @else
                @php $totalByCategory = array_sum($staff['by_category']); @endphp
                @foreach($staff['by_category'] as $cat => $count)
                @php $pct = $totalByCategory > 0 ? round($count / $totalByCategory * 100) : 0; @endphp
                <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.6rem;">
                    <span style="font-size:.75rem;min-width:90px;color:var(--p-text-secondary);">{{ ucfirst($cat) }}</span>
                    <div style="flex:1;height:8px;background:var(--p-surface-alt);border-radius:4px;overflow:hidden;">
                        <div style="height:100%;width:{{ $pct }}%;background:var(--p-primary);border-radius:4px;"></div>
                    </div>
                    <span style="font-size:.75rem;font-weight:600;min-width:30px;text-align:right;">{{ $count }}</span>
                </div>
                @endforeach
                @if($staff['pending_leaves'] > 0)
                <div style="margin-top:.75rem;padding:.5rem .75rem;background:rgba(var(--p-warning-rgb,245,158,11),.08);border-radius:var(--p-radius);border-left:3px solid var(--p-warning);font-size:.78rem;">
                    <i data-lucide="clock" style="width:12px;height:12px;"></i>
                    {{ $staff['pending_leaves'] }} pending leave request{{ $staff['pending_leaves'] !== 1 ? 's' : '' }}
                    <a href="{{ route('portals.staff.hr.leave') }}" style="margin-left:.35rem;text-decoration:underline;">Review →</a>
                </div>
                @endif
            @endif
        </div>
    </div>

    {{-- Inventory Alerts --}}
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">
                <i data-lucide="package" style="width:14px;height:14px;"></i>
                Inventory Alerts
            </h2>
        </div>
        <div class="panel-body" style="padding:.75rem;">
            @php
                $alerts = [];
                if ($inventory['pharma_out'] > 0)     $alerts[] = ['type'=>'danger',  'msg'=> $inventory['pharma_out']    . ' medicine(s) out of stock', 'url'=> route('portals.staff.inventory.pharmacy')];
                if ($inventory['pharma_low'] > 0)     $alerts[] = ['type'=>'warning', 'msg'=> $inventory['pharma_low']    . ' medicine(s) low stock', 'url'=> route('portals.staff.inventory.pharmacy')];
                if ($inventory['pharma_expired'] > 0) $alerts[] = ['type'=>'danger',  'msg'=> $inventory['pharma_expired']. ' expired medicine(s)', 'url'=> route('portals.staff.inventory.pharmacy')];
                if ($inventory['blood_total_units'] === 0) $alerts[] = ['type'=>'danger', 'msg'=>'Blood bank is empty', 'url'=> route('portals.staff.inventory.blood')];
                elseif ($inventory['blood_groups'] < 4)    $alerts[] = ['type'=>'warning','msg'=> $inventory['blood_groups'] . ' blood group(s) in stock', 'url'=> route('portals.staff.inventory.blood')];
            @endphp

            @if(empty($alerts))
                <div style="text-align:center;padding:1.5rem 0;color:var(--p-success);">
                    <i data-lucide="check-circle" style="width:28px;height:28px;margin-bottom:.35rem;display:block;margin:0 auto .35rem;"></i>
                    <p style="font-size:.85rem;margin:0;">All inventory levels look good.</p>
                </div>
            @else
                @foreach($alerts as $alert)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:.5rem .6rem;margin-bottom:.4rem;border-radius:var(--p-radius);background:{{ $alert['type']==='danger' ? 'rgba(220,38,38,.07)' : 'rgba(245,158,11,.07)' }};border-left:3px solid {{ $alert['type']==='danger' ? 'var(--p-danger)' : 'var(--p-warning)' }};">
                    <span style="font-size:.78rem;">{{ $alert['msg'] }}</span>
                    <a href="{{ $alert['url'] }}" style="font-size:.72rem;text-decoration:underline;white-space:nowrap;margin-left:.5rem;">Fix →</a>
                </div>
                @endforeach
            @endif

            <div style="margin-top:.75rem;display:flex;gap:.5rem;flex-wrap:wrap;">
                <a href="{{ route('portals.staff.inventory.pharmacy') }}" class="btn btn-ghost btn-xs">
                    <i data-lucide="pill" style="width:11px;height:11px;"></i> Pharmacy
                </a>
                <a href="{{ route('portals.staff.inventory.blood') }}" class="btn btn-ghost btn-xs">
                    <i data-lucide="droplets" style="width:11px;height:11px;"></i> Blood Bank
                </a>
            </div>
        </div>
    </div>

</div>

{{-- ── Row 5: Insurance Revenue breakdown ─────────────────────── --}}
@if($revenue['insurance_covered'] > 0)
<div class="panel" style="margin-bottom:1.25rem;">
    <div class="panel-header">
        <h2 class="panel-title">
            <i data-lucide="shield-check" style="width:14px;height:14px;"></i>
            Revenue Breakdown
        </h2>
    </div>
    <div class="panel-body" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;padding:1rem;">
        @foreach([
            ['label'=>'Total Invoiced',    'value'=> $revenue['total_invoiced'],    'color'=>'var(--p-primary)'],
            ['label'=>'Collected',         'value'=> $revenue['total_collected'],   'color'=>'var(--p-success)'],
            ['label'=>'Insurance Covered', 'value'=> $revenue['insurance_covered'], 'color'=>'var(--p-teal,#0d9488)'],
            ['label'=>'Outstanding',       'value'=> $revenue['total_outstanding'], 'color'=>'var(--p-warning)'],
        ] as $item)
        <div style="text-align:center;">
            <div style="font-size:1.4rem;font-weight:700;color:{{ $item['color'] }};">{{ number_format($item['value'], 2) }}</div>
            <div style="font-size:.72rem;color:var(--p-text-secondary);margin-top:.1rem;">{{ $item['label'] }}</div>
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection

@section('scripts')
@if(!empty($visitTrend))
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
                    backgroundColor: 'rgba(79,70,229,.6)',
                    borderColor: 'rgba(79,70,229,1)',
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
                        borderColor: 'rgba(79,70,229,.6)',
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
