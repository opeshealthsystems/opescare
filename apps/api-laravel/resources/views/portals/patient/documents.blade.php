@extends('layouts.portal')

@section('title', 'My Documents — OpesCare Patient Portal')
@section('breadcrumb_home', __('public.portal.my_portal', [], app()->getLocale()) ?: 'My Portal')
@section('breadcrumb_home_url', route('portals.patient'))
@section('breadcrumb_section', 'Documents')

@section('patient_banner')
    @include('partials.guardian-context-banner')
@endsection

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">My Documents</h1>
        <p class="page-subtitle">Official documents and certificates issued to you.</p>
    </div>
</div>

@if(session('success'))
<div class="alert alert-info" style="margin-bottom:var(--p-space-4);"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>
@endif
@if(session('warning'))
<div class="alert" style="margin-bottom:var(--p-space-4);background:#FEF3C7;border-color:#FCD34D;color:#92400E;"><i data-lucide="alert-circle"></i><div>{{ session('warning') }}</div></div>
@endif

@if(!$patient)
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon" style="color:var(--p-warning);"><i data-lucide="alert-circle"></i></div>
        <h3>No Patient Profile Found</h3>
        <p>Your patient profile could not be loaded. Please contact support.</p>
    </div>
</div>
@elseif($documents->isEmpty())
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="file-text"></i></div>
        <h3>No Documents</h3>
        <p>You have no official documents issued at this time.</p>
    </div>
</div>
@else
<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title"><i data-lucide="file-text"></i> Official Documents</h2>
        <span class="badge badge-primary">{{ method_exists($documents, 'total') ? $documents->total() : $documents->count() }}</span>
    </div>
    <div class="table-wrapper">
        <table class="data-table" aria-label="Documents list">
            <thead>
                <tr>
                    <th>Document</th>
                    <th>Type</th>
                    <th>Number</th>
                    <th>Status</th>
                    <th>Issued</th>
                    <th>Expires</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($documents as $doc)
                <tr>
                    <td data-label="Document">
                        <span class="td-strong">{{ $doc->title ?? 'Untitled Document' }}</span>
                    </td>
                    <td data-label="Type">
                        <span class="td-muted">{{ str_replace('_', ' ', ucfirst($doc->document_type)) }}</span>
                    </td>
                    <td data-label="Number">
                        <span style="font-family:monospace;font-size:0.8125rem;color:var(--p-text-muted);">{{ $doc->document_number ?? '—' }}</span>
                    </td>
                    <td data-label="Status">
                        @php
                            $statusBg   = match($doc->status) { 'released' => '#D1FAE5', 'revoked' => '#FEE2E2', default => 'var(--p-surface-2)' };
                            $statusText = match($doc->status) { 'released' => '#059669', 'revoked' => '#DC2626', default => 'var(--p-text-muted)' };
                        @endphp
                        <span style="padding:2px 8px;border-radius:9999px;font-size:0.75rem;font-weight:700;background:{{ $statusBg }};color:{{ $statusText }};">
                            {{ ucfirst($doc->status) }}
                        </span>
                    </td>
                    <td data-label="Issued">
                        <span class="td-muted">{{ $doc->issued_at?->format('d M Y') ?? '—' }}</span>
                    </td>
                    <td data-label="Expires">
                        <span class="td-muted">{{ $doc->expires_at?->format('d M Y') ?? 'No expiry' }}</span>
                    </td>
                    <td>
                        @if($doc->status === 'released' && $doc->pdf_path)
                        <a href="{{ route('portals.patient.documents.download', $doc->id) }}"
                           style="display:inline-flex;align-items:center;gap:4px;font-size:0.75rem;color:var(--p-primary);font-weight:600;text-decoration:none;">
                            <i data-lucide="download" style="width:0.75rem;height:0.75rem;"></i>Download
                        </a>
                        @elseif($doc->status === 'released')
                        <span style="font-size:0.75rem;color:var(--p-text-muted);">Processing…</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if(method_exists($documents, 'links') && $documents->hasPages())
    <div style="padding:var(--p-space-4);border-top:1px solid var(--p-border);">
        {{ $documents->links() }}
    </div>
    @endif
</div>
@endif

@endsection
