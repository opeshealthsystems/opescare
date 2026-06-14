@extends('layouts.portal')

@section('title', 'Sample Tracking')

@section('sidebar_role_badge')
<div class="sidebar-role-badge">
    <i data-lucide="microscope"></i>
    Laboratory
</div>
@endsection
@section('sidebar_user_role', 'Lab Technician')

@section('sidebar_nav')
@include('portals.lab._sidebar')
@endsection

@section('breadcrumb_home', 'Lab Portal')
@section('breadcrumb_home_url', route('portals.lab.dashboard'))
@section('breadcrumb_section', 'Sample Tracking')

@section('content')

<div class="page-head">
    <h2>Sample tracking</h2>
    <p class="page-subtitle">Track samples from order through collection to the bench.</p>
</div>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif

<div class="field-grid">

    {{-- Awaiting Collection --}}
    <div class="panel">
        <div class="panel-header">
            <h3 class="panel-title"><i data-lucide="clock"></i> Awaiting collection ({{ $pending->count() }})</h3>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr><th>Test</th><th>Patient</th><th class="row-actions"></th></tr></thead>
                <tbody>
                @forelse($pending as $order)
                <tr>
                    <td data-label="Test">
                        <span class="td-strong">{{ $order->test_name }}</span>
                        <div class="td-muted">Ordered {{ $order->ordered_at?->diffForHumans() ?? '' }}</div>
                    </td>
                    <td data-label="Patient">
                        {{ $order->patient?->full_name ?? '—' }}
                        @if($order->urgency === 'urgent') <span class="badge badge-danger badge-sm">Urgent</span>@endif
                    </td>
                    <td class="row-actions" data-label="">
                        <form method="POST" action="{{ route('portals.lab.orders.collect', $order->id) }}" class="inline-form">@csrf
                            <button type="submit" class="btn btn-primary btn-sm"><i data-lucide="test-tube"></i> Collect</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="td-muted empty-cell">No samples awaiting collection.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Collected — Ready to Process --}}
    <div class="panel">
        <div class="panel-header">
            <h3 class="panel-title"><i data-lucide="test-tube"></i> Collected — ready to process ({{ $collected->count() }})</h3>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr><th>Test</th><th>Patient</th><th class="row-actions"></th></tr></thead>
                <tbody>
                @forelse($collected as $order)
                <tr>
                    <td data-label="Test">
                        <span class="td-strong">{{ $order->test_name }}</span>
                        <div class="td-muted">Collected {{ $order->collected_at?->diffForHumans() ?? '' }}</div>
                    </td>
                    <td data-label="Patient">
                        {{ $order->patient?->full_name ?? '—' }}
                        @if($order->urgency === 'urgent') <span class="badge badge-danger badge-sm">Urgent</span>@endif
                    </td>
                    <td class="row-actions" data-label="">
                        <form method="POST" action="{{ route('portals.lab.orders.process', $order->id) }}" class="inline-form">@csrf
                            <button type="submit" class="btn btn-secondary btn-sm"><i data-lucide="loader"></i> Process</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="td-muted empty-cell">No collected samples waiting.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection
