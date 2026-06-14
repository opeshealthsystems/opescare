@extends('layouts.portal')
@section('title', $certification->integration_name . ' — Certification')
@section('sidebar') @include('portals.admin.control_center._sidebar') @endsection

@section('content')

<div class="breadcrumb">
    <a href="{{ route('portals.admin.certifications.index') }}">Integration Certifications</a>
    <i data-lucide="chevron-right"></i>
    <span>{{ $certification->integration_name }}</span>
</div>

<div class="entity-head">
    <div class="entity-head__icon"><i data-lucide="shield-check"></i></div>
    <div>
        <h2 class="entity-head__title">{{ $certification->integration_name }}</h2>
        <div class="entity-head__sub">
            <span class="badge {{ $certification->statusBadgeClass() }} badge-sm">{{ ucfirst(str_replace('_', ' ', $certification->status)) }}</span>
            <span class="badge badge--info badge-sm">{{ strtoupper($certification->integration_type) }}</span>
            @if($certification->certification_level)
            <span class="badge {{ $certification->levelBadgeClass() }} badge-sm">{{ ucfirst($certification->certification_level) }}</span>
            @endif
        </div>
    </div>
</div>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

<div class="grid-2">

    {{-- Left column: details + badge --}}
    <div>
        {{-- Details --}}
        <div class="panel mb-6">
            <div class="panel-header"><h3 class="panel-title"><i data-lucide="info"></i> Details</h3></div>
            <div class="panel-body">
                <table class="kv-table">
                    <tr><td>Vendor</td><td class="kv-strong">{{ $certification->vendor_name ?? '—' }}</td></tr>
                    <tr><td>Contact</td><td class="kv-strong">{{ $certification->vendor_contact ?? '—' }}</td></tr>
                    <tr><td>Version</td><td class="kv-strong">{{ $certification->version ?? '—' }}</td></tr>
                    <tr><td>Submitted</td><td class="kv-strong">{{ $certification->submitted_at?->format('d M Y') ?? '—' }}</td></tr>
                    <tr><td>Certified</td><td class="kv-strong">{{ $certification->certified_at?->format('d M Y') ?? '—' }}</td></tr>
                    <tr><td>Expires</td><td class="kv-strong">
                        {{ $certification->expires_at?->format('d M Y') ?? 'No expiry' }}
                        @if($certification->isExpiringSoon())
                            <span class="badge badge-warning badge-sm"><i data-lucide="alert-triangle"></i> Expiring soon</span>
                        @endif
                    </td></tr>
                </table>
                @if($certification->scope_description)
                <div class="detail-divider">
                    <div class="kv-strong mb-3">Scope</div>
                    <div class="td-muted">{{ $certification->scope_description }}</div>
                </div>
                @endif
            </div>
        </div>

        {{-- Badge --}}
        @if($certification->badge)
        <div class="panel">
            <div class="panel-header"><h3 class="panel-title"><i data-lucide="award"></i> Active badge</h3></div>
            <div class="panel-body">
                <div class="badge-showcase">
                    <i data-lucide="{{ $certification->badge->levelIcon() }}" class="badge-showcase__icon" style="color: {{ $certification->badge->levelColor() }};"></i>
                    <div class="badge-showcase__level">{{ ucfirst($certification->badge->certification_level) }}</div>
                    <div class="mono code-token">{{ $certification->badge->badge_code }}</div>
                    <div class="td-muted text-sm">
                        Issued {{ $certification->badge->issued_at->format('d M Y') }}
                        @if($certification->badge->expires_at) · Expires {{ $certification->badge->expires_at->format('d M Y') }} @endif
                    </div>
                </div>
                <form method="POST" action="{{ route('portals.admin.certifications.badge.revoke', $certification->badge) }}"
                      class="mt-6" onsubmit="return confirm('Revoke this badge?')">
                    @csrf
                    <div class="form-group mb-3">
                        <input type="text" name="revoke_reason" class="form-control" placeholder="Revoke reason (required)" required>
                    </div>
                    <button type="submit" class="btn btn-danger btn-block">Revoke Badge</button>
                </form>
            </div>
        </div>
        @elseif($certification->isPassed())
        <div class="panel">
            <div class="panel-header"><h3 class="panel-title"><i data-lucide="award"></i> Issue badge</h3></div>
            <div class="panel-body">
                <form method="POST" action="{{ route('portals.admin.certifications.badge.issue', $certification) }}">
                    @csrf
                    <div class="form-group mb-3">
                        <label class="form-label">Level</label>
                        <select name="certification_level" class="form-control" required>
                            <option value="bronze">Bronze</option>
                            <option value="silver">Silver</option>
                            <option value="gold">Gold</option>
                            <option value="platinum">Platinum</option>
                        </select>
                    </div>
                    <div class="form-group mb-4">
                        <label class="form-label">Valid for (months)</label>
                        <input type="number" name="expires_months" value="12" min="1" max="36" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Issue Badge</button>
                </form>
            </div>
        </div>
        @endif
    </div>

    {{-- Right column: test runs + record new run --}}
    <div>
        {{-- Record test run --}}
        <div class="panel mb-6">
            <div class="panel-header"><h3 class="panel-title"><i data-lucide="clipboard-check"></i> Record test run</h3></div>
            <div class="panel-body">
                @if($requirements->isEmpty())
                <p class="td-muted">
                    No requirements defined.
                    <a href="{{ route('portals.admin.certifications.seed') }}" onclick="this.closest('form')?.submit();return false;">Seed core requirements</a> first.
                </p>
                @else
                <form method="POST" action="{{ route('portals.admin.certifications.test_run', $certification) }}">
                    @csrf
                    <div class="form-group mb-3">
                        <label class="form-label">Run Label</label>
                        <input type="text" name="run_label" class="form-control" placeholder="e.g. Pre-cert run 1">
                    </div>
                    <div class="scroll-table mb-3">
                        <table class="data-table">
                            <thead><tr>
                                <th>Requirement</th>
                                <th>Result</th>
                            </tr></thead>
                            <tbody>
                            @foreach($requirements as $i => $req)
                            <tr>
                                <td data-label="Requirement">
                                    <input type="hidden" name="results[{{ $i }}][requirement_id]" value="{{ $req->id }}">
                                    <span class="td-strong">{{ $req->name }}</span>
                                    <span class="badge {{ $req->severityBadgeClass() }} badge-sm">{{ $req->severity }}</span>
                                </td>
                                <td data-label="Result">
                                    <select name="results[{{ $i }}][result]" class="form-control">
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
                    <div class="form-group mb-3">
                        <label class="form-label">Run Notes</label>
                        <textarea name="run_notes" rows="2" class="form-control"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Record Results</button>
                </form>
                @endif
            </div>
        </div>

        {{-- Test run history --}}
        @if($certification->testRuns->isNotEmpty())
        <div class="panel">
            <div class="panel-header"><h3 class="panel-title"><i data-lucide="history"></i> Test run history</h3></div>
            <div class="panel-body panel-body--flush">
                <div class="table-wrapper">
                <table class="data-table">
                    <thead><tr>
                        <th>Run</th><th>Pass Rate</th><th>Status</th><th>Date</th>
                    </tr></thead>
                    <tbody>
                    @foreach($certification->testRuns->sortByDesc('started_at') as $run)
                    <tr>
                        <td data-label="Run">{{ $run->run_label ?: 'Run' }}</td>
                        <td data-label="Pass Rate">
                            <span class="badge {{ $run->isPassed() ? 'badge-success' : 'badge-danger' }} badge-sm">{{ $run->passRate() }}%</span>
                            <span class="td-muted code-muted">({{ $run->passed_count }}/{{ $run->total_requirements }})</span>
                        </td>
                        <td data-label="Status"><span class="badge {{ $run->statusBadgeClass() }} badge-sm">{{ ucfirst($run->status) }}</span></td>
                        <td data-label="Date" class="td-muted">{{ $run->started_at?->format('d M Y') }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                </div>
            </div>
        </div>
        @endif
    </div>

</div>

@endsection
