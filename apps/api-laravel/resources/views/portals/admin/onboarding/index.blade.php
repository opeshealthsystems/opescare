@extends('layouts.portal')
@section('title', 'Facility Onboarding')
@section('sidebar') @include('portals.admin.control_center._sidebar') @endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">
                <i data-lucide="rocket" style="width:22px;height:22px;vertical-align:middle;margin-right:8px;color:#7c3aed;"></i>
                Facility Onboarding & Go-Live
            </h1>
            <p class="portal-page-subtitle">Track onboarding progress and approve facilities for live operations</p>
        </div>
    </div>

    {{-- KPI Strip --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:20px;">
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#eff6ff;"><i data-lucide="building-2" style="color:#2563eb;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value">{{ $stats['total'] }}</div><div class="stat-card__label">Facilities</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#f0fdf4;"><i data-lucide="check-circle-2" style="color:#16a34a;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value">{{ $stats['approved'] }}</div><div class="stat-card__label">Approved</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#eff6ff;"><i data-lucide="clock" style="color:#2563eb;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value">{{ $stats['ready_for_approval'] }}</div><div class="stat-card__label">Ready to Approve</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#fff7ed;"><i data-lucide="alert-circle" style="color:#d97706;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value">{{ $stats['pending'] }}</div><div class="stat-card__label">In Progress</div></div>
        </div>
    </div>

    <div class="portal-card">
        <div class="portal-card__header">
            <h2 class="portal-card__title">All Facilities</h2>
        </div>
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table">
                <thead>
                    <tr>
                        <th>Facility</th>
                        <th>Type</th>
                        <th>Checklist Progress</th>
                        <th>Status</th>
                        <th>Approved</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($facilities as $facility)
                        @php
                            $r = $readinessMap[$facility->id];
                            $checklist = $r->checklist_json ?? [];
                            $done = collect($checklist)->filter(fn($v) => $v === true)->count();
                            $total = count($checklist);
                            $pct = $total > 0 ? round($done / $total * 100) : 0;
                        @endphp
                        <tr>
                            <td style="font-weight:600;font-size:0.88rem;">{{ $facility->name }}</td>
                            <td style="font-size:0.82rem;text-transform:capitalize;">{{ $facility->type ?? '—' }}</td>
                            <td style="min-width:160px;">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <div style="flex:1;background:#e5e7eb;border-radius:99px;height:6px;">
                                        <div style="width:{{ $pct }}%;background:{{ $pct === 100 ? '#16a34a' : '#7c3aed' }};border-radius:99px;height:6px;transition:width .3s;"></div>
                                    </div>
                                    <span style="font-size:0.75rem;color:#6b7280;white-space:nowrap;">{{ $done }}/{{ $total }}</span>
                                </div>
                            </td>
                            <td>
                                @php
                                    $color = match($r->status) {
                                        'approved' => 'success',
                                        'ready_for_approval' => 'info',
                                        default => 'warning',
                                    };
                                @endphp
                                <span class="badge badge--{{ $color }}" style="font-size:0.72rem;">
                                    {{ str_replace('_', ' ', ucfirst($r->status)) }}
                                </span>
                            </td>
                            <td style="font-size:0.79rem;color:#6b7280;">
                                {{ $r->approved_at?->format('d M Y') ?? '—' }}
                            </td>
                            <td style="text-align:right;">
                                <a href="{{ route('portals.admin.onboarding.show', $facility) }}"
                                   class="btn btn--outline btn--sm">Manage</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:40px;color:#9ca3af;">
                                No facilities found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
