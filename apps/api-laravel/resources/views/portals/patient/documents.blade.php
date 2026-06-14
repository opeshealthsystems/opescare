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
<div class="alert alert-info mb-4"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>
@endif
@if(session('warning'))
<div class="alert alert-warning mb-4"><i data-lucide="alert-circle"></i><div>{{ session('warning') }}</div></div>
@endif

@if(!$patient)
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="alert-circle"></i></div>
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
                        <span class="td-mono">{{ $doc->document_number ?? '—' }}</span>
                    </td>
                    <td data-label="Status">
                        @php
                            $statusCls = match($doc->status) { 'released' => 'badge-success', 'revoked' => 'badge-danger', default => 'badge-neutral' };
                        @endphp
                        <span class="badge {{ $statusCls }}">{{ ucfirst($doc->status) }}</span>
                    </td>
                    <td data-label="Issued">
                        <span class="td-muted">{{ $doc->issued_at?->format('d M Y') ?? '—' }}</span>
                    </td>
                    <td data-label="Expires">
                        <span class="td-muted">{{ $doc->expires_at?->format('d M Y') ?? 'No expiry' }}</span>
                    </td>
                    <td class="row-actions">
                        @if($doc->status === 'released' && $doc->pdf_path)
                        <a href="{{ route('portals.patient.documents.download', $doc->id) }}" class="btn btn-secondary btn-sm">
                            <i data-lucide="download"></i> Download
                        </a>
                        @elseif($doc->status === 'released')
                        <span class="td-muted">Processing…</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if(method_exists($documents, 'links') && $documents->hasPages())
    <div class="panel-body">
        {{ $documents->links() }}
    </div>
    @endif
</div>
@endif

@endsection
