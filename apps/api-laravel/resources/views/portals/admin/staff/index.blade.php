@extends('layouts.portal')
@section('title', 'All Staff')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Staff')
@section('content')

<div class="breadcrumb">
    <a href="{{ route('admin.staff.index') }}">Staff</a>
    <i data-lucide="chevron-right"></i>
    <span>Directory</span>
</div>

<div class="page-head">
    <h2>All Staff</h2>
    <div class="page-head__spacer"></div>
</div>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

<form method="GET" action="{{ route('admin.staff.index') }}" class="filter-bar">
    <label class="filter-search">
        <i data-lucide="search"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or staff number" aria-label="Search staff">
    </label>
    <select name="status" class="filter-select" aria-label="Status" onchange="this.form.submit()">
        <option value="">All</option>
        <option value="active" {{ request('status')==='active'?'selected':'' }}>Active</option>
        <option value="suspended" {{ request('status')==='suspended'?'selected':'' }}>Suspended</option>
        <option value="inactive" {{ request('status')==='inactive'?'selected':'' }}>Inactive</option>
    </select>
    <button type="submit" class="btn btn-secondary btn-sm"><i data-lucide="filter"></i> Filter</button>
    <a href="{{ route('admin.staff.index') }}" class="btn btn-ghost btn-sm">Reset</a>
</form>

<div class="panel">
    <div class="panel-header">
        <h3 class="panel-title"><i data-lucide="stethoscope"></i> {{ $staff->total() }} staff members</h3>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Staff #</th>
                    <th>Name</th>
                    <th>Specialty</th>
                    <th>Facility</th>
                    <th>Status</th>
                    <th class="row-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($staff as $s)
                <tr>
                    <td data-label="Staff #"><span class="td-mono">{{ $s->staff_number??'—' }}</span></td>
                    <td data-label="Name"><span class="td-strong">{{ ($s->first_name??'').' '.($s->last_name??'') }}</span></td>
                    <td data-label="Specialty" class="td-muted">{{ $s->specialty??$s->designation??'—' }}</td>
                    <td data-label="Facility" class="td-muted">{{ $s->facility?->name??'—' }}</td>
                    <td data-label="Status">
                        @if(($s->status??'')==='active')<span class="badge badge-success">Active</span>
                        @elseif(($s->status??'')==='suspended')<span class="badge badge-danger">Suspended</span>
                        @else<span class="badge badge-warning">{{ ucfirst($s->status??'inactive') }}</span>@endif
                    </td>
                    <td class="row-actions" data-label="Actions">
                        @if(($s->status??'')!=='active')
                        <form method="POST" action="{{ route('admin.staff.activate',$s->id) }}" class="inline-form">@csrf
                            <button type="submit" class="icon-btn" aria-label="Activate staff" title="Activate"><i data-lucide="check-circle"></i></button>
                        </form>
                        @endif
                        @if(($s->status??'')!=='suspended')
                        <form method="POST" action="{{ route('admin.staff.suspend',$s->id) }}" class="inline-form">@csrf
                            <button type="submit" class="icon-btn" aria-label="Suspend staff" title="Suspend"><i data-lucide="ban"></i></button>
                        </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="td-muted empty-cell">No staff found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="panel-body">{{ $staff->links() }}</div>
</div>

@endsection
