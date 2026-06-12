@extends('layouts.admin')
@section('title', 'Support Tickets')
@section('content')
<div class="admin-page">

  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

  <div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Support Tickets</h1>
  </div>

  <!-- Stats Row -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="rounded-circle bg-primary bg-opacity-10 p-3">
            <i data-lucide="ticket" class="text-primary" style="width:20px;height:20px;"></i>
          </div>
          <div>
            <div class="fw-bold fs-4">{{ $stats['total'] ?? 0 }}</div>
            <div class="text-muted small">Total</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="rounded-circle bg-danger bg-opacity-10 p-3">
            <i data-lucide="alert-circle" class="text-danger" style="width:20px;height:20px;"></i>
          </div>
          <div>
            <div class="fw-bold fs-4">{{ $stats['open'] ?? 0 }}</div>
            <div class="text-muted small">Open</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="rounded-circle bg-warning bg-opacity-10 p-3">
            <i data-lucide="clock" class="text-warning" style="width:20px;height:20px;"></i>
          </div>
          <div>
            <div class="fw-bold fs-4">{{ $stats['pending'] ?? 0 }}</div>
            <div class="text-muted small">Pending</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="rounded-circle bg-success bg-opacity-10 p-3">
            <i data-lucide="check-circle" class="text-success" style="width:20px;height:20px;"></i>
          </div>
          <div>
            <div class="fw-bold fs-4">{{ $stats['resolved'] ?? 0 }}</div>
            <div class="text-muted small">Resolved</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filters -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <form method="GET" action="{{ route('portals.admin.support.index') }}" class="row g-2 align-items-end">
        <div class="col-12 col-md-3">
          <label class="form-label small fw-semibold mb-1">Status</label>
          <select name="status" class="form-select form-select-sm">
            <option value="">All Statuses</option>
            <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
            <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
          </select>
        </div>
        <div class="col-12 col-md-2">
          <label class="form-label small fw-semibold mb-1">Priority</label>
          <select name="priority" class="form-select form-select-sm">
            <option value="">All Priorities</option>
            <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
            <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
            <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
            <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
          </select>
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label small fw-semibold mb-1">Category</label>
          <select name="category" class="form-select form-select-sm">
            <option value="">All Categories</option>
            @foreach($categories ?? [] as $cat)
              <option value="{{ $cat }}"
                {{ request('category') === $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label small fw-semibold mb-1">Search</label>
          <input type="text" name="search" class="form-control form-control-sm"
            placeholder="Ticket # or subject..." value="{{ request('search') }}">
        </div>
        <div class="col-12 col-md-1 d-flex gap-1">
          <button type="submit" class="btn btn-primary btn-sm w-100">
            <i data-lucide="search" style="width:14px;height:14px;"></i>
          </button>
          <a href="{{ route('portals.admin.support.index') }}" class="btn btn-outline-secondary btn-sm w-100">
            <i data-lucide="x" style="width:14px;height:14px;"></i>
          </a>
        </div>
      </form>
    </div>
  </div>

  <!-- Table -->
  <div class="card border-0 shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-3">Ticket #</th>
              <th>Subject</th>
              <th>Category</th>
              <th>Priority</th>
              <th>Status</th>
              <th>Assignee</th>
              <th>Created</th>
              <th class="text-end pe-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($tickets ?? [] as $ticket)
            <tr>
              <td class="ps-3">
                <span class="fw-semibold text-muted">#{{ $ticket->ticket_number ?? $ticket->id }}</span>
              </td>
              <td>
                <a href="{{ route('portals.admin.support.show', $ticket) }}"
                   class="text-decoration-none fw-semibold">{{ Str::limit($ticket->subject, 50) }}</a>
              </td>
              <td>
                <span class="badge bg-light text-dark border">{{ ucfirst($ticket->category ?? 'General') }}</span>
              </td>
              <td>
                @php
                  $priorityClass = match($ticket->priority ?? 'medium') {
                    'urgent' => 'danger',
                    'high'   => 'warning',
                    'medium' => 'info',
                    'low'    => 'secondary',
                    default  => 'secondary',
                  };
                @endphp
                <span class="badge bg-{{ $priorityClass }}">{{ ucfirst($ticket->priority ?? 'Medium') }}</span>
              </td>
              <td>
                @php
                  $statusClass = match($ticket->status ?? 'open') {
                    'open'     => 'danger',
                    'pending'  => 'warning',
                    'resolved' => 'success',
                    'closed'   => 'secondary',
                    default    => 'secondary',
                  };
                @endphp
                <span class="badge bg-{{ $statusClass }}">{{ ucfirst($ticket->status ?? 'Open') }}</span>
              </td>
              <td>
                @if($ticket->assignee)
                  <span class="d-flex align-items-center gap-1">
                    <i data-lucide="user" style="width:14px;height:14px;"></i>
                    {{ $ticket->assignee->name }}
                  </span>
                @else
                  <span class="text-muted small">Unassigned</span>
                @endif
              </td>
              <td>
                <span class="text-muted small">{{ $ticket->created_at?->format('M d, Y') }}</span>
              </td>
              <td class="text-end pe-3">
                <div class="d-flex justify-content-end gap-1">
                  <a href="{{ route('portals.admin.support.show', $ticket) }}"
                     class="btn btn-sm btn-outline-primary" title="View">
                    <i data-lucide="eye" style="width:14px;height:14px;"></i>
                  </a>
                  <button type="button" class="btn btn-sm btn-outline-secondary"
                    title="Assign"
                    data-bs-toggle="modal" data-bs-target="#assignModal"
                    data-ticket-id="{{ $ticket->id }}"
                    data-ticket-num="#{{ $ticket->ticket_number ?? $ticket->id }}">
                    <i data-lucide="user-check" style="width:14px;height:14px;"></i>
                  </button>
                  @if(!in_array($ticket->status, ['closed', 'resolved']))
                  <form method="POST"
                    action="{{ route('portals.admin.support.close', $ticket) }}"
                    onsubmit="return confirm('Close this ticket?')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Close">
                      <i data-lucide="x-circle" style="width:14px;height:14px;"></i>
                    </button>
                  </form>
                  @endif
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center py-5 text-muted">
                <i data-lucide="inbox" style="width:32px;height:32px;" class="mb-2 d-block mx-auto"></i>
                No tickets found.
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    @if(isset($tickets) && $tickets->hasPages())
    <div class="card-footer bg-transparent d-flex justify-content-end">
      {{ $tickets->withQueryString()->links() }}
    </div>
    @endif
  </div>

</div>

<!-- Assign Modal -->
<div class="modal fade" id="assignModal" tabindex="-1" aria-labelledby="assignModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="assignModalLabel">
          <i data-lucide="user-check" style="width:16px;height:16px;"></i>
          Assign Ticket
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" id="assignForm" action="">
        @csrf
        @method('PATCH')
        <div class="modal-body">
          <p class="text-muted small mb-3">Assigning ticket <strong id="assignTicketNum"></strong></p>
          <label class="form-label fw-semibold">Assignee</label>
          <select name="assignee_id" class="form-select" required>
            <option value="">-- Select Staff Member --</option>
            @foreach($staffUsers ?? [] as $staff)
              <option value="{{ $staff->id }}">{{ $staff->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm">
            <i data-lucide="check" style="width:14px;height:14px;"></i> Assign
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
  const assignModal = document.getElementById('assignModal');
  assignModal.addEventListener('show.bs.modal', function (event) {
    const btn = event.relatedTarget;
    const ticketId = btn.getAttribute('data-ticket-id');
    const ticketNum = btn.getAttribute('data-ticket-num');
    document.getElementById('assignTicketNum').textContent = ticketNum;
    const baseUrl = '{{ url("portals/admin/support") }}';
    document.getElementById('assignForm').action = baseUrl + '/' + ticketId + '/assign';
  });
  lucide.createIcons();
</script>
@endpush
@endsection
