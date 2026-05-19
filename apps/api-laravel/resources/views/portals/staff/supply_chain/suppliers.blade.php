@extends('layouts.portal')
@section('title', 'Suppliers — Supply Chain')
@section('sidebar') @include('portals.staff.supply_chain._sidebar') @endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">Suppliers</h1>
            <p class="portal-page-subtitle">Manage your procurement supplier list</p>
        </div>
        <button class="btn btn--primary" onclick="openModal('createModal')">
            <i data-lucide="plus" style="width:15px;height:15px;"></i> Add Supplier
        </button>
    </div>

    @if(session('success'))<div class="alert alert--success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert--danger">{{ session('error') }}</div>@endif

    <div class="portal-card">
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table">
                <thead>
                    <tr>
                        <th>Supplier</th>
                        <th>Contact</th>
                        <th>Phone / Email</th>
                        <th>Status</th>
                        <th>Added</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                        <tr>
                            <td>
                                <div style="font-weight:600;">{{ $supplier->name }}</div>
                                @if($supplier->code)
                                    <code style="font-size:0.76rem;background:#f9fafb;padding:1px 5px;border-radius:4px;">{{ $supplier->code }}</code>
                                @endif
                            </td>
                            <td style="font-size:0.83rem;">{{ $supplier->contact_person ?: '—' }}</td>
                            <td style="font-size:0.82rem;">
                                @if($supplier->phone)<div>{{ $supplier->phone }}</div>@endif
                                @if($supplier->email)<div style="color:#6b7280;">{{ $supplier->email }}</div>@endif
                                @if(!$supplier->phone && !$supplier->email)—@endif
                            </td>
                            <td>
                                <span class="badge badge--{{ $supplier->status === 'active' ? 'success' : ($supplier->status === 'blacklisted' ? 'danger' : 'warning') }}">
                                    {{ $supplier->status }}
                                </span>
                            </td>
                            <td style="font-size:0.82rem;color:#9ca3af;">{{ $supplier->created_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="text-align:center;padding:40px;color:#9ca3af;">
                            <i data-lucide="truck" style="width:32px;height:32px;display:block;margin:0 auto 10px;"></i>
                            No suppliers yet.
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($suppliers->hasPages())<div class="portal-card__footer">{{ $suppliers->links() }}</div>@endif
    </div>
</div>

{{-- Create Supplier Modal --}}
<div id="createModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)closeModal('createModal')">
    <div class="modal-box" style="max-width:500px;width:95%;">
        <div class="modal-header">
            <h3 class="modal-title"><i data-lucide="truck" style="width:18px;height:18px;"></i> Add Supplier</h3>
            <button class="modal-close" onclick="closeModal('createModal')">&times;</button>
        </div>
        <form method="POST" action="{{ route('portals.staff.supply.suppliers.store') }}">
            @csrf
            <div class="modal-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label">Supplier Name <span style="color:red">*</span></label>
                        <input type="text" name="name" class="form-control" required maxlength="150" value="{{ old('name') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Code</label>
                        <input type="text" name="code" class="form-control" maxlength="50" value="{{ old('code') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Person</label>
                        <input type="text" name="contact_person" class="form-control" maxlength="100" value="{{ old('contact_person') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" maxlength="30" value="{{ old('phone') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" maxlength="100" value="{{ old('email') }}">
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--outline" onclick="closeModal('createModal')">Cancel</button>
                <button type="submit" class="btn btn--primary">Add Supplier</button>
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
