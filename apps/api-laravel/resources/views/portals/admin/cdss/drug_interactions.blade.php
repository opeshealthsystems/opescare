@extends('layouts.admin')
@section('title', 'Drug Interaction Rules')
@section('content')
<div class="admin-page">
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

  <div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center gap-3">
      <a href="{{ route('portals.admin.cdss.index') }}" class="btn btn-sm btn-outline-secondary">
        <i data-lucide="arrow-left" style="width:14px;height:14px;"></i>
      </a>
      <h1 class="h3 mb-0">Drug Interaction Rules</h1>
    </div>
    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addRuleModal">
      <i data-lucide="plus" class="me-1" style="width:16px;height:16px;"></i> Add Rule
    </button>
  </div>

  <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
    <i data-lucide="alert-triangle" class="me-2 flex-shrink-0" style="width:18px;height:18px;"></i>
    <div class="small">These rules affect clinical decision support. Review carefully before adding.</div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Drug A</th>
              <th>Drug B</th>
              <th>Severity</th>
              <th>Description</th>
              <th>Action Required</th>
              <th>Created</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($rules as $rule)
            <tr>
              <td class="fw-medium">{{ $rule->drug_a }}</td>
              <td class="fw-medium">{{ $rule->drug_b }}</td>
              <td>
                @php
                  $badge = match($rule->severity) {
                    'mild'            => 'info',
                    'moderate'        => 'warning',
                    'severe'          => 'danger',
                    'contraindicated' => 'dark',
                    default           => 'secondary',
                  };
                @endphp
                <span class="badge bg-{{ $badge }}">{{ ucfirst($rule->severity) }}</span>
              </td>
              <td class="text-muted small" style="max-width:260px;">{{ $rule->description }}</td>
              <td class="text-muted small" style="max-width:200px;">{{ $rule->action_required }}</td>
              <td class="text-muted small text-nowrap">{{ $rule->created_at->format('d M Y') }}</td>
              <td class="text-end">
                <form action="{{ route('portals.admin.cdss.destroy-drug', $rule->id) }}" method="POST"
                      onsubmit="return confirm('Delete this drug interaction rule?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                  </button>
                </form>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center text-muted py-5">
                <i data-lucide="inbox" style="width:32px;height:32px;" class="mb-2 d-block mx-auto"></i>
                No drug interaction rules found.
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    @if(isset($rules) && $rules->hasPages())
    <div class="card-footer bg-transparent d-flex justify-content-end">
      {{ $rules->links() }}
    </div>
    @endif
  </div>
</div>

<!-- Add Rule Modal -->
<div class="modal fade" id="addRuleModal" tabindex="-1" aria-labelledby="addRuleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="{{ route('portals.admin.cdss.store-drug') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="addRuleModalLabel">
            <i data-lucide="zap" class="me-2" style="width:18px;height:18px;"></i> Add Drug Interaction Rule
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-medium">Drug A <span class="text-danger">*</span></label>
              <input type="text" name="drug_a" class="form-control" placeholder="e.g. Warfarin" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-medium">Drug B <span class="text-danger">*</span></label>
              <input type="text" name="drug_b" class="form-control" placeholder="e.g. Aspirin" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-medium">Severity <span class="text-danger">*</span></label>
              <select name="severity" class="form-select" required>
                <option value="">Select severity...</option>
                <option value="mild">Mild</option>
                <option value="moderate">Moderate</option>
                <option value="severe">Severe</option>
                <option value="contraindicated">Contraindicated</option>
              </select>
            </div>
            <div class="col-md-8">
              <label class="form-label fw-medium">Action Required</label>
              <input type="text" name="action_required" class="form-control" placeholder="e.g. Monitor INR closely">
            </div>
            <div class="col-12">
              <label class="form-label fw-medium">Description</label>
              <textarea name="description" class="form-control" rows="3" placeholder="Describe the interaction and its clinical significance..."></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i data-lucide="plus" class="me-1" style="width:14px;height:14px;"></i> Add Rule
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
