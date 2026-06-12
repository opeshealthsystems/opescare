@extends('layouts.portal')

@section('title', 'Health Programs')

@section('sidebar_role_badge')
<div class="sidebar-role-badge" style="background:rgba(245,158,11,.15);border-color:rgba(245,158,11,.4);color:#fbbf24;">
    <i data-lucide="heart-handshake" style="width:.75rem;height:.75rem;display:inline;vertical-align:middle;margin-right:4px;"></i>
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

<div class="card" style="overflow:hidden;">
    <div class="card-body" style="padding:0;overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Facility / Site</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Care Map</th>
                </tr>
            </thead>
            <tbody>
                @forelse($facilities as $facility)
                <tr>
                    <td style="font-weight:600;">{{ $facility->name }}</td>
                    <td>
                        <span class="badge badge-default">{{ ucfirst(str_replace('_', ' ', $facility->type)) }}</span>
                    </td>
                    <td>
                        <span class="badge badge-{{ $facility->status === 'active' ? 'success' : 'default' }}">
                            {{ ucfirst($facility->status) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('public.care-map.profile', $facility->id) }}" target="_blank" class="btn btn-outline btn-sm">
                            <i data-lucide="external-link" style="width:12px;height:12px;"></i>
                            View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align:center;padding:2rem;color:#94a3b8;">No facilities found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top:1rem;">{{ $facilities->links() }}</div>

@endsection
