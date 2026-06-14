@extends('layouts.portal')
@section('title', 'All Invoices')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Invoices')
@section('content')

<div class="breadcrumb">
    <a href="{{ route('portals.admin.financial.index') }}">Financial</a>
    <i data-lucide="chevron-right"></i>
    <span>Invoices</span>
</div>

<div class="page-head">
    <h2>All invoices</h2>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.admin.financial.payments') }}" class="btn btn-secondary btn-sm"><i data-lucide="receipt"></i> Payments</a>
</div>

<p class="td-muted mb-6">Full invoice ledger across all facilities.</p>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

<form method="GET" action="{{ route('portals.admin.financial.invoices') }}" class="filter-bar">
    <label class="filter-search">
        <i data-lucide="search"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Invoice number..." aria-label="Search">
    </label>
    <select name="status" class="filter-select" aria-label="Status">
        <option value="">All statuses</option>
        @foreach(['draft','unpaid','paid','partial','cancelled','overdue'] as $s)
        <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
    <select name="facility_id" class="filter-select" aria-label="Facility">
        <option value="">All facilities</option>
        @foreach($facilities as $f)<option value="{{ $f->id }}" {{ request('facility_id')==$f->id?'selected':'' }}>{{ $f->name }}</option>@endforeach
    </select>
    <label class="filter-search">
        <i data-lucide="calendar"></i>
        <input type="date" name="from_date" value="{{ request('from_date') }}" aria-label="From date">
    </label>
    <label class="filter-search">
        <i data-lucide="calendar"></i>
        <input type="date" name="to_date" value="{{ request('to_date') }}" aria-label="To date">
    </label>
    <button type="submit" class="btn btn-primary btn-sm"><i data-lucide="filter"></i> Filter</button>
    <a href="{{ route('portals.admin.financial.invoices') }}" class="btn btn-ghost btn-sm">Reset</a>
</form>

<div class="panel">
    <div class="panel-header"><h3 class="panel-title"><i data-lucide="file-text"></i> {{ $invoices->total() }} invoices</h3></div>
    <div class="table-wrapper"><table class="data-table"><thead><tr>
        <th>Invoice #</th><th>Patient</th><th>Facility</th><th>Subtotal</th><th>Paid</th><th>Balance</th><th>Status</th><th>Issued</th><th class="row-actions">Actions</th>
    </tr></thead><tbody>
    @forelse($invoices as $inv)
    <tr>
        <td data-label="Invoice #"><span class="mono">{{ $inv->invoice_number }}</span></td>
        <td data-label="Patient">{{ $inv->patient?->first_name.' '.$inv->patient?->last_name ?? '—' }}<br><span class="td-muted">{{ $inv->patient?->health_id }}</span></td>
        <td data-label="Facility">{{ $inv->facility?->name ?? '—' }}</td>
        <td data-label="Subtotal">{{ number_format($inv->subtotal_amount,0,'.',',') }}</td>
        <td data-label="Paid">{{ number_format($inv->paid_amount ?? 0,0,'.',',') }}</td>
        <td data-label="Balance">{{ number_format($inv->balance_amount ?? 0,0,'.',',') }}</td>
        <td data-label="Status">
            @if($inv->status==='paid')<span class="badge badge-success">Paid</span>
            @elseif($inv->status==='partial')<span class="badge badge-warning">Partial</span>
            @elseif($inv->status==='cancelled')<span class="badge badge-neutral">Voided</span>
            @elseif($inv->status==='overdue')<span class="badge badge-danger">Overdue</span>
            @else<span class="badge badge-warning">{{ ucfirst($inv->status) }}</span>@endif
        </td>
        <td data-label="Issued">{{ $inv->issued_at?->format('d M Y') ?? '—' }}</td>
        <td class="row-actions" data-label="Actions">
            <div class="row-actions-inline">
                @if(in_array($inv->status,['draft','unpaid']))
                <button type="button" class="btn btn-warning btn-sm" onclick="opOpenModal('void-modal-{{ $inv->id }}')">Void</button>
                @endif
                @if(!in_array($inv->status,['paid','cancelled']))
                <button type="button" class="btn btn-success btn-sm" onclick="opOpenModal('pay-modal-{{ $inv->id }}')">Mark paid</button>
                @endif
            </div>
        </td>
    </tr>
    @empty<tr><td colspan="9" class="td-muted empty-cell">No invoices found.</td></tr>@endforelse
    </tbody></table></div>
    <div class="panel-body">{{ $invoices->links() }}</div>
</div>

{{-- Modals (rendered outside the table) --}}
@foreach($invoices as $inv)
    @if(in_array($inv->status,['draft','unpaid']))
    <div id="void-modal-{{ $inv->id }}" class="modal-backdrop mt-6" hidden>
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="void-modal-title-{{ $inv->id }}">
            <h3 class="modal__title" id="void-modal-title-{{ $inv->id }}"><i data-lucide="alert-triangle"></i> Void invoice</h3>
            <form method="POST" action="{{ route('portals.admin.financial.void-invoice',$inv->id) }}">@csrf
                <div class="modal__body">
                    <p>Void invoice <strong>{{ $inv->invoice_number }}</strong>? This cannot be undone.</p>
                </div>
                <div class="modal__footer">
                    <button type="button" class="btn btn-ghost" onclick="opCloseModal('void-modal-{{ $inv->id }}')">Cancel</button>
                    <button type="submit" class="btn btn-warning">Void</button>
                </div>
            </form>
        </div>
    </div>
    @endif
    @if(!in_array($inv->status,['paid','cancelled']))
    <div id="pay-modal-{{ $inv->id }}" class="modal-backdrop mt-6" hidden>
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="pay-modal-title-{{ $inv->id }}">
            <h3 class="modal__title" id="pay-modal-title-{{ $inv->id }}"><i data-lucide="check-circle"></i> Mark invoice {{ $inv->invoice_number }} paid</h3>
            <form method="POST" action="{{ route('portals.admin.financial.mark-paid',$inv->id) }}">@csrf
                <div class="modal__body">
                    <div class="form-group">
                        <label class="form-label">Amount (XAF)</label>
                        <input type="number" name="amount" value="{{ $inv->balance_amount ?? $inv->subtotal_amount }}" class="form-control" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Gateway</label>
                        <select name="method" class="form-control">@foreach(['cash'=>'Cash','mtn_momo'=>'MTN MoMo','orange_money'=>'Orange Money','card'=>'Card','insurance'=>'Insurance','bank_transfer'=>'Bank Transfer'] as $k=>$l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Payer phone</label>
                        <input type="text" name="payer_phone" class="form-control" placeholder="+237...">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Payer name</label>
                        <input type="text" name="payer_name" class="form-control">
                    </div>
                </div>
                <div class="modal__footer">
                    <button type="button" class="btn btn-ghost" onclick="opCloseModal('pay-modal-{{ $inv->id }}')">Cancel</button>
                    <button type="submit" class="btn btn-success">Record payment</button>
                </div>
            </form>
        </div>
    </div>
    @endif
@endforeach

@endsection
@section('scripts')
<script>
function opOpenModal(id){ document.getElementById(id).removeAttribute('hidden'); }
function opCloseModal(id){ document.getElementById(id).setAttribute('hidden',''); }
document.addEventListener('keydown', function(e){
    if(e.key==='Escape'){ document.querySelectorAll('.modal-backdrop').forEach(function(m){ m.setAttribute('hidden',''); }); }
});
</script>
@endsection
