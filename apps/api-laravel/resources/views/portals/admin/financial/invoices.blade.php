@extends('layouts.admin')
@section('title', 'Invoices')
@section('content')
<div class="admin-page">
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

  <div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Invoices</h1>
    <a href="{{ route('portals.admin.financial.index') }}" class="btn btn-outline-secondary btn-sm">
      <i data-lucide="arrow-left"></i> Back to Overview
    </a>
  </div>

  <!-- Filters -->
  <form method="GET" action="{{ route('portals.admin.financial.invoices') }}" class="mb-4">
    <div class="card">
      <div class="card-body py-3">
        <div class="row g-3 align-items-end">
          <div class="col-md-2">
            <label class="form-label fw-semibold">Status</label>
            <select name="status" class="form-control">
              <option value="">All Statuses</option>
              <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
              <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
              <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
              <option value="voided" {{ request('status') === 'voided' ? 'selected' : '' }}>Voided</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label fw-semibold">Facility</label>
            <select name="facility_id" class="form-control">
              <option value="">All Facilities</option>
              @foreach($facilities ?? [] as $facility)
              <option value="{{ $facility->id }}" {{ request('facility_id') == $facility->id ? 'selected' : '' }}>{{ $facility->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label fw-semibold">From Date</label>
            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
          </div>
          <div class="col-md-2">
            <label class="form-label fw-semibold">To Date</label>
            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
          </div>
          <div class="col-md-2">
            <label class="form-label fw-semibold">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Invoice # or patient" value="{{ request('search') }}">
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
          </div>
        </div>
      </div>
    </div>
  </form>

  <!-- Table -->
  <div class="card shadow-sm">
    <div class="card-body p-0">
      <table class="table table-striped table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th>Invoice #</th>
            <th>Patient</th>
            <th>Facility</th>
            <th class="text-end">Amount</th>
            <th>Status</th>
            <th>Due Date</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($invoices ?? [] as $invoice)
          <tr>
            <td><span class="font-monospace">{{ $invoice->invoice_number }}</span></td>
            <td>{{ $invoice->patient_name }}</td>
            <td>{{ $invoice->facility_name }}</td>
            <td class="text-end">${{ number_format($invoice->amount, 2) }}</td>
            <td>
              @php
                $statusClass = match($invoice->status) {
                  'paid' => 'bg-success',
                  'pending' => 'bg-warning text-dark',
                  'overdue' => 'bg-danger',
                  'voided' => 'bg-secondary',
                  default => 'bg-light text-dark'
                };
              @endphp
              <span class="badge {{ $statusClass }}">{{ ucfirst($invoice->status) }}</span>
            </td>
            <td>
              @if($invoice->due_date)
                <span class="{{ $invoice->status === 'overdue' ? 'text-danger fw-semibold' : '' }}">
                  {{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }}
                </span>
              @else
                <span class="text-muted">—</span>
              @endif
            </td>
            <td class="text-end">
              <div class="d-flex gap-1 justify-content-end">
                <a href="#" class="btn btn-sm btn-outline-secondary" title="View">
                  <i data-lucide="eye"></i>
                </a>
                @if(!in_array($invoice->status, ['paid', 'voided']))
                <button type="button" class="btn btn-sm btn-outline-success"
                  data-bs-toggle="modal" data-bs-target="#markPaidModal"
                  data-invoice-id="{{ $invoice->id }}"
                  data-invoice-number="{{ $invoice->invoice_number }}"
                  data-invoice-amount="{{ $invoice->amount }}"
                  title="Mark Paid">
                  <i data-lucide="check-circle"></i>
                </button>
                <form method="POST" action="{{ route('portals.admin.financial.void-invoice', $invoice->id) }}" class="d-inline" onsubmit="return confirm('Void invoice {{ $invoice->invoice_number }}?')">
                  @csrf
                  @method('PUT')
                  <button type="submit" class="btn btn-sm btn-outline-danger" title="Void">
                    <i data-lucide="x-circle"></i>
                  </button>
                </form>
                @endif
              </div>
            </td>
          </tr>
          @empty
          <tr><td colspan="7" class="text-center text-muted py-4">No invoices found.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if(isset($invoices) && $invoices->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
      <span class="text-muted small">Showing {{ $invoices->firstItem() }}–{{ $invoices->lastItem() }} of {{ $invoices->total() }}</span>
      {{ $invoices->withQueryString()->links() }}
    </div>
    @endif
  </div>
</div>

<!-- Mark Paid Modal -->
<div class="modal fade" id="markPaidModal" tabindex="-1" aria-labelledby="markPaidModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="markPaidModalLabel">Mark Invoice as Paid</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="{{ route('portals.admin.financial.mark-paid') }}" id="markPaidForm">
        @csrf
        <input type="hidden" name="invoice_id" id="modal_invoice_id">
        <div class="modal-body">
          <p class="text-muted mb-3">Invoice: <strong id="modal_invoice_number"></strong></p>
          <div class="mb-3">
            <label class="form-label fw-semibold">Amount Received</label>
            <div class="input-group">
              <span class="input-group-text">$</span>
              <input type="number" name="amount" id="modal_amount" class="form-control" step="0.01" min="0" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Payment Method</label>
            <select name="method" class="form-control" required>
              <option value="">Select method...</option>
              <option value="cash">Cash</option>
              <option value="check">Check</option>
              <option value="bank_transfer">Bank Transfer</option>
              <option value="credit_card">Credit Card</option>
              <option value="insurance">Insurance</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Payment Date</label>
            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
          </div>
          <div class="mb-1">
            <label class="form-label fw-semibold">Reference / Note <span class="text-muted fw-normal">(optional)</span></label>
            <input type="text" name="reference" class="form-control" placeholder="Check #, transaction ID, etc.">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Mark as Paid</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.getElementById('markPaidModal').addEventListener('show.bs.modal', function(event) {
  const btn = event.relatedTarget;
  document.getElementById('modal_invoice_id').value = btn.dataset.invoiceId;
  document.getElementById('modal_invoice_number').textContent = btn.dataset.invoiceNumber;
  document.getElementById('modal_amount').value = parseFloat(btn.dataset.invoiceAmount).toFixed(2);
});
</script>
@endpush
@endsection
