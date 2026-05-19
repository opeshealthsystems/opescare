@extends('layouts.portal')
@section('title', 'Integration Certifications')
@section('sidebar') @include('portals.admin.control_center._sidebar') @endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">
                <i data-lucide="shield-check" style="width:22px;height:22px;vertical-align:middle;margin-right:8px;color:#7c3aed;"></i>
                Integration Certifications
            </h1>
            <p class="portal-page-subtitle">OpesCare interoperability & security certification program</p>
        </div>
        <div style="display:flex;gap:8px;">
            <form method="POST" action="{{ route('portals.admin.certifications.seed') }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn--outline btn--sm">
                    <i data-lucide="list-checks" style="width:13px;height:13px;"></i> Seed Requirements
                </button>
            </form>
            <a href="{{ route('portals.admin.certifications.create') }}" class="btn btn--primary btn--sm">
                <i data-lucide="plus" style="width:13px;height:13px;"></i> New Certification
            </a>
        </div>
    </div>

    @if(session('success'))
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 16px;margin-bottom:16px;color:#166534;font-size:0.88rem;">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 16px;margin-bottom:16px;color:#991b1b;font-size:0.88rem;">✗ {{ session('error') }}</div>
    @endif

    {{-- Stats --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:12px;margin-bottom:20px;">
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#eff6ff;"><i data-lucide="layers" style="color:#2563eb;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value">{{ $stats['total'] }}</div><div class="stat-card__label">Total</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#f0fdf4;"><i data-lucide="check-circle-2" style="color:#16a34a;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value">{{ $stats['passed'] }}</div><div class="stat-card__label">Certified</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#fff7ed;"><i data-lucide="clock" style="color:#d97706;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value">{{ $stats['in_progress'] }}</div><div class="stat-card__label">In Progress</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#fdf4ff;"><i data-lucide="award" style="color:#9333ea;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value">{{ $stats['badges'] }}</div><div class="stat-card__label">Active Badges</div></div>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;">
        <select name="status" onchange="this.form.submit()"
                style="padding:6px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.82rem;">
            <option value="">All Statuses</option>
            @foreach($statuses as $s)
            <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
            @endforeach
        </select>
        <select name="type" onchange="this.form.submit()"
                style="padding:6px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.82rem;">
            <option value="">All Types</option>
            @foreach($types as $t)
            <option value="{{ $t }}" {{ $type === $t ? 'selected' : '' }}>{{ strtoupper($t) }}</option>
            @endforeach
        </select>
        @if($status || $type)
        <a href="{{ route('portals.admin.certifications.index') }}" class="btn btn--ghost btn--sm">Clear</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="portal-card">
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table">
                <thead>
                    <tr>
                        <th>Integration</th>
                        <th>Type</th>
                        <th>Vendor</th>
                        <th>Status</th>
                        <th>Level</th>
                        <th>Last Test Run</th>
                        <th>Badge</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($certifications as $cert)
                    <tr>
                        <td style="font-weight:600;font-size:0.88rem;">
                            {{ $cert->integration_name }}
                            @if($cert->version)
                            <span style="font-size:0.74rem;color:#9ca3af;font-weight:400;">v{{ $cert->version }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge--info" style="font-size:0.7rem;font-weight:700;">{{ strtoupper($cert->integration_type) }}</span>
                        </td>
                        <td style="font-size:0.82rem;color:#6b7280;">{{ $cert->vendor_name ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $cert->statusBadgeClass() }}" style="font-size:0.72rem;">
                                {{ ucfirst(str_replace('_', ' ', $cert->status)) }}
                            </span>
                        </td>
                        <td>
                            @if($cert->certification_level)
                            <span class="badge {{ $cert->levelBadgeClass() }}" style="font-size:0.72rem;text-transform:capitalize;">
                                {{ $cert->certification_level }}
                            </span>
                            @else
                            <span style="color:#d1d5db;font-size:0.8rem;">—</span>
                            @endif
                        </td>
                        <td style="font-size:0.8rem;">
                            @if($cert->latestTestRun)
                                <span style="color:{{ $cert->latestTestRun->isPassed() ? '#16a34a' : '#dc2626' }};">
                                    {{ $cert->latestTestRun->passRate() }}%
                                </span>
                                <span style="color:#9ca3af;font-size:0.74rem;">
                                    ({{ $cert->latestTestRun->started_at?->format('d M Y') }})
                                </span>
                            @else
                                <span style="color:#d1d5db;">No runs</span>
                            @endif
                        </td>
                        <td style="font-size:0.8rem;">
                            @if($cert->badge)
                                <span style="font-family:monospace;font-size:0.74rem;color:#7c3aed;">{{ $cert->badge->badge_code }}</span>
                            @else
                                <span style="color:#d1d5db;">—</span>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            <a href="{{ route('portals.admin.certifications.show', $cert) }}"
                               class="btn btn--outline btn--sm">Manage</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align:center;padding:40px;color:#9ca3af;">
                            No certifications yet. <a href="{{ route('portals.admin.certifications.create') }}" style="color:#7c3aed;">Start the first one →</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($certifications->hasPages())
            <div style="padding:12px 20px;border-top:1px solid #f3f4f6;">{{ $certifications->links() }}</div>
            @endif
        </div>
    </div>

</div>
@endsection
