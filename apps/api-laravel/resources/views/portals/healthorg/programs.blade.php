@extends('layouts.portal')

@section('title', 'Health Programs')

@section('sidebar_role_badge')
<div class="sidebar-role-badge">
    <i data-lucide="heart-handshake"></i>
    Health Org
</div>
@endsection
@section('sidebar_user_role', 'Health Org Admin')

@section('sidebar_nav')
@include('portals.healthorg._sidebar')
@endsection

@section('breadcrumb_home', 'Health Org Portal')
@section('breadcrumb_home_url', route('portals.healthorg.dashboard'))
@section('breadcrumb_section', 'Programs')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Health Programs</h1>
        <p class="page-subtitle">Facilities and sites associated with your health programs.</p>
    </div>
</div>

<div class="panel">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Facility / Site</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th class="row-actions">Care Map</th>
                </tr>
            </thead>
            <tbody>
                @forelse($facilities as $facility)
                <tr>
                    <td data-label="Facility / Site"><span class="td-strong">{{ $facility->name }}</span></td>
                    <td data-label="Type">
                        <span class="badge badge-neutral">{{ ucfirst(str_replace('_', ' ', $facility->type)) }}</span>
                    </td>
                    <td data-label="Status">
                        <span class="badge badge-{{ $facility->status === 'active' ? 'success' : 'neutral' }}">
                            {{ ucfirst($facility->status) }}
                        </span>
                    </td>
                    <td data-label="Care Map" class="row-actions">
                        <a href="{{ route('public.care-map.profile', $facility->id) }}" target="_blank" class="btn btn-secondary btn-sm">
                            <i data-lucide="external-link"></i> View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="td-muted empty-cell">No facilities found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="panel-body">{{ $facilities->links() }}</div>
</div>

@endsection
