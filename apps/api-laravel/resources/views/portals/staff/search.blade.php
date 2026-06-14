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
<form method="GET" action="{{ route('portals.staff.search') }}" class="filter-bar">
    <label class="filter-search">
        <i data-lucide="search"></i>
        <input type="text" name="q" value="{{ $query }}"
            placeholder="Search patients, medicines, facilities, documents…"
            aria-label="Search the platform"
            autofocus>
    </label>
    <button type="submit" class="btn btn-primary btn-sm">Search</button>
    @if($query)
        <a href="{{ route('portals.staff.search') }}" class="btn btn-ghost btn-sm">Clear</a>
    @endif
</form>

@if($query === '')
    {{-- Empty state before search --}}
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="search"></i></div>
        <h3>Search the platform</h3>
        <p>Enter a keyword above to search across facilities, medicines, lab tests, documents, and partners.</p>
        <div class="row-actions-inline mt-6">
            @foreach(['Facilities','Medicines','Lab Tests','Documents','Partners'] as $cat)
                <span class="badge badge-neutral">{{ $cat }}</span>
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
    <div class="filter-bar">
        <span class="td-muted">
            <strong class="kv-strong">{{ $total }}</strong> result{{ $total !== 1 ? 's' : '' }} for
            <strong class="kv-strong">"{{ $query }}"</strong>
        </span>
        @foreach($counts as $type => $cnt)
            <span class="badge badge-neutral badge-sm">{{ ucfirst(str_replace('_',' ',$type)) }}: {{ $cnt }}</span>
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
    <div class="panel mb-4">
        <div class="panel-header">
            <h3 class="panel-title">
                <i data-lucide="{{ $typeIcon[$type] ?? 'circle' }}"></i>
                {{ $typeLabel[$type] ?? ucfirst($type) }}
                <span class="badge badge-neutral badge-sm">{{ $items->count() }}</span>
            </h3>
        </div>
        <div class="panel-body panel-body--flush">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead><tr>
                        <th>Title</th><th>Details</th><th>Metadata</th>
                    </tr></thead>
                    <tbody>
                        @foreach($items as $item)
                        <tr>
                            <td data-label="Title">
                                <span class="cell-with-icon">
                                    <i data-lucide="{{ $typeIcon[$type] ?? 'circle' }}"></i>
                                    <span class="td-strong">{{ $item['title'] }}</span>
                                </span>
                            </td>
                            <td data-label="Details" class="td-muted">{{ $item['subtitle'] ?? '—' }}</td>
                            <td data-label="Metadata">
                                @foreach($item['metadata'] ?? [] as $mk => $mv)
                                    @if($mv !== null && $mv !== '')
                                        <span class="td-muted">{{ $mk }}:</span>
                                        <code class="mono">{{ is_array($mv) ? json_encode($mv) : $mv }}</code>
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
