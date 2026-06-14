@extends('layouts.portal')
@section('title', $certification->integration_name . ' — Certification')
@section('sidebar') @include('portals.admin.control_center._sidebar') @endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <a href="{{ route('portals.admin.certifications.index') }}" style="font-size:0.83rem;color:#6b7280;text-decoration:none;">← Integration Certifications</a>
            <h1 class="portal-page-title" style="margin-top:4px;">{{ $certification->integration_name }}</h1>
            <p class="portal-page-subtitle" style="display:flex;align-items:center;gap:8px;">
                <span class="badge {{ $certification->statusBadgeClass() }}">{{ ucfirst(str_replace('_', ' ', $certification->status)) }}</span>
                <span class="badge badge--info" style="font-size:0.7rem;">{{ strtoupper($certification->integration_type) }}</span>
                @if($certification->certification_level)
                <span class="badge {{ $certification->levelBadgeClass() }}" style="text-transform:capitalize;">{{ $certification->certification_level }}</span>
                @endif
            </p>
        </div>
    </div>

    @if(session('success'))
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 16px;margin-bottom:16px;color:#166534;font-size:0.88rem;"><i data-lucide="check" style="width:14px;height:14px;vertical-align:-2px;"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 16px;margin-bottom:16px;color:#991b1b;font-size:0.88rem;"><i data-lucide="x" style="width:14px;height:14px;vertical-align:-2px;"></i> {{ session('error') }}</div>
    @endif

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

        {{-- Left column: details + badge --}}
        <div>
            {{-- Details --}}
            <div class="portal-card" style="margin-bottom:16px;">
                <div class="portal-card__header"><h2 class="portal-card__title">Details</h2></div>
                <div class="portal-card__body" style="padding:16px 20px;font-size:0.85rem;">
                    <dl style="display:grid;grid-template-columns:auto 1fr;gap:6px 16px;">
                        <dt style="font-weight:600;color:#374151;">Vendor</dt>
                        <dd style="color:#6b7280;">{{ $certification->vendor_name ?? '—' }}</dd>
                        <dt style="font-weight:600;color:#374151;">Contact</dt>
                        <dd style="color:#6b7280;">{{ $certification->vendor_contact ?? '—' }}</dd>
                        <dt style="font-weight:600;color:#374151;">Version</dt>
                        <dd style="color:#6b7280;">{{ $certification->version ?? '—' }}</dd>
                        <dt style="font-weight:600;color:#374151;">Submitted</dt>
                        <dd style="color:#6b7280;">{{ $certification->submitted_at?->format('d M Y') ?? '—' }}</dd>
                        <dt style="font-weight:600;color:#374151;">Certified</dt>
                        <dd style="color:#6b7280;">{{ $certification->certified_at?->format('d M Y') ?? '—' }}</dd>
                        <dt style="font-weight:600;color:#374151;">Expires</dt>
                        <dd style="color:{{ $certification->isExpiringSoon() ? '#d97706' : '#6b7280' }};">
                            {{ $certification->expires_at?->format('d M Y') ?? 'No expiry' }}
                            @if($certification->isExpiringSoon()) <i data-lucide="alert-triangle" style="width:14px;height:14px;vertical-align:-2px;"></i> Expiring soon @endif
                        </dd>
                    </dl>
                    @if($certification->scope_description)
                    <div style="margin-top:12px;padding-top:12px;border-top:1px solid #f3f4f6;">
                        <div style="font-weight:600;color:#374151;margin-bottom:4px;">Scope</div>
                        <div style="color:#6b7280;">{{ $certification->scope_description }}</div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Badge --}}
            @if($certification->badge)
            <div class="portal-card" style="border-color:#bbf7d0;">
                <div class="portal-card__header"><h2 class="portal-card__title">Active Badge</h2></div>
                <div class="portal-card__body" style="padding:16px 20px;">
                    <div style="text-align:center;padding:8px 0;">
                        <div><i data-lucide="{{ $certification->badge->levelIcon() }}" style="width:40px;height:40px;color:{{ $certification->badge->levelColor() }};"></i></div>
                        <div style="font-size:1.1rem;font-weight:800;margin:6px 0;text-transform:capitalize;">{{ $certification->badge->certification_level }}</div>
                        <div style="font-family:monospace;font-size:0.82rem;color:#7c3aed;font-weight:700;">{{ $certification->badge->badge_code }}</div>
                        <div style="font-size:0.78rem;color:#9ca3af;margin-top:4px;">
                            Issued {{ $certification->badge->issued_at->format('d M Y') }}
                            @if($certification->badge->expires_at) · Expires {{ $certification->badge->expires_at->format('d M Y') }} @endif
                        </div>
                    </div>
                    <form method="POST" action="{{ route('portals.admin.certifications.badge.revoke', $certification->badge) }}"
                          style="margin-top:12px;" onsubmit="return confirm('Revoke this badge?')">
                        @csrf
                        <input type="text" name="revoke_reason" placeholder="Revoke reason (required)" required
                               style="width:100%;padding:6px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.82rem;margin-bottom:8px;">
                        <button type="submit" class="btn btn--outline btn--sm" style="width:100%;color:#dc2626;border-color:#dc2626;">
                            Revoke Badge
                        </button>
                    </form>
                </div>
            </div>
            @elseif($certification->isPassed())
            <div class="portal-card">
                <div class="portal-card__header"><h2 class="portal-card__title">Issue Badge</h2></div>
                <div class="portal-card__body" style="padding:16px 20px;">
                    <form method="POST" action="{{ route('portals.admin.certifications.badge.issue', $certification) }}">
                        @csrf
                        <div style="margin-bottom:10px;">
                            <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:4px;">Level</label>
                            <select name="certification_level" required
                                    style="width:100%;padding:7px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.84rem;">
                                <option value="bronze">Bronze</option>
                                <option value="silver">Silver</option>
                                <option value="gold">Gold</option>
                                <option value="platinum">Platinum</option>
                            </select>
                        </div>
                        <div style="margin-bottom:12px;">
                            <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:4px;">Valid for (months)</label>
                            <input type="number" name="expires_months" value="12" min="1" max="36"
                                   style="width:100%;padding:7px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.84rem;">
                        </div>
                        <button type="submit" class="btn btn--primary" style="width:100%;">Issue Badge</button>
                    </form>
                </div>
            </div>
            @endif
        </div>

        {{-- Right column: test runs + record new run --}}
        <div>
            {{-- Record test run --}}
            <div class="portal-card" style="margin-bottom:16px;">
                <div class="portal-card__header"><h2 class="portal-card__title">Record Test Run</h2></div>
                <div class="portal-card__body" style="padding:16px 20px;">
                    @if($requirements->isEmpty())
                    <p style="font-size:0.83rem;color:#9ca3af;">
                        No requirements defined. <a href="{{ route('portals.admin.certifications.seed') }}" onclick="this.closest('form').submit();return false;" style="color:#7c3aed;">Seed core requirements</a> first.
                    </p>
                    @else
                    <form method="POST" action="{{ route('portals.admin.certifications.test_run', $certification) }}">
                        @csrf
                        <div style="margin-bottom:10px;">
                            <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:4px;">Run Label</label>
                            <input type="text" name="run_label" placeholder="e.g. Pre-cert run 1"
                                   style="width:100%;padding:7px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.84rem;">
                        </div>
                        <div style="max-height:280px;overflow-y:auto;border:1px solid #e5e7eb;border-radius:6px;margin-bottom:10px;">
                            <table style="width:100%;border-collapse:collapse;font-size:0.8rem;">
                                <thead><tr style="background:#f9fafb;position:sticky;top:0;">
                                    <th style="padding:6px 10px;text-align:left;font-weight:600;color:#374151;">Requirement</th>
                                    <th style="padding:6px 10px;text-align:center;font-weight:600;color:#374151;">Result</th>
                                </tr></thead>
                                <tbody>
                                @foreach($requirements as $i => $req)
                                <tr style="border-top:1px solid #f3f4f6;">
                                    <td style="padding:6px 10px;">
                                        <input type="hidden" name="results[{{ $i }}][requirement_id]" value="{{ $req->id }}">
                                        <span style="font-weight:600;">{{ $req->name }}</span>
                                        <span class="badge {{ $req->severityBadgeClass() }}" style="font-size:0.66rem;margin-left:4px;">{{ $req->severity }}</span>
                                    </td>
                                    <td style="padding:6px 10px;text-align:center;">
                                        <select name="results[{{ $i }}][result]" style="padding:3px 6px;border:1px solid #e5e7eb;border-radius:4px;font-size:0.78rem;">
                                            <option value="passed">Pass</option>
                                            <option value="failed">Fail</option>
                                            <option value="skipped">— Skip</option>
                                        </select>
                                    </td>
                                </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div style="margin-bottom:10px;">
                            <label style="display:block;font-size:0.8rem;font-weight:600;margin-bottom:4px;">Run Notes</label>
                            <textarea name="run_notes" rows="2"
                                      style="width:100%;padding:7px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.84rem;resize:vertical;"></textarea>
                        </div>
                        <button type="submit" class="btn btn--primary btn--sm" style="width:100%;">Record Results</button>
                    </form>
                    @endif
                </div>
            </div>

            {{-- Test run history --}}
            @if($certification->testRuns->isNotEmpty())
            <div class="portal-card">
                <div class="portal-card__header"><h2 class="portal-card__title">Test Run History</h2></div>
                <div class="portal-card__body" style="padding:0;">
                    <table class="portal-table" style="font-size:0.81rem;">
                        <thead><tr>
                            <th>Run</th><th>Pass Rate</th><th>Status</th><th>Date</th>
                        </tr></thead>
                        <tbody>
                        @foreach($certification->testRuns->sortByDesc('started_at') as $run)
                        <tr>
                            <td>{{ $run->run_label ?: 'Run' }}</td>
                            <td style="font-weight:600;color:{{ $run->isPassed() ? '#16a34a' : '#dc2626' }};">
                                {{ $run->passRate() }}%
                                <span style="color:#9ca3af;font-weight:400;">({{ $run->passed_count }}/{{ $run->total_requirements }})</span>
                            </td>
                            <td><span class="badge {{ $run->statusBadgeClass() }}" style="font-size:0.68rem;">{{ ucfirst($run->status) }}</span></td>
                            <td style="color:#9ca3af;">{{ $run->started_at?->format('d M Y') }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

    </div>

</div>
@endsection
