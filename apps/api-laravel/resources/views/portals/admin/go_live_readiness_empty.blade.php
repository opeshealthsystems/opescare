@extends('layouts.portal')
@section('title', 'Facility Go-Live Readiness')
@section('sidebar') @include('portals.admin.control_center._sidebar') @endsection

@section('content')

<div class="breadcrumb">
    <span>Go-Live Readiness</span>
</div>

<div class="page-head">
    <h2>Facility go-live readiness</h2>
</div>

<div class="panel">
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="rocket"></i></div>
        <h3>No facilities registered</h3>
        <p>Once a facility is onboarded, its go-live readiness checklist will appear here.</p>
    </div>
</div>

@endsection
