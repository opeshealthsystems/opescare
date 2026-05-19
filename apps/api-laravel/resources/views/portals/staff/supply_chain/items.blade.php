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
            <i data-lucide="plus" style="width:15px;height:15px;"></i> Add Item
        </button>
    </div>

    @if(session('success'))<div class="alert alert--success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert--danger">{{ session('error') }}</div>@endif

    {{-- Filters --}}
    <div class="portal-card" style="margin-bottom:18px;">
        <div class="portal-card__body">
            <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
                <div>
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control form-control--sm" placeholder="Name or code…" value="{{ request('search') }}">
                </div>
                <div>
                    <label class="form-label">Category</label>
                    <select name="category" class="form-control form-control--sm">
                        <option value="">All</option>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control form-control--sm">
                        <option value="">All</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn--primary btn--sm">Filter</button>
                <a href="{{ route('portals.staff.supply.items') }}" class="btn btn--outline btn--sm">Reset</a>
            </form>
        </div>
    </div>

    <div class="portal-card">
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table">
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
                            <td>
                                <div style="font-weight:600;">{{ $item->name }}</div>
                                @if($item->description)
                                    <div style="font-size:0.76rem;color:#9ca3af;">{{ Str::limit($item->description,50) }}</div>
                                @endif
                            </td>
                            <td><code style="font-size:0.78rem;background:#f9fafb;padding:1px 6px;border-radius:4px;">{{ $item->code ?: '—' }}</code></td>
                            <td style="font-size:0.83rem;">{{ $categories[$item->category] ?? ucfirst($item->category) }}</td>
                            <td style="font-size:0.83rem;">{{ $item->unit }}</td>
                            <td style="font-size:0.85rem;font-weight:600;">{{ $item->reorder_level }}</td>
                            <td>
                                @if($item->track_expiry)
                                    <span class="badge badge--success">Yes</span>
                                @else
                                    <span style="color:#9ca3af;font-size:0.8rem;">No</span>
                                @endif
                            </td>
                            <td><span class="badge badge--{{ $item->status === 'active' ? 'success' : 'warning' }}">{{ $item->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align:center;padding:40px;color:#9ca3af;">
                            <i data-lucide="list" style="width:32px;height:32px;display:block;margin:0 auto 10px;"></i>
                            No items yet. Add your first item.
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())<div class="portal-card__footer">{{ $items->links() }}</div>@endif
    </div>
</div>

{{-- Create Item Modal --}}
<div id="createModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)closeModal('createModal')">
    <div class="modal-box" style="max-width:520px;width:95%;">
        <div class="modal-header">
            <h3 class="modal-title"><i data-lucide="plus-circle" style="width:18px;height:18px;"></i> Add Inventory Item</h3>
            <button class="modal-close" onclick="closeModal('createModal')">&times;</button>
        </div>
        <form method="POST" action="{{ route('portals.staff.supply.items.store') }}">
            @csrf
            <div class="modal-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label">Item Name <span style="color:red">*</span></label>
                        <input type="text" name="name" class="form-control" required maxlength="150" value="{{ old('name') }}" placeholder="e.g. Paracetamol 500mg">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Item Code</label>
                        <input type="text" name="code" class="form-control" maxlength="50" value="{{ old('code') }}" placeholder="e.g. PARA-500">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Unit <span style="color:red">*</span></label>
                        <input type="text" name="unit" class="form-control" required value="{{ old('unit','unit') }}" placeholder="tablet, vial, box…">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category <span style="color:red">*</span></label>
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
                    <div class="form-group">
                        <label class="form-label">Unit Cost</label>
                        <input type="number" name="unit_cost" class="form-control" min="0" step="0.0001" value="{{ old('unit_cost') }}" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                            <input type="checkbox" name="track_expiry" value="1" {{ old('track_expiry') ? 'checked' : '' }} style="accent-color:#0891b2;">
                            <span class="form-label" style="margin:0;">Track Expiry</span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                            <input type="checkbox" name="track_batch" value="1" {{ old('track_batch') ? 'checked' : '' }} style="accent-color:#0891b2;">
                            <span class="form-label" style="margin:0;">Track Batch/Lot</span>
                        </label>
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--outline" onclick="closeModal('createModal')">Cancel</button>
                <button type="submit" class="btn btn--primary">Add Item</button>
            </div>
        </form>
    </div>
</div>

@if($errors->any())<script>document.addEventListener('DOMContentLoaded',()=>openModal('createModal'));</script>@endif
<script>
function openModal(id){ document.getElementById(id).style.display='flex'; }
function closeModal(id){ document.getElementById(id).style.display='none'; }
</script>
@endsection
