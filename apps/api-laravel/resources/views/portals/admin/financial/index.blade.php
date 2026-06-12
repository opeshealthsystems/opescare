@extends('layouts.admin')
@section('title', 'Financial Overview')
@section('content')
<div class="admin-page">
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

  <div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Financial Overview</h1>
    <div class="d-flex gap-2">
      <a href="{{ route('portals.admin.financial.invoices') }}" class="btn btn-outline-primary btn-sm">
        <i data-lucide="file-text"></i> Invoices
      </a>
      <a href="{{ route('portals.admin.financial.payments') }}" class="btn btn-outline-secondary btn-sm">
        <i data-lucide="credit-card"></i> Payments
      </a>
    </div>
  </div>

  <!-- Date Range Filter -->
  <form method="GET" action="{{ route('portals.admin.financial.index') }}" class="mb-4">
    <div class="card">
      <div class="card-body py-3">
        <div class="row g-3 align-items-end">
          <div class="col-md-4">
            <label class="form-label fw-semibold">From Date</label>
            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">To Date</label>
            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
          </div>
          <div class="col-md-4">
            <button type="submit" class="btn btn-primary">Apply Filter</button>
            <a href="{{ route('portals.admin.financial.index') }}" class="btn btn-outline-secondary ms-2">Reset</a>
          </div>
        </div>
      </div>
    </div>
  </form>

  <!-- Stats Row -->
  <div class="row g-4 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold text-uppercase">Total Invoiced</div>
              <div class="h4 mb-0 mt-1">${{ number_format($stats['total_invoiced'] ?? 0, 2) }}</div>
            </div>
            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
              <i data-lucide="file-text" class="text-primary"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold text-uppercase">Total Collected</div>
              <div class="h4 mb-0 mt-1">${{ number_format($stats['total_collected'] ?? 0, 2) }}</div>
            </div>
            <div class="bg-success bg-opacity-10 rounded-circle p-3">
              <i data-lucide="check-circle" class="text-success"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold text-uppercase">Outstanding</div>
              <div class="h4 mb-0 mt-1">${{ number_format($stats['outstanding'] ?? 0, 2) }}</div>
            </div>
            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
              <i data-lucide="clock" class="text-warning"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold text-uppercase">Overdue</div>
              <div class="h4 mb-0 mt-1">{{ $stats['overdue_count'] ?? 0 }}</div>
            </div>
            <div class="bg-danger bg-opacity-10 rounded-circle p-3">
              <i data-lucide="alert-triangle" class="text-danger"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Revenue by Facility -->
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">
      <i data-lucide="building-2" class="me-1"></i> Revenue by Facility
    </div>
    <div class="card-body p-0">
      <table class="table table-striped table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th>Facility</th>
            <th class="text-end">Invoiced</th>
            <th class="text-end">Collected</th>
            <th class="text-end">Outstanding</th>
            <th class="text-end">Collection Rate</th>
          </tr>
        </thead>
        <tbody>
          @forelse($revenueByFacility ?? [] as $row)
          <tr>
            <td>{{ $row->facility_name }}</td>
            <td class="text-end">${{ number_format($row->total_invoiced, 2) }}</td>
            <td class="text-end">${{ number_format($row->total_collected, 2) }}</td>
            <td class="text-end">${{ number_format($row->outstanding, 2) }}</td>
            <td class="text-end">
              @php $rate = $row->total_invoiced > 0 ? ($row->total_collected / $row->total_invoiced) * 100 : 0; @endphp
              <span class="badge {{ $rate >= 80 ? 'bg-success' : ($rate >= 50 ? 'bg-warning text-dark' : 'bg-danger') }}">
                {{ number_format($rate, 1) }}%
              </span>
            </td>
          </tr>
          @empty
          <tr><td colspan="5" class="text-center text-muted py-3">No data for selected period.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- Recent Invoices -->
  <div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <span class="fw-semibold"><i data-lucide="list" class="me-1"></i> Recent Invoices</span>
      <a href="{{ route('portals.admin.financial.invoices') }}" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
      <table class="table table-striped table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th>Invoice #</th>
            <th>Patient</th>
            <th>Facility</th>
            <th class="text-end">Amount</th>
            <th>Status</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          @forelse($recentInvoices ?? [] as $invoice)
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
            <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('M d, Y') }}</td>
          </tr>
          @empty
          <tr><td colspan="6" class="text-center text-muted py-3">No recent invoices.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
