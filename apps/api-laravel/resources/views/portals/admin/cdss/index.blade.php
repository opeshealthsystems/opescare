@extends('layouts.admin')
@section('title', 'CDSS Rules')
@section('content')
<div class="admin-page">
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

  <div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Clinical Decision Support Rules</h1>
  </div>

  <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
    <i data-lucide="alert-triangle" class="me-2 flex-shrink-0" style="width:20px;height:20px;"></i>
    <div><strong>Caution:</strong> These rules affect clinical decision support. Review carefully before adding or removing any rule.</div>
  </div>

  <div class="row g-4">
    <div class="col-md-4">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body d-flex flex-column">
          <div class="d-flex align-items-center mb-3">
            <div class="bg-danger bg-opacity-10 rounded p-3 me-3">
              <i data-lucide="zap" class="text-danger" style="width:24px;height:24px;"></i>
            </div>
            <div>
              <div class="text-muted small">Drug Interaction Rules</div>
              <div class="fs-2 fw-bold">{{ $drugInteractionCount ?? 0 }}</div>
            </div>
          </div>
          <p class="text-muted small mb-3">Rules that flag dangerous drug-drug interactions and require clinical review or override.</p>
          <a href="{{ route('portals.admin.cdss.drug-interactions') }}" class="btn btn-outline-danger btn-sm mt-auto">
            <i data-lucide="arrow-right" class="me-1" style="width:14px;height:14px;"></i> Manage Rules
          </a>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body d-flex flex-column">
          <div class="d-flex align-items-center mb-3">
            <div class="bg-warning bg-opacity-10 rounded p-3 me-3">
              <i data-lucide="shield-alert" class="text-warning" style="width:24px;height:24px;"></i>
            </div>
            <div>
              <div class="text-muted small">Allergy Alert Rules</div>
              <div class="fs-2 fw-bold">{{ $allergyAlertCount ?? 0 }}</div>
            </div>
          </div>
          <p class="text-muted small mb-3">Rules that trigger allergy alerts when a drug matches a known allergen class for a patient.</p>
          <a href="{{ route('portals.admin.cdss.allergy-alerts') }}" class="btn btn-outline-warning btn-sm mt-auto">
            <i data-lucide="arrow-right" class="me-1" style="width:14px;height:14px;"></i> Manage Rules
          </a>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body d-flex flex-column">
          <div class="d-flex align-items-center mb-3">
            <div class="bg-info bg-opacity-10 rounded p-3 me-3">
              <i data-lucide="flask-conical" class="text-info" style="width:24px;height:24px;"></i>
            </div>
            <div>
              <div class="text-muted small">Lab Alert Rules</div>
              <div class="fs-2 fw-bold">{{ $labAlertCount ?? 0 }}</div>
            </div>
          </div>
          <p class="text-muted small mb-3">Rules that alert clinicians when lab values fall outside defined thresholds for a given context.</p>
          <a href="#" class="btn btn-outline-info btn-sm mt-auto disabled">
            <i data-lucide="arrow-right" class="me-1" style="width:14px;height:14px;"></i> Coming Soon
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
