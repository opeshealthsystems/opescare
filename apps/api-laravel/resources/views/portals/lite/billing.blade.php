@extends('layouts.lite')
@section('title', 'Billing')

@section('content')

<h1 class="lite-page-title">Quick billing</h1>
<p class="lite-page-sub">Issue a basic receipt for services rendered</p>

@if($patient)
<div class="lite-card lite-mb">
    <div class="lite-card__body lite-patient-chip">
        <div class="lite-patient-chip__avatar"><i data-lucide="user"></i></div>
        <div>
            <div class="lite-td-strong">{{ $patient->first_name }} {{ $patient->last_name }}</div>
            <div class="lite-mono--accent">{{ $patient->health_id }}</div>
        </div>
        <a href="{{ route('portals.lite.lookup') }}" class="lite-muted-link lite-ml-auto">Change</a>
    </div>
</div>
@else
<div class="lite-alert lite-alert--info">
    <i data-lucide="info"></i>
    <span>No patient selected. <a href="{{ route('portals.lite.lookup') }}" class="lite-alert__link">Select patient →</a></span>
</div>
@endif

<form id="billingForm" method="POST" action="{{ route('portals.staff.billing.create') }}">
    @csrf
    @if($patient)
        <input type="hidden" name="patient_id" value="{{ $patient->id }}">
    @endif

    <div class="lite-card">
        <div class="lite-card__head lite-card__head--row">
            <span>Line items</span>
            <button type="button" onclick="addLineItem()" class="lite-btn lite-btn--outline lite-btn--sm">+ Add item</button>
        </div>
        <div class="lite-card__body lite-card__body--flush">
            <table class="lite-table" id="lineItemsTable">
                <thead><tr><th>Description</th><th>Qty</th><th>Amount (FCFA)</th><th></th></tr></thead>
                <tbody id="lineItems">
                    <tr id="row-0">
                        <td><input type="text" name="items[0][description]" class="lite-input lite-input--cell" placeholder="Service…"></td>
                        <td class="lite-col-narrow"><input type="number" name="items[0][qty]" class="lite-input lite-input--cell" value="1" min="1" onchange="calcTotal()"></td>
                        <td class="lite-col-amt"><input type="number" name="items[0][amount]" class="lite-input lite-input--cell" placeholder="0" step="1" onchange="calcTotal()"></td>
                        <td class="lite-col-x">—</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="lite-card">
        <div class="lite-card__body">
            <div class="lite-row lite-row--between lite-mb">
                <span class="lite-td-strong">Total</span>
                <span class="lite-total">FCFA <span id="totalAmt">0</span></span>
            </div>
            <div class="lite-form-group">
                <label class="lite-label">Payment mode</label>
                <select name="payment_mode" class="lite-input">
                    <option value="cash">Cash</option>
                    <option value="pos">POS / Card</option>
                    <option value="transfer">Bank transfer</option>
                    <option value="wallet">Wallet</option>
                    <option value="nhis">NHIS</option>
                </select>
            </div>
            <div class="lite-form-group">
                <label class="lite-label">Note (optional)</label>
                <input type="text" name="note" class="lite-input" placeholder="e.g. consultation fee…">
            </div>
        </div>
    </div>

    <button type="submit" class="lite-btn lite-btn--success lite-btn--full lite-mt">
        <i data-lucide="receipt"></i> Issue receipt
    </button>
    <div class="lite-empty lite-mt">
        <a href="{{ route('portals.lite.dashboard') }}" class="lite-muted-link">← Cancel</a>
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
        <td><input type="text" name="items[${i}][description]" class="lite-input lite-input--cell" placeholder="Service…"></td>
        <td class="lite-col-narrow"><input type="number" name="items[${i}][qty]" class="lite-input lite-input--cell" value="1" min="1" onchange="calcTotal()"></td>
        <td class="lite-col-amt"><input type="number" name="items[${i}][amount]" class="lite-input lite-input--cell" placeholder="0" step="1" onchange="calcTotal()"></td>
        <td class="lite-col-x">
            <button type="button" onclick="this.closest('tr').remove();calcTotal()" class="lite-btn--icon"><i data-lucide="x"></i></button>
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
