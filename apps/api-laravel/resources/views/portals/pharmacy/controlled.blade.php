@extends('layouts.portal')

@section('title', 'Controlled Substances')

@section('sidebar_role_badge')
<div class="sidebar-role-badge" style="background:rgba(16,185,129,.15);border-color:rgba(16,185,129,.4);color:#34d399;">
    <i data-lucide="pill" style="width:.75rem;height:.75rem;display:inline;vertical-align:middle;margin-right:4px;"></i>
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

<div class="page-header">
    <div>
        <h1 class="page-title">Controlled Substances</h1>
        <p class="page-subtitle">Stock overview and recent dispensing log for controlled drugs.</p>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">

    <div class="card" style="overflow:hidden;">
        <div class="card-header" style="font-weight:700;">Controlled Drug Stock</div>
        <div class="card-body" style="padding:0;overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Drug</th>
                        <th>Form</th>
                        <th>Qty</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($controlled as $drug)
                    <tr>
                        <td style="font-weight:600;">{{ $drug->medicine_name }}</td>
                        <td style="font-size:.83rem;color:#64748b;">{{ $drug->form }} {{ $drug->strength }}</td>
                        <td style="font-weight:700;">{{ $drug->available_quantity }}</td>
                        <td>
                            <span class="badge badge-{{ match($drug->stock_status) { 'in_stock' => 'success', 'low_stock' => 'warning', 'out_of_stock' => 'danger', default => 'default' } }}">
                                {{ ucfirst(str_replace('_', ' ', $drug->stock_status)) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align:center;padding:2rem;color:#94a3b8;">No controlled substances on record.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card" style="overflow:hidden;">
        <div class="card-header" style="font-weight:700;">Recent Dispensing Log</div>
        <div class="card-body" style="padding:0;overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Items</th>
                        <th>Dispensed</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentRx as $rx)
                    <tr>
                        <td style="font-weight:600;font-size:.85rem;">{{ $rx->patient?->full_name ?? '—' }}</td>
                        <td style="font-size:.8rem;color:#64748b;">{{ $rx->items->count() }} item(s)</td>
                        <td style="font-size:.8rem;color:#64748b;">{{ $rx->dispensed_at?->format('d M Y H:i') ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" style="text-align:center;padding:2rem;color:#94a3b8;">No dispensing records.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection
