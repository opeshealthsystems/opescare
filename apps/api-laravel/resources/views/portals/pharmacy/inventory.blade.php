@extends('layouts.portal')
@section('title', __('public.pharmacy_portal.page_title', [], app()->getLocale()) ?: 'Drug Inventory')
@section('sidebar_role_badge')
<div class="sidebar-role-badge">
    <i data-lucide="pill"></i>
    {{ __('public.pharmacy_portal.role_badge', [], app()->getLocale()) ?: 'Pharmacy' }}
</div>
@endsection
@section('sidebar_user_role', __('public.pharmacy_portal.role_label', [], app()->getLocale()) ?: 'Pharmacist')
@section('sidebar_nav')
@include('portals.pharmacy._sidebar')
@endsection
@section('breadcrumb_home', __('public.pharmacy_portal.breadcrumb_home', [], app()->getLocale()) ?: 'Pharmacy Portal')
@section('breadcrumb_home_url', route('portals.pharmacy.dashboard'))
@section('breadcrumb_section', __('public.pharmacy_portal.breadcrumb_section_inventory', [], app()->getLocale()) ?: 'Drug Inventory')
@php $l = app()->getLocale(); @endphp

@section('content')

<div class="page-head">
    <h2>{{ __('public.pharmacy_portal.page_heading_inventory', [], $l) ?: 'Drug inventory' }}</h2>
    <p class="page-subtitle">{{ __('public.pharmacy_portal.page_subtitle_inventory', [], $l) ?: 'Current stock levels, expiries, and availability.' }}</p>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.staff.inventory.pharmacy') }}" class="btn btn-secondary btn-sm">
        <i data-lucide="settings"></i> {{ __('public.pharmacy_portal.btn_manage_stock', [], $l) ?: 'Manage stock' }}
    </a>
</div>

<form method="GET" class="filter-bar">
    <label class="filter-search">
        <i data-lucide="search"></i>
        <input type="text" name="search" placeholder="{{ __('public.pharmacy_portal.ph_search_drug', [], $l) ?: 'Drug or generic name…' }}" value="{{ request('search') }}" aria-label="{{ __('public.aria_search_drugs') }}">
    </label>
    <select name="stock_status" class="filter-select" aria-label="{{ __('public.aria_stock_status') }}" onchange="this.form.submit()">
        <option value="">{{ __('public.pharmacy_portal.filter_all_stock', [], $l) ?: 'All stock' }}</option>
        <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>{{ __('public.pharmacy_portal.filter_in_stock', [], $l) ?: 'In stock' }}</option>
        <option value="low_stock" {{ request('stock_status') === 'low_stock' ? 'selected' : '' }}>{{ __('public.pharmacy_portal.filter_low_stock', [], $l) ?: 'Low stock' }}</option>
        <option value="out_of_stock" {{ request('stock_status') === 'out_of_stock' ? 'selected' : '' }}>{{ __('public.pharmacy_portal.filter_out_of_stock', [], $l) ?: 'Out of stock' }}</option>
    </select>
    <button type="submit" class="btn btn-secondary btn-sm"><i data-lucide="filter"></i> {{ __('public.pharmacy_portal.btn_filter', [], $l) ?: 'Filter' }}</button>
    @if(request()->hasAny(['stock_status','search']))
        <a href="{{ route('portals.pharmacy.inventory') }}" class="btn btn-ghost btn-sm">{{ __('public.pharmacy_portal.btn_clear', [], $l) ?: 'Clear' }}</a>
    @endif
</form>

<div class="panel">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ __('public.pharmacy_portal.col_drug_name', [], $l) ?: 'Drug name' }}</th>
                    <th>{{ __('public.pharmacy_portal.col_generic', [], $l) ?: 'Generic' }}</th>
                    <th>{{ __('public.pharmacy_portal.col_form_strength', [], $l) ?: 'Form / strength' }}</th>
                    <th>{{ __('public.pharmacy_portal.col_qty', [], $l) ?: 'Qty' }}</th>
                    <th>{{ __('public.pharmacy_portal.col_stock_status', [], $l) ?: 'Stock status' }}</th>
                    <th>{{ __('public.pharmacy_portal.col_flags', [], $l) ?: 'Flags' }}</th>
                    <th>{{ __('public.pharmacy_portal.col_last_updated', [], $l) ?: 'Last updated' }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($drugs as $drug)
                <tr>
                    <td data-label="{{ __('public.pharmacy_portal.col_drug_name', [], $l) ?: 'Drug name' }}" class="td-strong">{{ $drug->medicine_name }}</td>
                    <td data-label="{{ __('public.pharmacy_portal.col_generic', [], $l) ?: 'Generic' }}" class="td-muted">{{ $drug->generic_name ?? '—' }}</td>
                    <td data-label="{{ __('public.pharmacy_portal.col_form_strength', [], $l) ?: 'Form / strength' }}">{{ $drug->form }} {{ $drug->strength }}</td>
                    <td data-label="{{ __('public.pharmacy_portal.col_qty', [], $l) ?: 'Qty' }}" class="td-strong">{{ $drug->available_quantity }}</td>
                    <td data-label="{{ __('public.pharmacy_portal.col_stock_status', [], $l) ?: 'Stock status' }}">
                        <span class="badge badge-{{ match($drug->stock_status) { 'in_stock' => 'success', 'low_stock' => 'warning', 'out_of_stock' => 'danger', default => 'neutral' } }}">
                            @enum($drug->stock_status, 'stock_status')
                        </span>
                    </td>
                    <td data-label="{{ __('public.pharmacy_portal.col_flags', [], $l) ?: 'Flags' }}">
                        @if($drug->is_expired)<span class="badge badge-danger">{{ __('public.pharmacy_portal.flag_expired', [], $l) ?: 'Expired' }}</span>@endif
                        @if($drug->is_recalled)<span class="badge badge-danger">{{ __('public.pharmacy_portal.flag_recalled', [], $l) ?: 'Recalled' }}</span>@endif
                        @if($drug->is_quarantined)<span class="badge badge-warning">{{ __('public.pharmacy_portal.flag_quarantined', [], $l) ?: 'Quarantined' }}</span>@endif
                        @if(!$drug->is_expired && !$drug->is_recalled && !$drug->is_quarantined)<span class="td-muted">—</span>@endif
                    </td>
                    <td data-label="{{ __('public.pharmacy_portal.col_last_updated', [], $l) ?: 'Last updated' }}" class="td-muted">{{ $drug->last_stock_update?->format('d M Y') ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="td-muted empty-cell">{{ __('public.pharmacy_portal.no_drugs', [], $l) ?: 'No drugs found.' }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="panel-body">{{ $drugs->links() }}</div>
</div>

@endsection
