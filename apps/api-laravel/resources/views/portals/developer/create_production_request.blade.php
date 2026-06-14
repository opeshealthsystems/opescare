@extends('layouts.portal')
@section('title', 'Request Production Access')
@section('sidebar_nav') @include('portals.developer._sidebar') @endsection

@section('content')

    <div class="breadcrumb">
        <a href="{{ route('portals.developer.production_requests') }}">Production requests</a>
        <i data-lucide="chevron-right"></i>
        <span>Request production access</span>
    </div>

    <div class="page-head">
        <h2>Request production access</h2>
    </div>

    <div class="panel form-panel">
        <div class="panel-body">

            <div class="alert alert-warning mb-6">
                <i data-lucide="alert-triangle"></i>
                <div>Production integrations access real patient data. Approval requires a security review.
                Do not attempt to access production without prior approval — violations result in immediate revocation.</div>
            </div>

            <form method="POST" action="{{ route('portals.developer.production_requests.store') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label form-label-required">App (sandbox integration client)</label>
                    <select name="integration_client_id" required class="form-control">
                        <option value="">Select app…</option>
                        @foreach($clients as $client)
                        <option value="{{ $client->client_id }}" {{ old('integration_client_id') === $client->client_id ? 'selected' : '' }}>
                            {{ $client->name ?? $client->client_id }}
                        </option>
                        @endforeach
                    </select>
                    @error('integration_client_id') <div class="form-hint">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label form-label-required">Use case</label>
                    <input type="text" name="use_case" value="{{ old('use_case') }}" required class="form-control"
                           placeholder="e.g. Hospital Information System integration for patient record sync">
                    @error('use_case') <div class="form-hint">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label form-label-required">Technical description <span class="td-muted">(min 50 characters)</span></label>
                    <textarea name="technical_description" rows="4" required minlength="50" class="form-control"
                              placeholder="Describe the integration architecture, data flows, security measures, and how you handle patient data…">{{ old('technical_description') }}</textarea>
                    @error('technical_description') <div class="form-hint">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label form-label-required">Requested scopes <span class="td-muted">(select all that apply)</span></label>
                    <div class="form-row">
                        @foreach($scopeOptions as $scope)
                        <label class="form-check">
                            <input type="checkbox" name="requested_scopes[]" value="{{ $scope }}"
                                   {{ in_array($scope, old('requested_scopes', [])) ? 'checked' : '' }}>
                            <span class="mono">{{ $scope }}</span>
                        </label>
                        @endforeach
                    </div>
                    @error('requested_scopes') <div class="form-hint">{{ $message }}</div> @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Estimated daily requests</label>
                        <select name="estimated_daily_requests" class="form-control">
                            <option value="">Select range…</option>
                            <option value="< 100">Less than 100</option>
                            <option value="100–1 000">100–1 000</option>
                            <option value="1 000–10 000">1 000–10 000</option>
                            <option value="10 000–100 000">10 000–100 000</option>
                            <option value="> 100 000">More than 100 000</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Data residency region</label>
                        <input type="text" name="data_residency_region" value="{{ old('data_residency_region') }}" class="form-control"
                               placeholder="e.g. West Africa, EU, US">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" name="handles_patient_data" value="1" {{ old('handles_patient_data') ? 'checked' : '' }}>
                        This integration handles identifiable patient data
                    </label>
                    <label class="form-check">
                        <input type="checkbox" name="security_review_done" value="1" {{ old('security_review_done') ? 'checked' : '' }}>
                        We have completed an internal security review of this integration
                    </label>
                </div>

                <div class="alert alert-danger mb-6">
                    <i data-lucide="shield-alert"></i>
                    <div>By submitting this request you confirm this integration complies with all applicable data protection laws and the
                    <a href="{{ route('public.legal', 'api-developer-terms') }}" target="_blank">OpesCare API Developer Terms</a>.</div>
                </div>

                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" name="terms_accepted" value="1" required {{ old('terms_accepted') ? 'checked' : '' }}>
                        I confirm compliance with OpesCare API Developer Terms and applicable data protection law
                    </label>
                    @error('terms_accepted') <div class="form-hint">{{ $message }}</div> @enderror
                </div>

                <div class="form-actions-end">
                    <a href="{{ route('portals.developer.production_requests') }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i data-lucide="rocket"></i> Submit request</button>
                </div>
            </form>
        </div>
    </div>

@endsection
