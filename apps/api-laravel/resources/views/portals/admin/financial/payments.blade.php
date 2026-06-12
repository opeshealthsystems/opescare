@extends('layouts.admin')
@section('title', 'Payments')
@section('content')
<div class="admin-page">
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

  <div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Payments</h1>
    <a href="{{ route('portals.admin.financial.index') }}" class="btn btn-outline-secondary btn-sm">
      <i data-lucide="arrow-left"></i> Back to Overview
    </a>
  </div>

  <!-- Filters -->
  <form method="GET" action="{{ route('portals.admin.financial.payments') }}" class="mb-4">
    <div class="card">
      <div class="card-body py-3">
        <div class="row g-3 align-items-end">
          <div class="col-md-2">
            <label class="form-label fw-semibold">Method</label>
            <select name="method" class="form-control">
              <option value="">All Methods</option>
              <option value="cash" {{ request('method') === 'cash' ? 'selected' : '' }}>Cash</option>
              <option value="check" {{ request('method') === 'check' ? 'selected' : '' }}>Check</option>
              <option value="bank_transfer" {{ request('method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
              <option value="credit_card" {{ request('method') === 'credit_card' ? 'selected' : '' }}>Credit Card</option>
              <option value="insurance" {{ request('method') === 'insurance' ? 'selected' : '' }}>Insurance</option>
              <option value="other" {{ request('method') === 'other' ? 'selected' : '' }}>Other</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label fw-semibold">Status</label>
            <select name="status" class="form-control">
              <option value="">All Statuses</option>
              <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
              <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
              <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
              <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
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
            <th>Payment Ref</th>
            <th>Invoice</th>
            <th>Facility</th>
            <th class="text-end">Amount</th>
            <th>Method</th>
            <th>Status</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          @forelse($payments ?? [] as $payment)
          <tr>
            <td><span class="font-monospace">{{ $payment->reference ?? ('PMT-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT)) }}</span></td>
            <td>
              <span class="font-monospace text-primary">{{ $payment->invoice_number }}</span>
            </td>
            <td>{{ $payment->facility_name }}</td>
            <td class="text-end">${{ number_format($payment->amount, 2) }}</td>
            <td>
              @php
                $methodClass = match($payment->method) {
                  'cash' => 'bg-success',
                  'check' => 'bg-info text-dark',
                  'bank_transfer' => 'bg-primary',
                  'credit_card' => 'bg-purple',
                  'insurance' => 'bg-teal',
                  default => 'bg-secondary'
                };
                $methodLabel = match($payment->method) {
                  'cash' => 'Cash',
                  'check' => 'Check',
                  'bank_transfer' => 'Bank Transfer',
                  'credit_card' => 'Credit Card',
                  'insurance' => 'Insurance',
                  default => ucfirst($payment->method ?? 'Other')
                };
              @endphp
              <span class="badge {{ $methodClass }}">{{ $methodLabel }}</span>
            </td>
            <td>
              @php
                $statusClass = match($payment->status) {
                  'completed' => 'bg-success',
                  'pending' => 'bg-warning text-dark',
                  'failed' => 'bg-danger',
                  'refunded' => 'bg-secondary',
                  default => 'bg-light text-dark'
                };
              @endphp
              <span class="badge {{ $statusClass }}">{{ ucfirst($payment->status ?? 'unknown') }}</span>
            </td>
            <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}</td>
          </tr>
          @empty
          <tr><td colspan="7" class="text-center text-muted py-4">No payments found.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if(isset($payments) && $payments->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
      <span class="text-muted small">Showing {{ $payments->firstItem() }}–{{ $payments->lastItem() }} of {{ $payments->total() }}</span>
      {{ $payments->withQueryString()->links() }}
    </div>
    @endif
  </div>
</div>
@endsection
