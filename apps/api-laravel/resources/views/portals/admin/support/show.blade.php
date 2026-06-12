@extends('layouts.admin')
@section('title', 'Ticket #' . ($ticket->ticket_number ?? $ticket->id))
@section('content')
<div class="admin-page">

  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">
        <a href="{{ route('portals.admin.support.index') }}">Support Tickets</a>
      </li>
      <li class="breadcrumb-item active">#{{ $ticket->ticket_number ?? $ticket->id }}</li>
    </ol>
  </nav>

  <!-- Ticket Header -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="text-muted fw-semibold">#{{ $ticket->ticket_number ?? $ticket->id }}</span>
            @php
              $statusClass = match($ticket->status ?? 'open') {
                'open'     => 'danger',
                'pending'  => 'warning',
                'resolved' => 'success',
                'closed'   => 'secondary',
                default    => 'secondary',
              };
              $priorityClass = match($ticket->priority ?? 'medium') {
                'urgent' => 'danger',
                'high'   => 'warning',
                'medium' => 'info',
                'low'    => 'secondary',
                default  => 'secondary',
              };
            @endphp
            <span class="badge bg-{{ $statusClass }}">{{ ucfirst($ticket->status ?? 'Open') }}</span>
            <span class="badge bg-{{ $priorityClass }}">{{ ucfirst($ticket->priority ?? 'Medium') }} Priority</span>
          </div>
          <h2 class="h4 mb-1">{{ $ticket->subject }}</h2>
          <div class="d-flex flex-wrap gap-3 text-muted small mt-2">
            <span><i data-lucide="tag" style="width:13px;height:13px;"></i> {{ ucfirst($ticket->category ?? 'General') }}</span>
            <span><i data-lucide="user" style="width:13px;height:13px;"></i>
              {{ $ticket->submittedBy->name ?? $ticket->user->name ?? 'Unknown' }}
            </span>
            <span><i data-lucide="calendar" style="width:13px;height:13px;"></i>
              {{ $ticket->created_at?->format('M d, Y H:i') }}
            </span>
            @if($ticket->assignee)
            <span><i data-lucide="user-check" style="width:13px;height:13px;"></i>
              Assigned to {{ $ticket->assignee->name }}
            </span>
            @endif
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex flex-wrap gap-2 flex-shrink-0">
          <button type="button" class="btn btn-outline-secondary btn-sm"
            data-bs-toggle="modal" data-bs-target="#assignModal">
            <i data-lucide="user-check" style="width:14px;height:14px;"></i> Assign
          </button>

          @if(!in_array($ticket->status, ['closed', 'resolved']))
          <form method="POST" action="{{ route('portals.admin.support.close', $ticket) }}">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-outline-warning btn-sm"
              onclick="return confirm('Close this ticket?')">
              <i data-lucide="x-circle" style="width:14px;height:14px;"></i> Close
            </button>
          </form>
          @endif

          @if(in_array($ticket->status, ['closed', 'resolved']))
          <form method="POST" action="{{ route('portals.admin.support.reopen', $ticket) }}">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-outline-success btn-sm"
              onclick="return confirm('Reopen this ticket?')">
              <i data-lucide="refresh-cw" style="width:14px;height:14px;"></i> Reopen
            </button>
          </form>
          @endif

          <form method="POST" action="{{ route('portals.admin.support.destroy', $ticket) }}"
            onsubmit="return confirm('Permanently delete this ticket? This cannot be undone.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm">
              <i data-lucide="trash-2" style="width:14px;height:14px;"></i> Delete
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <!-- Ticket Body -->
    <div class="col-12 col-lg-8">
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent fw-semibold d-flex align-items-center gap-2">
          <i data-lucide="message-square" style="width:16px;height:16px;"></i>
          Description
        </div>
        <div class="card-body">
          <div class="ticket-body" style="white-space: pre-wrap; line-height: 1.7;">
            {{ $ticket->description ?? $ticket->body ?? 'No description provided.' }}
          </div>
          @if($ticket->attachments?->count())
          <div class="mt-3 border-top pt-3">
            <div class="text-muted small fw-semibold mb-2">Attachments</div>
            <div class="d-flex flex-wrap gap-2">
              @foreach($ticket->attachments as $attachment)
              <a href="{{ Storage::url($attachment->path) }}" target="_blank"
                 class="btn btn-sm btn-outline-secondary">
                <i data-lucide="paperclip" style="width:13px;height:13px;"></i>
                {{ $attachment->filename ?? 'File' }}
              </a>
              @endforeach
            </div>
          </div>
          @endif
        </div>
      </div>

      <!-- Replies -->
      @if(isset($ticket->replies) && $ticket->replies->count())
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent fw-semibold d-flex align-items-center gap-2">
          <i data-lucide="messages-square" style="width:16px;height:16px;"></i>
          Replies
          <span class="badge bg-secondary ms-1">{{ $ticket->replies->count() }}</span>
        </div>
        <div class="card-body p-0">
          @foreach($ticket->replies as $reply)
          <div class="p-3 {{ !$loop->last ? 'border-bottom' : '' }}">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                  style="width:32px;height:32px;">
                  <i data-lucide="user" class="text-primary" style="width:15px;height:15px;"></i>
                </div>
                <div>
                  <span class="fw-semibold small">{{ $reply->author->name ?? 'Staff' }}</span>
                  @if($reply->author?->is_staff || $reply->is_staff_reply)
                  <span class="badge bg-primary bg-opacity-10 text-primary ms-1" style="font-size:.65rem;">Staff</span>
                  @endif
                </div>
              </div>
              <span class="text-muted small">{{ $reply->created_at?->format('M d, Y H:i') }}</span>
            </div>
            <div class="ps-5" style="white-space: pre-wrap; line-height: 1.6;">
              {{ $reply->body ?? $reply->message }}
            </div>
          </div>
          @endforeach
        </div>
      </div>
      @else
      <div class="card border-0 shadow-sm">
        <div class="card-body text-center text-muted py-4">
          <i data-lucide="message-circle" style="width:28px;height:28px;" class="mb-2 d-block mx-auto"></i>
          No replies yet.
        </div>
      </div>
      @endif
    </div>

    <!-- Sidebar -->
    <div class="col-12 col-lg-4">
      <!-- Ticket Details -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent fw-semibold d-flex align-items-center gap-2">
          <i data-lucide="info" style="width:16px;height:16px;"></i>
          Ticket Details
        </div>
        <div class="card-body">
          <dl class="row mb-0" style="font-size:.875rem;">
            <dt class="col-5 text-muted">Status</dt>
            <dd class="col-7">
              <span class="badge bg-{{ $statusClass }}">{{ ucfirst($ticket->status ?? 'Open') }}</span>
            </dd>

            <dt class="col-5 text-muted">Priority</dt>
            <dd class="col-7">
              <span class="badge bg-{{ $priorityClass }}">{{ ucfirst($ticket->priority ?? 'Medium') }}</span>
            </dd>

            <dt class="col-5 text-muted">Category</dt>
            <dd class="col-7">{{ ucfirst($ticket->category ?? 'General') }}</dd>

            <dt class="col-5 text-muted">Submitted By</dt>
            <dd class="col-7">{{ $ticket->submittedBy->name ?? $ticket->user->name ?? 'Unknown' }}</dd>

            <dt class="col-5 text-muted">Assignee</dt>
            <dd class="col-7">{{ $ticket->assignee->name ?? 'Unassigned' }}</dd>

            <dt class="col-5 text-muted">Created</dt>
            <dd class="col-7">{{ $ticket->created_at?->format('M d, Y') }}</dd>

            @if($ticket->updated_at && $ticket->updated_at != $ticket->created_at)
            <dt class="col-5 text-muted">Updated</dt>
            <dd class="col-7">{{ $ticket->updated_at?->format('M d, Y') }}</dd>
            @endif

            @if($ticket->resolved_at)
            <dt class="col-5 text-muted">Resolved</dt>
            <dd class="col-7">{{ $ticket->resolved_at?->format('M d, Y') }}</dd>
            @endif
          </dl>
        </div>
      </div>

      <!-- Quick Assign -->
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent fw-semibold d-flex align-items-center gap-2">
          <i data-lucide="user-check" style="width:16px;height:16px;"></i>
          Quick Assign
        </div>
        <div class="card-body">
          <form method="POST" action="{{ route('portals.admin.support.assign', $ticket) }}">
            @csrf
            @method('PATCH')
            <div class="mb-3">
              <label class="form-label small fw-semibold">Assign To</label>
              <select name="assignee_id" class="form-select form-select-sm">
                <option value="">-- Unassigned --</option>
                @foreach($staffUsers ?? [] as $staff)
                  <option value="{{ $staff->id }}"
                    {{ ($ticket->assignee_id ?? null) == $staff->id ? 'selected' : '' }}>
                    {{ $staff->name }}
                  </option>
                @endforeach
              </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm w-100">
              <i data-lucide="check" style="width:14px;height:14px;"></i> Update Assignee
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- Assign Modal (from index, reused here for toolbar button) -->
<div class="modal fade" id="assignModal" tabindex="-1" aria-labelledby="assignModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="assignModalLabel">
          <i data-lucide="user-check" style="width:16px;height:16px;"></i> Assign Ticket
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('portals.admin.support.assign', $ticket) }}">
        @csrf
        @method('PATCH')
        <div class="modal-body">
          <p class="text-muted small mb-3">
            Assigning ticket <strong>#{{ $ticket->ticket_number ?? $ticket->id }}</strong>
          </p>
          <label class="form-label fw-semibold">Assignee</label>
          <select name="assignee_id" class="form-select" required>
            <option value="">-- Select Staff Member --</option>
            @foreach($staffUsers ?? [] as $staff)
              <option value="{{ $staff->id }}"
                {{ ($ticket->assignee_id ?? null) == $staff->id ? 'selected' : '' }}>
                {{ $staff->name }}
              </option>
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
  lucide.createIcons();
</script>
@endpush
@endsection
