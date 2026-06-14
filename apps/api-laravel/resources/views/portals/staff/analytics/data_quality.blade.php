@extends('layouts.portal')
@section('title', 'Data Quality Analytics')
@section('sidebar') @include('portals.staff.cdss._sidebar') @endsection

@section('content')
<div class="portal-content">

    @include('portals.staff.analytics._tabs')

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">Data Quality</h1>
            <p class="portal-page-subtitle">Patient record completeness, import health &amp; CDSS signal quality</p>
        </div>
    </div>

    {{-- Patient Record Completeness --}}
    <div class="portal-card mb-6">
        <div class="portal-card__header">
            <h2 class="portal-card__title">Patient Record Completeness</h2>
            <span class="td-muted">{{ number_format($totalPatients) }} total patients</span>
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
            <div class="breakdown">
            @foreach($fields as $label => $count)
                @php
                    $pct = $totalPatients > 0 ? round($count / $totalPatients * 100) : 0;
                    $fillMod = $pct >= 80 ? '' : ($pct >= 50 ? 'breakdown__fill--warning' : 'breakdown__fill--danger');
                @endphp
                <div class="breakdown__row">
                    <span class="breakdown__label">{{ $label }}</span>
                    <div class="breakdown__track"><div class="breakdown__fill {{ $fillMod }}" style="width:{{ $pct }}%;"></div></div>
                    <span class="breakdown__value">{{ number_format($count) }} / {{ number_format($totalPatients) }} ({{ $pct }}%)</span>
                </div>
            @endforeach
            </div>
        </div>
    </div>

    <div class="grid-2 mb-6">

        {{-- Import History --}}
        <div class="portal-card">
            <div class="portal-card__header"><h2 class="portal-card__title">Data Import Health</h2></div>
            <div class="portal-card__body panel-body--flush">
                <div class="table-wrapper">
                <table class="data-table">
                    <thead><tr><th>Status</th><th>Batches</th><th>Records</th></tr></thead>
                    <tbody>
                        @forelse($importStats as $status => $row)
                            <tr>
                                <td data-label="Status">
                                    <span class="badge badge--{{ match($status) {
                                        'completed' => 'success',
                                        'failed'    => 'danger',
                                        'pending'   => 'warning',
                                        'processing'=> 'info',
                                        default     => 'default',
                                    } }}">{{ ucfirst($status) }}</span>
                                </td>
                                <td data-label="Batches" class="td-strong">{{ number_format($row->cnt) }}</td>
                                <td data-label="Records" class="td-muted">{{ number_format($row->records ?? 0) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="empty-cell td-muted">No imports yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
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
                        <div class="list-row">
                            <span class="list-row__main">{{ ucwords(str_replace('_',' ', $type)) }}</span>
                            <span class="list-row__value">{{ number_format($cnt) }}</span>
                        </div>
                    @endforeach
                    <div class="kv-table mt-6">
                        <div class="flex-between">
                            <span class="td-muted">Override Rate</span>
                            <span class="badge {{ ($overrideRate ?? 0) > 50 ? 'badge-danger' : 'badge-success' }}">
                                {{ $overrideRate ?? 0 }}%
                            </span>
                        </div>
                        @if(($overrideRate ?? 0) > 50)
                            <p class="td-muted mt-6">High override rate — review CDSS rule sensitivity.</p>
                        @endif
                    </div>
                @else
                    <p class="td-muted">No CDSS alerts in last 30 days.</p>
                @endif
            </div>
        </div>

    </div>

    {{-- Recent Imports --}}
    @if(!empty($recentImports))
    <div class="portal-card">
        <div class="portal-card__header"><h2 class="portal-card__title">Recent Imports</h2></div>
        <div class="portal-card__body panel-body--flush">
            <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr><th>Batch</th><th>Type</th><th>Records</th><th>Errors</th><th>Status</th><th>Date</th></tr>
                </thead>
                <tbody>
                    @foreach($recentImports as $imp)
                        <tr>
                            <td data-label="Batch"><span class="mono">{{ substr($imp->id ?? '', 0, 8) }}…</span></td>
                            <td data-label="Type">{{ str_replace('_',' ', $imp->import_type ?? '—') }}</td>
                            <td data-label="Records">{{ number_format($imp->total_records ?? 0) }}</td>
                            <td data-label="Errors">
                                <span class="badge {{ ($imp->error_count ?? 0) > 0 ? 'badge-danger' : 'badge-success' }}">{{ $imp->error_count ?? 0 }}</span>
                            </td>
                            <td data-label="Status">
                                <span class="badge badge--{{ match($imp->status ?? '') {
                                    'completed' => 'success',
                                    'failed'    => 'danger',
                                    'pending'   => 'warning',
                                    default     => 'default',
                                } }}">{{ ucfirst($imp->status ?? '—') }}</span>
                            </td>
                            <td data-label="Date" class="td-muted">
                                {{ isset($imp->created_at) ? \Carbon\Carbon::parse($imp->created_at)->format('d M Y') : '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
