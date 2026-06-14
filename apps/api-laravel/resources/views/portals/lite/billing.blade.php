@extends('layouts.lite')
@section('title', 'Billing')

@section('content')

<h1 class="lite-page-title">Quick Billing</h1>
<p class="lite-page-sub">Issue a basic receipt for services rendered</p>

@if($patient)
<div class="lite-card" style="margin-bottom:14px;">
    <div class="lite-card__body" style="display:flex;align-items:center;gap:12px;">
        <div style="width:44px;height:44px;border-radius:50%;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i data-lucide="user" style="color:#16a34a;width:22px;height:22px;"></i>
        </div>
        <div>
            <div style="font-weight:700;font-size:0.95rem;">{{ $patient->first_name }} {{ $patient->last_name }}</div>
            <div style="font-family:monospace;font-size:0.78rem;color:#16a34a;">{{ $patient->health_id }}</div>
        </div>
        <a href="{{ route('portals.lite.lookup') }}" style="margin-left:auto;font-size:0.78rem;color:#64748b;">Change</a>
    </div>
</div>
@else
<div class="lite-alert lite-alert--info" style="margin-bottom:14px;">
    <i data-lucide="info" style="width:16px;height:16px;flex-shrink:0;"></i>
    No patient selected. <a href="{{ route('portals.lite.lookup') }}" style="font-weight:700;color:inherit;">Select patient →</a>
</div>
@endif

<form id="billingForm" method="POST" action="{{ route('portals.staff.billing.create') }}">
    @csrf
    @if($patient)
        <input type="hidden" name="patient_id" value="{{ $patient->id }}">
    @endif

    <div class="lite-card">
        <div class="lite-card__head" style="display:flex;justify-content:space-between;align-items:center;">
            <span>Line Items</span>
            <button type="button" onclick="addLineItem()" class="lite-btn lite-btn--outline"
                    style="padding:4px 10px;font-size:0.78rem;">+ Add Item</button>
        </div>
        <div class="lite-card__body" style="padding:0;">
            <table class="lite-table" id="lineItemsTable">
                <thead><tr><th>Description</th><th>Qty</th><th>Amount (FCFA)</th><th></th></tr></thead>
                <tbody id="lineItems">
                    <tr id="row-0">
                        <td><input type="text" name="items[0][description]" class="lite-input" style="width:100%;min-width:0;" placeholder="Service…"></td>
                        <td style="width:70px;"><input type="number" name="items[0][qty]" class="lite-input" value="1" min="1" onchange="calcTotal()" style="width:100%;min-width:0;"></td>
                        <td style="width:110px;"><input type="number" name="items[0][amount]" class="lite-input" placeholder="0" step="1" onchange="calcTotal()" style="width:100%;min-width:0;"></td>
                        <td style="width:40px;text-align:center;">—</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="lite-card" style="margin-top:0;">
        <div class="lite-card__body">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <span style="font-weight:600;">Total</span>
                <span style="font-size:1.3rem;font-weight:800;color:#16a34a;">FCFA <span id="totalAmt">0</span></span>
            </div>
            <div class="lite-form-group">
                <label class="lite-label">Payment Mode</label>
                <select name="payment_mode" class="lite-input">
                    <option value="cash">Cash</option>
                    <option value="pos">POS / Card</option>
                    <option value="transfer">Bank Transfer</option>
                    <option value="wallet">Wallet</option>
                    <option value="nhis">NHIS</option>
                </select>
            </div>
            <div class="lite-form-group" style="margin-bottom:0;">
                <label class="lite-label">Note (optional)</label>
                <input type="text" name="note" class="lite-input" placeholder="e.g. consultation fee…">
            </div>
        </div>
    </div>

    <button type="submit" class="lite-btn lite-btn--success lite-btn--full" style="margin-top:8px;">
        <i data-lucide="receipt" style="width:16px;height:16px;"></i> Issue Receipt
    </button>
    <div style="text-align:center;margin-top:10px;">
        <a href="{{ route('portals.lite.dashboard') }}" style="font-size:0.83rem;color:#64748b;">← Cancel</a>
    </div>
</form>

@endsection

@section('scripts')
<script>
let rowCount = 1;

function addLineItem() {
    const tbody = document.getElementById('lineItems');
    const i = rowCount++;
    const tr = document.createElement('tr');
    tr.id = 'row-' + i;
    tr.innerHTML = `
        <td><input type="text" name="items[${i}][description]" class="lite-input" style="width:100%;min-width:0;" placeholder="Service…"></td>
        <td style="width:70px;"><input type="number" name="items[${i}][qty]" class="lite-input" value="1" min="1" onchange="calcTotal()" style="width:100%;min-width:0;"></td>
        <td style="width:110px;"><input type="number" name="items[${i}][amount]" class="lite-input" placeholder="0" step="1" onchange="calcTotal()" style="width:100%;min-width:0;"></td>
        <td style="width:40px;text-align:center;">
            <button type="button" onclick="this.closest('tr').remove();calcTotal()"
                    style="background:none;border:none;cursor:pointer;color:#dc2626;font-size:1rem;padding:0;"><i data-lucide="x" style="width:14px;height:14px;"></i></button>
        </td>`;
    tbody.appendChild(tr);
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function calcTotal() {
    let total = 0;
    document.querySelectorAll('#lineItems tr').forEach(tr => {
        const qty = parseFloat(tr.querySelector('input[name*="[qty]"]')?.value || 1);
        const amt = parseFloat(tr.querySelector('input[name*="[amount]"]')?.value || 0);
        if (!isNaN(qty) && !isNaN(amt)) total += qty * amt;
    });
    document.getElementById('totalAmt').textContent = total.toLocaleString('fr-FR');
}
</script>
@endsection
