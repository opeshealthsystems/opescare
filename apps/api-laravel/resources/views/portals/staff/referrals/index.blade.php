@extends('layouts.portal')

@section('title', 'Referrals — OpesCare Staff Portal')

@section('breadcrumb_home', __('public.staff_portal.title', [], app()->getLocale()) ?: 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Referrals')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Referrals</h1>
        <p class="page-subtitle">Manage patient referrals between facilities and specialists.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('portals.staff.referrals.create') }}" class="btn btn-primary">
            <i data-lucide="plus"></i>
            New Referral
        </a>
    </div>
</div>

<!-- Filters -->
<div class="panel mb-6" style="margin-bottom:var(--p-space-6);">
    <form method="GET" action="{{ route('portals.staff.referrals') }}">
        <div class="filter-bar">
            <div class="form-group" style="flex:1;min-width:180px;">
                <div class="form-search">
                    <span class="search-icon"><i data-lucide="search"></i></span>
                    <input type="text" name="patient_id" class="form-control" placeholder="Patient Health ID…" value="{{ request('patient_id') }}" aria-label="Filter by patient health ID">
                </div>
            </div>
            <div class="form-group" style="min-width:150px;">
                <select name="status" class="form-control" aria-label="Filter by status">
                    <option value="">All Status</option>
                    <option value="draft"     @selected(request('status')==='draft')>Draft</option>
                    <option value="sent"      @selected(request('status')==='sent')>Sent</option>
                    <option value="accepted"  @selected(request('status')==='accepted')>Accepted</option>
                    <option value="rejected"  @selected(request('status')==='rejected')>Rejected</option>
                    <option value="completed" @selected(request('status')==='completed')>Completed</option>
                    <option value="cancelled" @selected(request('status')==='cancelled')>Cancelled</option>
                    <option value="expired"   @selected(request('status')==='expired')>Expired</option>
                </select>
            </div>
            <div class="form-group" style="min-width:140px;">
                <select name="priority" class="form-control" aria-label="Filter by priority">
                    <option value="">All Priority</option>
                    <option value="routine"   @selected(request('priority')==='routine')>Routine</option>
                    <option value="urgent"    @selected(request('priority')==='urgent')>Urgent</option>
                    <option value="emergency" @selected(request('priority')==='emergency')>Emergency</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i data-lucide="filter"></i> Filter</button>
            <a href="{{ route('portals.staff.referrals') }}" class="btn btn-secondary"><i data-lucide="x"></i> Clear</a>
        </div>
    </form>
</div>

<!-- Referrals Table -->
<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title"><i data-lucide="send"></i> Referral Records</h2>
        <span class="badge badge-primary">{{ count($referrals) }}</span>
    </div>

    @if(count($referrals) === 0)
        <div class="empty-state">
            <div class="empty-state-icon"><i data-lucide="send"></i></div>
            <h3>No Referrals Found</h3>
            <p>No referrals match your filters. Create a new referral to get started.</p>
            <a href="{{ route('portals.staff.referrals.create') }}" class="btn btn-primary">
                <i data-lucide="plus"></i> New Referral
            </a>
        </div>
    @else
        <div class="table-wrapper">
            <table class="data-table" aria-label="Referrals list">
                <thead>
                    <tr>
                        <th>Referral ID</th>
                        <th>Patient</th>
                        <th>Priority</th>
                        <th>Referring Facility</th>
                        <th>Specialty</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($referrals as $referral)
                    <tr>
                        <td data-label="ID">
                            <span class="td-mono">{{ substr($referral->id, 0, 8) }}…</span>
                        </td>
                        <td data-label="Patient">
                            <span class="td-mono">{{ $referral->patient_id }}</span>
                        </td>
                        <td data-label="Priority">
                            @php
                                $prCls = match($referral->priority ?? 'routine') {
                                    'emergency' => 'badge-critical',
                                    'urgent'    => 'badge-danger',
                                    default     => 'badge-neutral',
                                };
                            @endphp
                            <span class="badge {{ $prCls }}">{{ ucfirst($referral->priority ?? 'routine') }}</span>
                        </td>
                        <td data-label="From">
                            <span class="td-muted">{{ $referral->referring_facility_id ?? '—' }}</span>
                        </td>
                        <td data-label="Specialty">
                            <span class="td-muted">{{ $referral->specialty ?? '—' }}</span>
                        </td>
                        <td data-label="Status">
                            @php
                                $stCls = match($referral->status ?? 'draft') {
                                    'accepted'  => 'badge-success',
                                    'completed' => 'badge-teal',
                                    'sent'      => 'badge-primary',
                                    'rejected'  => 'badge-danger',
                                    'cancelled' => 'badge-neutral',
                                    'expired'   => 'badge-neutral',
                                    default     => 'badge-warning',
                                };
                            @endphp
                            <span class="badge {{ $stCls }}">{{ ucfirst($referral->status ?? 'draft') }}</span>
                        </td>
                        <td data-label="Created">
                            <span class="td-muted">{{ $referral->created_at?->format('d M Y') ?? '—' }}</span>
                        </td>
                        <td data-label="Action" style="text-align:right;">
                            <a href="{{ route('portals.staff.referrals.show', $referral->id) }}" class="btn btn-ghost btn-sm">
                                <i data-lucide="eye"></i> View
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection
