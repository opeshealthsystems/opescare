@extends('layouts.portal')
@section('title', 'All Facilities')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Facilities')
@section('content')

<div class="breadcrumb">
    <a href="{{ route('admin.facilities.index') }}">Facilities</a>
    <i data-lucide="chevron-right"></i>
    <span>Directory</span>
</div>

<div class="page-head">
    <h2>Facilities &amp; organizations</h2>
    <div class="page-head__spacer"></div>
    <a href="{{ route('admin.facilities.index') }}#add" class="btn btn-primary">
        <i data-lucide="plus"></i> Add facility
    </a>
</div>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

<form method="GET" action="{{ route('admin.facilities.index') }}" class="filter-bar">
    <label class="filter-search">
        <i data-lucide="search"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or license" aria-label="Search facilities">
    </label>
    <select name="type" class="filter-select" aria-label="Type" onchange="this.form.submit()">
        <option value="">All types</option>
        @foreach(['hospital','clinic','laboratory','pharmacy','radiology','specialist'] as $t)
        <option value="{{ $t }}" {{ request('type')===$t?'selected':'' }}>{{ ucfirst($t) }}</option>
        @endforeach
    </select>
    <select name="status" class="filter-select" aria-label="Status" onchange="this.form.submit()">
        <option value="">All statuses</option>
        <option value="active" {{ request('status')==='active'?'selected':'' }}>Active</option>
        <option value="pending_approval" {{ request('status')==='pending_approval'?'selected':'' }}>Pending</option>
        <option value="suspended" {{ request('status')==='suspended'?'selected':'' }}>Suspended</option>
    </select>
    <button type="submit" class="btn btn-secondary btn-sm"><i data-lucide="filter"></i> Filter</button>
    <a href="{{ route('admin.facilities.index') }}" class="btn btn-ghost btn-sm">Reset</a>
</form>

<div class="panel">
    <div class="panel-header">
        <h3 class="panel-title"><i data-lucide="building-2"></i> {{ $facilities->total() }} facilities</h3>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Facility</th>
                    <th>Type</th>
                    <th>Region</th>
                    <th>Status</th>
                    <th class="row-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($facilities as $f)
                <tr>
                    <td data-label="Facility">
                        @php $typeIcon = ['hospital'=>'building-2','laboratory'=>'flask-conical','pharmacy'=>'pill','radiology'=>'scan'][$f->type] ?? 'building'; @endphp
                        <span class="cell-with-icon">
                            <i data-lucide="{{ $typeIcon }}"></i>
                            <span class="td-strong">{{ $f->name }}</span>
                        </span>
                    </td>
                    <td data-label="Type">{{ ucfirst($f->type ?? '—') }}</td>
                    <td data-label="Region">{{ $f->region ?? '—' }}</td>
                    <td data-label="Status">
                        @if($f->status==='active')<span class="badge badge-success">Active</span>
                        @elseif($f->status==='suspended')<span class="badge badge-danger">Suspended</span>
                        @elseif($f->status==='pending_approval')<span class="badge badge-warning">Pending</span>
                        @else<span class="badge badge-neutral">{{ ucfirst($f->status ?? 'pending') }}</span>@endif
                    </td>
                    <td class="row-actions" data-label="Actions">
                        <a href="{{ route('admin.facilities.show', $f->id) }}" class="icon-btn" aria-label="View facility" title="View">
                            <i data-lucide="eye"></i>
                        </a>
                        @if(($f->status ?? '') !== 'active')
                        <form method="POST" action="{{ route('admin.facilities.approve', $f->id) }}" class="inline-form">@csrf
                            <button type="submit" class="icon-btn" aria-label="Approve facility" title="Approve"><i data-lucide="check-circle"></i></button>
                        </form>
                        @endif
                        @if(($f->status ?? '') !== 'suspended')
                        <form method="POST" action="{{ route('admin.facilities.suspend', $f->id) }}" class="inline-form">@csrf
                            <button type="submit" class="icon-btn" aria-label="Suspend facility" title="Suspend"><i data-lucide="pause-circle"></i></button>
                        </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="td-muted empty-cell">No facilities found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="panel-body">{{ $facilities->links() }}</div>
</div>

@endsection
