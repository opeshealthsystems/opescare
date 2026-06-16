@extends('layouts.portal')

@section('title', __('public.portal.documents_title', [], app()->getLocale()) ?: 'My Documents')
@section('breadcrumb_home', __('public.portal.my_portal', [], app()->getLocale()) ?: 'My Portal')
@section('breadcrumb_home_url', route('portals.patient'))
@section('breadcrumb_section', __('public.portal.documents_breadcrumb', [], app()->getLocale()) ?: 'Documents')

@section('patient_banner')
    @include('partials.guardian-context-banner')
@endsection

@php $l = app()->getLocale(); @endphp

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">{{ __('public.portal.documents_title', [], $l) ?: 'My Documents' }}</h1>
        <p class="page-subtitle">{{ __('public.portal.documents_subtitle', [], $l) ?: 'Official documents and certificates issued to you.' }}</p>
    </div>
</div>

@if(!$patient)
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon" style="color:var(--p-warning);"><i data-lucide="alert-circle"></i></div>
        <h3>{{ __('public.portal.no_profile_found_title', [], $l) ?: 'No Patient Profile Found' }}</h3>
        <p>{{ __('public.portal.no_profile_found_desc', [], $l) ?: 'Your patient profile could not be loaded. Please contact support.' }}</p>
    </div>
</div>
@elseif($documents->isEmpty())
<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="file-text"></i></div>
        <h3>{{ __('public.portal.no_documents_title', [], $l) ?: 'No Documents' }}</h3>
        <p>{{ __('public.portal.no_documents_desc', [], $l) ?: 'You have no official documents issued at this time.' }}</p>
    </div>
</div>
@else
<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title"><i data-lucide="file-text"></i> {{ __('public.portal.panel_official_documents', [], $l) ?: 'Official Documents' }}</h2>
    </div>
    <div class="panel-body" style="padding:0;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:var(--p-surface-2);font-size:0.8125rem;color:var(--p-text-muted);">
                    <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;">{{ __('public.portal.col_document', [], $l) ?: 'Document' }}</th>
                    <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;">{{ __('public.portal.col_type', [], $l) ?: 'Type' }}</th>
                    <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;">{{ __('public.portal.col_number', [], $l) ?: 'Number' }}</th>
                    <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;">{{ __('public.portal.col_status', [], $l) ?: 'Status' }}</th>
                    <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;">{{ __('public.portal.col_issued', [], $l) ?: 'Issued' }}</th>
                    <th style="padding:var(--p-space-3) var(--p-space-4);text-align:left;">{{ __('public.portal.col_expires', [], $l) ?: 'Expires' }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($documents as $doc)
                <tr style="border-top:1px solid var(--p-border);font-size:0.875rem;">
                    <td style="padding:var(--p-space-3) var(--p-space-4);font-weight:600;">{{ $doc->title ?? __('public.portal.lbl_untitled_document', [], $l) ?: 'Untitled Document' }}</td>
                    <td style="padding:var(--p-space-3) var(--p-space-4);color:var(--p-text-muted);">{{ str_replace('_', ' ', ucfirst($doc->document_type)) }}</td>
                    <td style="padding:var(--p-space-3) var(--p-space-4);color:var(--p-text-muted);font-family:monospace;font-size:0.8125rem;">{{ $doc->document_number ?? '—' }}</td>
                    <td style="padding:var(--p-space-3) var(--p-space-4);">
                        <span style="padding:2px 8px;border-radius:9999px;font-size:0.75rem;font-weight:700;
                            background:{{ $doc->status === 'released' ? '#D1FAE5' : 'var(--p-surface-2)' }};
                            color:{{ $doc->status === 'released' ? '#059669' : 'var(--p-text-muted)' }};">
                            {{ ucfirst($doc->status) }}
                        </span>
                    </td>
                    <td style="padding:var(--p-space-3) var(--p-space-4);color:var(--p-text-muted);">{{ $doc->issued_at?->format('d M Y') ?? '—' }}</td>
                    <td style="padding:var(--p-space-3) var(--p-space-4);color:var(--p-text-muted);">{{ $doc->expires_at?->format('d M Y') ?? __('public.portal.lbl_no_expiry', [], $l) ?: 'No expiry' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
