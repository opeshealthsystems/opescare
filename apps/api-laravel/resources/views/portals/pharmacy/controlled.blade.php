@extends('layouts.portal')

@section('title', 'Controlled Substances')

@section('sidebar_role_badge')
<div class="sidebar-role-badge">
    <i data-lucide="pill"></i>
    Pharmacy
</div>
@endsection
@section('sidebar_user_role', 'Pharmacist')

@section('sidebar_nav')
@include('portals.pharmacy._sidebar')
@endsection

@section('breadcrumb_home', 'Pharmacy Portal')
@section('breadcrumb_home_url', route('portals.pharmacy.dashboard'))
@section('breadcrumb_section', 'Controlled Substances')

@section('content')

<div class="page-head">
    <h2>Controlled substances</h2>
    <p class="page-subtitle">Stock overview and recent dispensing log for controlled drugs.</p>
</div>

<div class="field-grid">

    <div class="panel">
        <div class="panel-header"><h3 class="panel-title"><i data-lucide="lock"></i> Controlled drug stock</h3></div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr><th>Drug</th><th>Form</th><th>Qty</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($controlled as $drug)
                    <tr>
                        <td data-label="Drug" class="td-strong">{{ $drug->medicine_name }}</td>
                        <td data-label="Form" class="td-muted">{{ $drug->form }} {{ $drug->strength }}</td>
                        <td data-label="Qty" class="td-strong">{{ $drug->available_quantity }}</td>
                        <td data-label="Status">
                            <span class="badge badge-{{ match($drug->stock_status) { 'in_stock' => 'success', 'low_stock' => 'warning', 'out_of_stock' => 'danger', default => 'neutral' } }}">
                                {{ ucfirst(str_replace('_', ' ', $drug->stock_status)) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="td-muted empty-cell">No controlled substances on record.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header"><h3 class="panel-title"><i data-lucide="history"></i> Recent dispensing log</h3></div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr><th>Patient</th><th>Items</th><th>Dispensed</th></tr></thead>
                <tbody>
                    @forelse($recentRx as $rx)
                    <tr>
                        <td data-label="Patient" class="td-strong">{{ $rx->patient?->full_name ?? '—' }}</td>
                        <td data-label="Items" class="td-muted">{{ $rx->items->count() }} item(s)</td>
                        <td data-label="Dispensed" class="td-muted">{{ $rx->dispensed_at?->format('d M Y H:i') ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="td-muted empty-cell">No dispensing records.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection
