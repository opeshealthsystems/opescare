@extends('layouts.portal')

@section('title', __('public.staff_portal.btn_create_invoice', [], app()->getLocale()) ?: 'Create Invoice')

@section('sidebar_role_badge')
<div class="sidebar-role-badge">{{ __('public.staff_portal.role_clinical_staff', [], app()->getLocale()) ?: 'Clinical Staff' }}</div>
@endsection
@section('sidebar_user_role', __('public.staff_portal.role_clinical_staff', [], app()->getLocale()) ?: 'Clinical Staff')

@section('sidebar_nav')
@endif

<div class="panel">
    <div class="panel-body">
        <form method="POST" action="{{ route('portals.staff.billing.store') }}" id="invoice-form">
            @csrf

            <div class="form-group mb-4">
                <label class="form-label">{{ __('public.stf_billing_patient') }}</label>
                @if(count($patients) > 0)
                    <select name="patient_id" class="form-control" required>
                        <option value="">{{ __('public.stf_select_patient') }}</option>
                        @foreach($patients as $p)
                            <option value="{{ $p->id }}" {{ old('patient_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->health_id ?? $p->id }} ({{ $p->first_name ?? '' }} {{ $p->last_name ?? '' }})
                            </option>
                        @endforeach
                    </select>
                @else
                    <input type="text" name="patient_id" class="form-control" required placeholder="{{ __('public.stf_appt_patient_id') }}">
                @endif
                @error('patient_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            {{-- Line items --}}
            <h3 class="diff-label">{{ __('public.stf_billing_line_items') }}</h3>
            <div id="line-items">
                {{-- Structural line-item grid: no kit class exists for a multi-column repeated input row; layout kept inline (see report) --}}
                <div class="line-item" style="display:grid;grid-template-columns:1fr 70px 120px auto;gap:.5rem;margin-bottom:.5rem;align-items:end;">
                    <div class="form-group">
                        <label class="form-label">{{ __('public.staff_portal.lbl_description', [], app()->getLocale()) ?: 'Description' }} *</label>
                        <input type="text" name="items[0][description]" class="form-control" required placeholder="{{ __('public.stf_billing_service_desc') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('public.staff_portal.lbl_qty', [], app()->getLocale()) ?: 'Qty' }}</label>
                        <input type="number" name="items[0][quantity]" class="form-control" value="1" min="1" step="1" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('public.staff_portal.lbl_unit_price', [], app()->getLocale()) ?: 'Unit Price' }} *</label>
                        <input type="number" name="items[0][unit_price]" class="form-control" value="0.00" min="0" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <button type="button" class="btn btn-ghost btn-xs" disabled hidden>
                            <i data-lucide="trash-2"></i>
                        </button>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-ghost btn-sm mb-6" onclick="addLineItem()">
                <i data-lucide="plus"></i>
                {{ __('public.staff_portal.btn_add_item', [], app()->getLocale()) ?: 'Add Line Item' }}
            </button>

            <div class="row-actions-inline">
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="file-plus"></i>
                    {{ __('public.staff_portal.btn_create_invoice', [], app()->getLocale()) ?: 'Create Invoice' }}
                </button>
                <a href="{{ route('portals.staff.billing') }}" class="btn btn-ghost">{{ __('public.stf_cancel') }}</a>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    var lineCount = 1;
    function addLineItem() {
        var container = document.getElementById('line-items');
        var idx = lineCount++;
        var row = document.createElement('div');
        row.className = 'line-item';
        row.style.cssText = 'display:grid;grid-template-columns:1fr 70px 120px auto;gap:.5rem;margin-bottom:.5rem;align-items:end;';
        row.innerHTML =
            '<div class="form-group"><input type="text" name="items[' + idx + '][description]" class="form-control" required placeholder="{{ __('public.stf_billing_service_desc') }}"></div>' +
            '<div class="form-group"><input type="number" name="items[' + idx + '][quantity]" class="form-control" value="1" min="1" step="1" required></div>' +
            '<div class="form-group"><input type="number" name="items[' + idx + '][unit_price]" class="form-control" value="0.00" min="0" step="0.01" required></div>' +
            '<div class="form-group"><button type="button" class="btn btn-ghost btn-xs" onclick="this.closest(\'.line-item\').remove()">' +
            '<i data-lucide="trash-2"></i></button></div>';
        container.appendChild(row);
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
</script>
@endsection
