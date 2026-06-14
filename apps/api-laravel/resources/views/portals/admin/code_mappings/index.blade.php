@extends('layouts.portal')
@section('title', 'Code System Mappings')
@section('sidebar') @include('portals.admin.control_center._sidebar') @endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">
                <i data-lucide="git-merge" style="width:22px;height:22px;vertical-align:middle;margin-right:8px;color:#7c3aed;"></i>
                Code System Mappings
            </h1>
            <p class="portal-page-subtitle">LOINC · ICD-10 · ATC — terminology mapping catalog</p>
        </div>
        <a href="{{ route('portals.admin.code_mappings.create') }}" class="btn btn--primary btn--sm">
            <i data-lucide="plus" style="width:13px;height:13px;"></i> Add Mapping
        </a>
    </div>

    @if(session('success'))
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 16px;margin-bottom:16px;color:#166534;font-size:0.88rem;"><i data-lucide="check" style="width:14px;height:14px;vertical-align:-2px;"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 16px;margin-bottom:16px;color:#991b1b;font-size:0.88rem;"><i data-lucide="x" style="width:14px;height:14px;vertical-align:-2px;"></i> {{ session('error') }}</div>
    @endif

    {{-- Stats --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:12px;margin-bottom:20px;">
        <div class="stat-card">
            <div class="stat-card__body"><div class="stat-card__value">{{ $stats['total'] }}</div><div class="stat-card__label">Total</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__body"><div class="stat-card__value" style="color:#16a34a;">{{ $stats['approved'] }}</div><div class="stat-card__label">Approved</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__body"><div class="stat-card__value" style="color:#d97706;">{{ $stats['pending'] }}</div><div class="stat-card__label">Pending</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__body"><div class="stat-card__value">{{ $stats['loinc'] }}</div><div class="stat-card__label">LOINC</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__body"><div class="stat-card__value">{{ $stats['icd10'] }}</div><div class="stat-card__label">ICD-10</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__body"><div class="stat-card__value">{{ $stats['atc'] }}</div><div class="stat-card__label">ATC</div></div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="portal-card" style="margin-bottom:16px;">
        <div class="portal-card__body" style="padding:14px 18px;">
            <form method="GET" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;">
                <div>
                    <label style="display:block;font-size:0.78rem;font-weight:600;margin-bottom:3px;">Search</label>
                    <input type="text" name="q" value="{{ $search }}" placeholder="local code, name, standard code…"
                           style="padding:6px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.82rem;min-width:200px;">
                </div>
                <div>
                    <label style="display:block;font-size:0.78rem;font-weight:600;margin-bottom:3px;">System</label>
                    <select name="system" style="padding:6px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.82rem;">
                        <option value="">All</option>
                        @foreach($systems as $sys)
                        <option value="{{ $sys }}" {{ $system === $sys ? 'selected' : '' }}>{{ strtoupper($sys) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:0.78rem;font-weight:600;margin-bottom:3px;">Status</label>
                    <select name="status" style="padding:6px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.82rem;">
                        <option value="">All</option>
                        @foreach($statuses as $s)
                        <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:0.78rem;font-weight:600;margin-bottom:3px;">Type</label>
                    <select name="resource_type" style="padding:6px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.82rem;">
                        <option value="">All</option>
                        @foreach($resourceTypes as $rt)
                        <option value="{{ $rt }}" {{ $resourceType === $rt ? 'selected' : '' }}>{{ $rt }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn--outline btn--sm">Filter</button>
                <a href="{{ route('portals.admin.code_mappings.index') }}" class="btn btn--ghost btn--sm">Clear</a>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="portal-card">
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table">
                <thead>
                    <tr>
                        <th>Local Code</th>
                        <th>Local Name</th>
                        <th>System</th>
                        <th>Standard Code</th>
                        <th>Standard Display</th>
                        <th>Type</th>
                        <th>Confidence</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mappings as $mapping)
                    <tr>
                        <td style="font-family:monospace;font-size:0.82rem;font-weight:600;">{{ $mapping->local_code }}</td>
                        <td style="font-size:0.82rem;color:#374151;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $mapping->local_name }}">
                            {{ $mapping->local_name ?? '—' }}
                        </td>
                        <td>
                            <span class="badge badge--info" style="font-size:0.7rem;font-weight:700;">{{ strtoupper($mapping->standard_system) }}</span>
                        </td>
                        <td style="font-family:monospace;font-size:0.82rem;">{{ $mapping->standard_code }}</td>
                        <td style="font-size:0.8rem;color:#374151;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $mapping->standard_display }}">
                            {{ $mapping->standard_display ?? '—' }}
                        </td>
                        <td style="font-size:0.78rem;color:#6b7280;">{{ $mapping->resource_type }}</td>
                        <td style="font-size:0.78rem;color:#6b7280;text-transform:capitalize;">{{ $mapping->mapping_confidence }}</td>
                        <td>
                            <span class="badge {{ $mapping->statusBadgeClass() }}" style="font-size:0.7rem;">{{ ucfirst($mapping->status) }}</span>
                        </td>
                        <td style="text-align:right;">
                            <div style="display:flex;gap:6px;justify-content:flex-end;">
                                @if($mapping->isPending())
                                <form method="POST" action="{{ route('portals.admin.code_mappings.approve', $mapping) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn--outline btn--sm" style="font-size:0.72rem;padding:3px 8px;color:#16a34a;border-color:#16a34a;"><i data-lucide="check" style="width:13px;height:13px;vertical-align:-2px;"></i> Approve</button>
                                </form>
                                <form method="POST" action="{{ route('portals.admin.code_mappings.reject', $mapping) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn--outline btn--sm" style="font-size:0.72rem;padding:3px 8px;color:#dc2626;border-color:#dc2626;"><i data-lucide="x" style="width:13px;height:13px;vertical-align:-2px;"></i> Reject</button>
                                </form>
                                @endif
                                <form method="POST" action="{{ route('portals.admin.code_mappings.destroy', $mapping) }}" style="display:inline;"
                                      onsubmit="return confirm('Delete this mapping?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn--ghost btn--sm" style="font-size:0.72rem;padding:3px 8px;color:#9ca3af;">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="text-align:center;padding:40px;color:#9ca3af;">
                            No mappings found. <a href="{{ route('portals.admin.code_mappings.create') }}" style="color:#7c3aed;">Add the first mapping →</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            @if($mappings->hasPages())
            <div style="padding:12px 20px;border-top:1px solid #f3f4f6;">
                {{ $mappings->links() }}
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
