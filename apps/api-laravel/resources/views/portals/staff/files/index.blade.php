@extends('layouts.portal')
@section('title', 'Medical Attachments')
@section('breadcrumb_home', 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Files & Attachments')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Medical Attachments</h1>
        <p class="page-subtitle">Files and documents attached to clinical resources.</p>
    </div>
    <a href="{{ route('portals.staff.files.create', ['resource_type' => $resourceType, 'resource_id' => $resourceId]) }}"
       class="btn btn-primary btn-sm">
        <i data-lucide="upload" style="width:14px;height:14px;"></i> Upload File
    </a>
</div>

@if(session('success'))
    <div class="auth-alert auth-alert-success" style="margin-bottom:1rem;"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>
@endif
@if(session('error'))
    <div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;"><i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div></div>
@endif

{{-- Resource filter bar --}}
<div class="panel" style="margin-bottom:1rem;">
    <div class="panel-body">
        <form method="GET" action="{{ route('portals.staff.files.index') }}" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end;">
            <div class="form-group" style="margin:0;flex:1;min-width:140px;">
                <label class="form-label" style="font-size:.75rem;">Resource Type</label>
                <select name="resource_type" class="form-control form-control-sm">
                    @foreach(['patient','visit','triage_record','clinical_note','invoice','support_ticket'] as $rt)
                        <option value="{{ $rt }}" {{ $resourceType === $rt ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ',$rt)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;flex:2;min-width:200px;">
                <label class="form-label" style="font-size:.75rem;">Resource ID</label>
                <input type="text" name="resource_id" value="{{ $resourceId }}" class="form-control form-control-sm" placeholder="Paste UUID…">
            </div>
            <button type="submit" class="btn btn-ghost btn-sm">Filter</button>
        </form>
    </div>
</div>

@if($resourceId && $attachments->isNotEmpty())
{{-- Attachments for specific resource --}}
<div class="panel" style="margin-bottom:1.5rem;">
    <div style="padding:.75rem 1.25rem;border-bottom:1px solid var(--p-border);display:flex;align-items:center;gap:.5rem;">
        <i data-lucide="paperclip" style="width:15px;height:15px;color:var(--p-primary);"></i>
        <span style="font-weight:600;font-size:.9rem;">
            Attachments for {{ ucwords(str_replace('_',' ',$resourceType)) }}
        </span>
        <span class="badge badge-neutral" style="font-size:.72rem;">{{ $attachments->count() }}</span>
    </div>
    <div class="panel-body" style="padding:0;">
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr>
                    <th>File</th><th>Category</th><th>Description</th><th>Size</th><th>Uploaded By</th><th>Date</th><th>Actions</th>
                </tr></thead>
                <tbody>
                    @foreach($attachments as $att)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:.5rem;">
                                @php
                                    $mime = $att->fileAsset->mime_type ?? '';
                                    $icon = str_contains($mime,'pdf') ? 'file-text' : (str_contains($mime,'image') ? 'image' : 'file');
                                @endphp
                                <i data-lucide="{{ $icon }}" style="width:14px;height:14px;color:var(--p-primary);flex-shrink:0;"></i>
                                <span style="font-size:.82rem;font-weight:500;">{{ $att->fileAsset->original_name ?? '—' }}</span>
                            </div>
                        </td>
                        <td>
                            @if($att->category)
                                <span class="badge badge-neutral" style="font-size:.72rem;">{{ $categories[$att->category] ?? $att->category }}</span>
                            @else
                                <span style="color:var(--p-text-muted);">—</span>
                            @endif
                        </td>
                        <td style="font-size:.8rem;color:var(--p-text-muted);">{{ $att->description ?? '—' }}</td>
                        <td style="font-size:.78rem;color:var(--p-text-muted);">{{ $att->fileAsset?->humanSize() ?? '—' }}</td>
                        <td style="font-size:.78rem;color:var(--p-text-muted);">{{ $att->fileAsset->uploaded_by ?? '—' }}</td>
                        <td style="font-size:.78rem;color:var(--p-text-muted);">{{ \Carbon\Carbon::parse($att->created_at)->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('portals.staff.files.download', $att->file_asset_id) }}"
                               class="btn btn-ghost btn-xs" style="margin-right:4px;">
                                <i data-lucide="download" style="width:11px;height:11px;"></i> Download
                            </a>
                            <form method="POST" action="{{ route('portals.staff.files.destroy', $att->id) }}" style="display:inline;"
                                  onsubmit="return confirm('Remove this attachment?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-xs" style="color:var(--p-danger);">
                                    <i data-lucide="trash-2" style="width:11px;height:11px;"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@elseif($resourceId)
<div class="panel" style="margin-bottom:1.5rem;">
    <div class="panel-body">
        <div style="text-align:center;padding:1.5rem;color:var(--p-text-muted);font-size:.85rem;">
            No attachments found for this {{ str_replace('_',' ',$resourceType) }}.
        </div>
    </div>
</div>
@endif

{{-- All facility files --}}
<div class="panel">
    <div style="padding:.75rem 1.25rem;border-bottom:1px solid var(--p-border);display:flex;align-items:center;gap:.5rem;">
        <i data-lucide="folder-open" style="width:15px;height:15px;color:var(--p-primary);"></i>
        <span style="font-weight:600;font-size:.9rem;">All Facility Files</span>
        <span class="badge badge-neutral" style="font-size:.72rem;margin-left:auto;">{{ $assets->total() }} total</span>
    </div>
    <div class="panel-body" style="padding:0;">
        @if($assets->isEmpty())
            <div style="padding:1.5rem;text-align:center;color:var(--p-text-muted);font-size:.85rem;">
                No files uploaded yet.
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
                        <td style="font-weight:500;font-size:.82rem;">{{ $asset->original_name }}</td>
                        <td style="font-size:.78rem;"><code>{{ $asset->mime_type ?? '—' }}</code></td>
                        <td style="font-size:.78rem;color:var(--p-text-muted);">{{ $asset->humanSize() }}</td>
                        <td style="font-size:.72rem;color:var(--p-text-muted);">
                            @if($asset->checksum)
                                <code title="{{ $asset->checksum }}">{{ substr($asset->checksum,0,12) }}…</code>
                            @else —
                            @endif
                        </td>
                        <td style="font-size:.78rem;color:var(--p-text-muted);">{{ $asset->uploaded_by ?? '—' }}</td>
                        <td style="font-size:.78rem;color:var(--p-text-muted);">{{ \Carbon\Carbon::parse($asset->created_at)->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('portals.staff.files.download', $asset->id) }}"
                               class="btn btn-ghost btn-xs">
                                <i data-lucide="download" style="width:11px;height:11px;"></i> Download
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding:.75rem 1.25rem;border-top:1px solid var(--p-border);">
            {{ $assets->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
