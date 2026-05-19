@extends('layouts.portal')
@section('title', 'Global Search')
@section('breadcrumb_home', 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Global Search')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ __('public.portal.nav_search', [], app()->getLocale()) ?: 'Global Search' }}</h1>
        <p class="page-subtitle">Search across patients, facilities, medicines, lab tests, documents, and more.</p>
    </div>
</div>

{{-- Search Bar --}}
<form method="GET" action="{{ route('portals.staff.search') }}" style="margin-bottom:1.5rem;">
    <div style="display:flex;gap:.5rem;max-width:640px;">
        <div style="flex:1;position:relative;">
            <i data-lucide="search" style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);width:16px;height:16px;color:var(--p-text-muted);pointer-events:none;"></i>
            <input type="text" name="q" value="{{ $query }}" class="form-control"
                placeholder="Search patients, medicines, facilities, documents…"
                style="padding-left:2.25rem;"
                autofocus>
        </div>
        <button type="submit" class="btn btn-primary btn-sm" style="white-space:nowrap;">Search</button>
        @if($query)
            <a href="{{ route('portals.staff.search') }}" class="btn btn-ghost btn-sm">Clear</a>
        @endif
    </div>
</form>

@if($query === '')
    {{-- Empty state before search --}}
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="search"></i></div>
        <h3>Search the platform</h3>
        <p>Enter a keyword above to search across facilities, medicines, lab tests, documents, and partners.</p>
        <div style="margin-top:1rem;display:flex;flex-wrap:wrap;gap:.5rem;justify-content:center;">
            @foreach(['Facilities','Medicines','Lab Tests','Documents','Partners'] as $cat)
                <span class="badge badge-neutral" style="font-size:.8rem;padding:.35rem .75rem;">{{ $cat }}</span>
            @endforeach
        </div>
    </div>

@elseif($total === 0)
    {{-- No results --}}
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="search-x"></i></div>
        <h3>No results for "{{ $query }}"</h3>
        <p>Try different keywords or check your spelling.</p>
    </div>

@else
    {{-- Result count summary --}}
    <div style="margin-bottom:1rem;font-size:.85rem;color:var(--p-text-muted);">
        <strong style="color:var(--p-text);">{{ $total }}</strong> result{{ $total !== 1 ? 's' : '' }} for
        <strong style="color:var(--p-text);">"{{ $query }}"</strong>
        @foreach($counts as $type => $cnt)
            &nbsp;·&nbsp;<span class="badge badge-neutral" style="font-size:.72rem;">{{ ucfirst(str_replace('_',' ',$type)) }}: {{ $cnt }}</span>
        @endforeach
    </div>

    {{-- Type icon map --}}
    @php
        $typeIcon = [
            'patient'   => 'user',
            'facility'  => 'building-2',
            'document'  => 'file-text',
            'medicine'  => 'pill',
            'lab_test'  => 'flask-conical',
            'partner'   => 'handshake',
            'message'   => 'message-square',
        ];
        $typeBadge = [
            'patient'   => 'badge-primary',
            'facility'  => 'badge-neutral',
            'document'  => 'badge-warning',
            'medicine'  => 'badge-success',
            'lab_test'  => 'badge-neutral',
            'partner'   => 'badge-neutral',
            'message'   => 'badge-neutral',
        ];
        $typeLabel = [
            'patient'   => 'Patient',
            'facility'  => 'Facility',
            'document'  => 'Document',
            'medicine'  => 'Medicine',
            'lab_test'  => 'Lab Test',
            'partner'   => 'Partner',
            'message'   => 'Message',
        ];
    @endphp

    @foreach($grouped as $type => $items)
    <div class="panel" style="margin-bottom:1rem;">
        <div style="padding:.75rem 1.25rem;border-bottom:1px solid var(--p-border);display:flex;align-items:center;gap:.5rem;">
            <i data-lucide="{{ $typeIcon[$type] ?? 'circle' }}" style="width:16px;height:16px;color:var(--p-primary);"></i>
            <span style="font-weight:600;font-size:.9rem;">{{ $typeLabel[$type] ?? ucfirst($type) }}</span>
            <span class="badge badge-neutral" style="font-size:.72rem;margin-left:4px;">{{ $items->count() }}</span>
        </div>
        <div class="panel-body" style="padding:0;">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead><tr>
                        <th>Title</th><th>Details</th><th>Metadata</th>
                    </tr></thead>
                    <tbody>
                        @foreach($items as $item)
                        <tr>
                            <td style="font-weight:500;">
                                <div style="display:flex;align-items:center;gap:.5rem;">
                                    <i data-lucide="{{ $typeIcon[$type] ?? 'circle' }}" style="width:13px;height:13px;color:var(--p-text-muted);flex-shrink:0;"></i>
                                    {{ $item['title'] }}
                                </div>
                            </td>
                            <td style="font-size:.82rem;color:var(--p-text-muted);">{{ $item['subtitle'] ?? '—' }}</td>
                            <td style="font-size:.78rem;">
                                @foreach($item['metadata'] ?? [] as $mk => $mv)
                                    @if($mv !== null && $mv !== '')
                                        <span style="margin-right:.35rem;">
                                            <span style="color:var(--p-text-muted);">{{ $mk }}:</span>
                                            <code style="font-size:.75rem;">{{ is_array($mv) ? json_encode($mv) : $mv }}</code>
                                        </span>
                                    @endif
                                @endforeach
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endforeach
@endif
@endsection
