@extends('layouts.portal')
@section('title', 'Medical Attachments')
@section('breadcrumb_home', 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Files & Attachments')

@section('content')
<div class="page-head">
    <h2>Medical attachments</h2>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.staff.files.create', ['resource_type' => $resourceType, 'resource_id' => $resourceId]) }}"
       class="btn btn-primary btn-sm">
        <i data-lucide="upload"></i> Upload File
    </a>
</div>
<p class="page-subtitle mb-6">Files and documents attached to clinical resources.</p>

@if(session('success'))
    <div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>
@endif
@if(session('error'))
    <div class="alert alert-danger mb-6"><i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div></div>
@endif

{{-- Resource filter bar --}}
<form method="GET" action="{{ route('portals.staff.files.index') }}" class="filter-bar">
    <select name="resource_type" class="filter-select">
        @foreach(['patient','visit','triage_record','clinical_note','invoice','support_ticket'] as $rt)
            <option value="{{ $rt }}" {{ $resourceType === $rt ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ',$rt)) }}</option>
        @endforeach
    </select>
    <label class="filter-search">
        <i data-lucide="search"></i>
        <input type="text" name="resource_id" value="{{ $resourceId }}" placeholder="Paste resource UUID…">
    </label>
    <button type="submit" class="btn btn-secondary btn-sm"><i data-lucide="filter"></i> Filter</button>
</form>

@if($resourceId && $attachments->isNotEmpty())
{{-- Attachments for specific resource --}}
<div class="panel mb-6">
    <div class="panel-header">
        <h3 class="panel-title">
            <i data-lucide="paperclip"></i>
            Attachments for {{ ucwords(str_replace('_',' ',$resourceType)) }}
        </h3>
        <span class="badge badge-neutral">{{ $attachments->count() }}</span>
    </div>
    <div class="panel-body panel-body--flush">
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr>
                    <th>File</th><th>Category</th><th>Description</th><th>Size</th><th>Uploaded By</th><th>Date</th><th>Actions</th>
                </tr></thead>
                <tbody>
                    @foreach($attachments as $att)
                    <tr>
                        <td data-label="File">
                            @php
                                $mime = $att->fileAsset->mime_type ?? '';
                                $icon = str_contains($mime,'pdf') ? 'file-text' : (str_contains($mime,'image') ? 'image' : 'file');
                            @endphp
                            <span class="cell-with-icon">
                                <i data-lucide="{{ $icon }}"></i>
                                <span class="td-strong">{{ $att->fileAsset->original_name ?? '—' }}</span>
                            </span>
                        </td>
                        <td data-label="Category">
                            @if($att->category)
                                <span class="badge badge-neutral">{{ $categories[$att->category] ?? $att->category }}</span>
                            @else
                                <span class="td-muted">—</span>
                            @endif
                        </td>
                        <td data-label="Description" class="td-muted">{{ $att->description ?? '—' }}</td>
                        <td data-label="Size" class="td-muted">{{ $att->fileAsset?->humanSize() ?? '—' }}</td>
                        <td data-label="Uploaded By" class="td-muted">{{ $att->fileAsset->uploaded_by ?? '—' }}</td>
                        <td data-label="Date" class="td-muted">{{ \Carbon\Carbon::parse($att->created_at)->format('M d, Y') }}</td>
                        <td data-label="Actions">
                            <div class="row-actions-inline">
                            <a href="{{ route('portals.staff.files.download', $att->file_asset_id) }}"
                               class="btn btn-ghost btn-xs">
                                <i data-lucide="download"></i> Download
                            </a>
                            <form method="POST" action="{{ route('portals.staff.files.destroy', $att->id) }}" class="inline-form"
                                  onsubmit="return confirm('Remove this attachment?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-xs">
                                    <i data-lucide="trash-2"></i>
                                </button>
                            </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@elseif($resourceId)
<div class="panel mb-6">
    <div class="panel-body">
        <div class="empty-state">
            <p>No attachments found for this {{ str_replace('_',' ',$resourceType) }}.</p>
        </div>
    </div>
</div>
@endif

{{-- All facility files --}}
<div class="panel">
    <div class="panel-header">
        <h3 class="panel-title">
            <i data-lucide="folder-open"></i>
            All Facility Files
        </h3>
        <span class="badge badge-neutral">{{ $assets->total() }} total</span>
    </div>
    <div class="panel-body panel-body--flush">
        @if($assets->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="folder-open"></i></div>
                <p>No files uploaded yet.</p>
            </div>
        @else
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr>
                    <th>Filename</th><th>Type</th><th>Size</th><th>Checksum (SHA-256)</th><th>Uploaded By</th><th>Date</th><th>Actions</th>
                </tr></thead>
                <tbody>
                    @foreach($assets as $asset)
                    <tr>
                        <td data-label="Filename" class="td-strong">{{ $asset->original_name }}</td>
                        <td data-label="Type"><span class="mono">{{ $asset->mime_type ?? '—' }}</span></td>
                        <td data-label="Size" class="td-muted">{{ $asset->humanSize() }}</td>
                        <td data-label="Checksum (SHA-256)" class="td-muted">
                            @if($asset->checksum)
                                <span class="mono" title="{{ $asset->checksum }}">{{ substr($asset->checksum,0,12) }}…</span>
                            @else —
                            @endif
                        </td>
                        <td data-label="Uploaded By" class="td-muted">{{ $asset->uploaded_by ?? '—' }}</td>
                        <td data-label="Date" class="td-muted">{{ \Carbon\Carbon::parse($asset->created_at)->format('M d, Y') }}</td>
                        <td data-label="Actions">
                            <a href="{{ route('portals.staff.files.download', $asset->id) }}"
                               class="btn btn-ghost btn-xs">
                                <i data-lucide="download"></i> Download
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="panel-footer">
            {{ $assets->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
