@extends('layouts.portal')
@section('title', 'Items Catalog — Supply Chain')
@section('sidebar') @include('portals.staff.supply_chain._sidebar') @endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">Items Catalog</h1>
            <p class="portal-page-subtitle">All inventory items tracked at this facility</p>
        </div>
        <button class="btn btn--primary" onclick="openModal('createModal')">
            <i data-lucide="plus"></i> Add Item
        </button>
    </div>

    @if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
    @if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div></div>@endif

    {{-- Filters --}}
    <form method="GET" class="filter-bar">
        <label class="filter-search">
            <i data-lucide="search"></i>
            <input type="text" name="search" placeholder="Name or code…" value="{{ request('search') }}">
        </label>
        <select name="category" class="filter-select">
            <option value="">All categories</option>
            @foreach($categories as $key => $label)
                <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="status" class="filter-select">
            <option value="">All statuses</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        <button type="submit" class="btn btn-primary btn-sm"><i data-lucide="filter"></i> Filter</button>
        <a href="{{ route('portals.staff.supply.items') }}" class="btn btn-ghost btn-sm">Reset</a>
    </form>

    <div class="portal-card">
        <div class="portal-card__body panel-body--flush">
            <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th>Reorder Level</th>
                        <th>Track Expiry</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td data-label="Name">
                                <div class="td-strong">{{ $item->name }}</div>
                                @if($item->description)
                                    <div class="td-muted">{{ Str::limit($item->description,50) }}</div>
                                @endif
                            </td>
                            <td data-label="Code"><span class="mono">{{ $item->code ?: '—' }}</span></td>
                            <td data-label="Category">{{ $categories[$item->category] ?? ucfirst($item->category) }}</td>
                            <td data-label="Unit">{{ $item->unit }}</td>
                            <td data-label="Reorder Level" class="td-strong">{{ $item->reorder_level }}</td>
                            <td data-label="Track Expiry">
                                @if($item->track_expiry)
                                    <span class="badge badge-success">Yes</span>
                                @else
                                    <span class="td-muted">No</span>
                                @endif
                            </td>
                            <td data-label="Status"><span class="badge badge--{{ $item->status === 'active' ? 'success' : 'warning' }}">{{ $item->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7">
                            <div class="empty-state">
                                <div class="empty-state-icon"><i data-lucide="list"></i></div>
                                <p>No items yet. Add your first item.</p>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
        @if($items->hasPages())<div class="panel-footer">{{ $items->links() }}</div>@endif
    </div>
</div>

{{-- Create Item Modal --}}
<div id="createModal" class="modal-backdrop mt-6" hidden onclick="if(event.target===this)closeModal('createModal')">
    <div class="modal modal--lg" role="dialog" aria-modal="true">
        <h3 class="modal__title"><i data-lucide="plus-circle"></i> Add Inventory Item</h3>
        <form method="POST" action="{{ route('portals.staff.supply.items.store') }}">
            @csrf
            <div class="modal__body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label form-label-required">Item Name</label>
                        <input type="text" name="name" class="form-control" required maxlength="150" value="{{ old('name') }}" placeholder="e.g. Paracetamol 500mg">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Item Code</label>
                        <input type="text" name="code" class="form-control" maxlength="50" value="{{ old('code') }}" placeholder="e.g. PARA-500">
                    </div>
                    <div class="form-group">
                        <label class="form-label form-label-required">Unit</label>
                        <input type="text" name="unit" class="form-control" required value="{{ old('unit','unit') }}" placeholder="tablet, vial, box…">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label form-label-required">Category</label>
                        <select name="category" class="form-control" required>
                            @foreach($categories as $key => $label)
                                <option value="{{ $key }}" {{ old('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Reorder Level</label>
                        <input type="number" name="reorder_level" class="form-control" min="0" value="{{ old('reorder_level',0) }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Unit Cost</label>
                        <input type="number" name="unit_cost" class="form-control" min="0" step="0.0001" value="{{ old('unit_cost') }}" placeholder="0.00">
                    </div>
                </div>
                <label class="form-check">
                    <input type="checkbox" name="track_expiry" value="1" {{ old('track_expiry') ? 'checked' : '' }}> Track Expiry
                </label>
                <label class="form-check">
                    <input type="checkbox" name="track_batch" value="1" {{ old('track_batch') ? 'checked' : '' }}> Track Batch/Lot
                </label>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--outline" onclick="closeModal('createModal')">Cancel</button>
                <button type="submit" class="btn btn--primary">Add Item</button>
            </div>
        </form>
    </div>
</div>

@if($errors->any())<script>document.addEventListener('DOMContentLoaded',()=>openModal('createModal'));</script>@endif
<script>
function openModal(id){ document.getElementById(id).removeAttribute('hidden'); }
function closeModal(id){ document.getElementById(id).setAttribute('hidden',''); }
</script>
@endsection
