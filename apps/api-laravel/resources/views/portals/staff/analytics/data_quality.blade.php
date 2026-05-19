@extends('layouts.portal')
@section('title', 'Data Quality Analytics')
@section('sidebar') @include('portals.staff.cdss._sidebar') @endsection

@section('content')
<div class="portal-content">

    @include('portals.staff.analytics._tabs')

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">
                <i data-lucide="shield-check" style="width:22px;height:22px;vertical-align:middle;margin-right:8px;color:#7c3aed;"></i>
                Data Quality
            </h1>
            <p class="portal-page-subtitle">Patient record completeness, import health & CDSS signal quality</p>
        </div>
    </div>

    {{-- Patient Record Completeness --}}
    <div class="portal-card" style="margin-bottom:16px;">
        <div class="portal-card__header">
            <h2 class="portal-card__title">Patient Record Completeness</h2>
            <span style="font-size:0.82rem;color:#6b7280;">{{ number_format($totalPatients) }} total patients</span>
        </div>
        <div class="portal-card__body">
            @php
                $fields = [
                    'Phone Number'     => $withPhone,
                    'Date of Birth'    => $withDob,
                    'Address'          => $withAddress,
                    'Next of Kin'      => $withNextOfKin,
                    'NHIS Number'      => $withNhis,
                ];
            @endphp
            @foreach($fields as $label => $count)
                @php $pct = $totalPatients > 0 ? round($count / $totalPatients * 100) : 0; @endphp
                <div style="margin-bottom:12px;">
                    <div style="display:flex;justify-content:space-between;margin-bottom:4px;font-size:0.83rem;">
                        <span style="font-weight:500;">{{ $label }}</span>
                        <span style="color:#6b7280;">{{ number_format($count) }} / {{ number_format($totalPatients) }} ({{ $pct }}%)</span>
                    </div>
                    <div style="height:8px;background:#e5e7eb;border-radius:4px;overflow:hidden;">
                        <div style="height:100%;background:{{ $pct >= 80 ? '#16a34a' : ($pct >= 50 ? '#d97706' : '#dc2626') }};
                                    width:{{ $pct }}%;border-radius:4px;transition:width .3s;"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">

        {{-- Import History --}}
        <div class="portal-card">
            <div class="portal-card__header"><h2 class="portal-card__title">Data Import Health</h2></div>
            <div class="portal-card__body" style="padding:0;">
                <table class="portal-table">
                    <thead><tr><th>Status</th><th>Batches</th><th>Records</th></tr></thead>
                    <tbody>
                        @forelse($importStats as $status => $row)
                            <tr>
                                <td>
                                    <span class="badge badge--{{ match($status) {
                                        'completed' => 'success',
                                        'failed'    => 'danger',
                                        'pending'   => 'warning',
                                        'processing'=> 'info',
                                        default     => 'default',
                                    } }}" style="font-size:0.72rem;">{{ ucfirst($status) }}</span>
                                </td>
                                <td style="font-weight:600;font-size:0.85rem;">{{ number_format($row->cnt) }}</td>
                                <td style="font-size:0.82rem;color:#6b7280;">{{ number_format($row->records ?? 0) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" style="text-align:center;padding:20px;color:#9ca3af;">No imports yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- CDSS Alert Quality --}}
        <div class="portal-card">
            <div class="portal-card__header">
                <h2 class="portal-card__title">CDSS Alert Signal (Last 30 Days)</h2>
            </div>
            <div class="portal-card__body">
                @if(!empty($alertsByType))
                    @foreach($alertsByType as $type => $cnt)
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:5px 0;border-bottom:1px solid #f3f4f6;font-size:0.83rem;">
                            <span style="color:#374151;">{{ ucwords(str_replace('_',' ', $type)) }}</span>
                            <span style="font-weight:600;color:#7c3aed;">{{ number_format($cnt) }}</span>
                        </div>
                    @endforeach
                    <div style="margin-top:12px;padding-top:10px;border-top:1px solid #e5e7eb;font-size:0.83rem;">
                        <div style="display:flex;justify-content:space-between;">
                            <span style="color:#6b7280;">Override Rate</span>
                            <span style="font-weight:700;color:{{ ($overrideRate ?? 0) > 50 ? '#dc2626' : '#16a34a' }};">
                                {{ $overrideRate ?? 0 }}%
                            </span>
                        </div>
                        @if(($overrideRate ?? 0) > 50)
                            <div style="font-size:0.75rem;color:#dc2626;margin-top:4px;">
                                High override rate — review CDSS rule sensitivity.
                            </div>
                        @endif
                    </div>
                @else
                    <p style="color:#9ca3af;font-size:0.83rem;margin:0;">No CDSS alerts in last 30 days.</p>
                @endif
            </div>
        </div>

    </div>

    {{-- Recent Imports --}}
    @if(!empty($recentImports))
    <div class="portal-card">
        <div class="portal-card__header"><h2 class="portal-card__title">Recent Imports</h2></div>
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table">
                <thead>
                    <tr><th>Batch</th><th>Type</th><th>Records</th><th>Errors</th><th>Status</th><th>Date</th></tr>
                </thead>
                <tbody>
                    @foreach($recentImports as $imp)
                        <tr>
                            <td><code style="font-size:0.76rem;">{{ substr($imp->id ?? '', 0, 8) }}…</code></td>
                            <td style="font-size:0.82rem;">{{ str_replace('_',' ', $imp->import_type ?? '—') }}</td>
                            <td style="font-size:0.82rem;">{{ number_format($imp->total_records ?? 0) }}</td>
                            <td style="font-size:0.82rem;color:{{ ($imp->error_count ?? 0) > 0 ? '#dc2626' : '#16a34a' }};">
                                {{ $imp->error_count ?? 0 }}
                            </td>
                            <td>
                                <span class="badge badge--{{ match($imp->status ?? '') {
                                    'completed' => 'success',
                                    'failed'    => 'danger',
                                    'pending'   => 'warning',
                                    default     => 'default',
                                } }}" style="font-size:0.72rem;">{{ ucfirst($imp->status ?? '—') }}</span>
                            </td>
                            <td style="font-size:0.79rem;color:#6b7280;">
                                {{ isset($imp->created_at) ? \Carbon\Carbon::parse($imp->created_at)->format('d M Y') : '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
