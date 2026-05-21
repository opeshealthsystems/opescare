@extends('layouts.portal')
@section('title', 'Request Production Access')
@section('sidebar_nav') @include('portals.developer._sidebar') @endsection

@section('content')

    <div class="portal-page-header">
        <div>
            <a href="{{ route('portals.developer.production_requests') }}" style="font-size:0.83rem;color:#6b7280;text-decoration:none;">← Production Requests</a>
            <h1 class="portal-page-title" style="margin-top:4px;">Request Production Access</h1>
        </div>
    </div>

    <div class="portal-card" style="max-width:700px;">
        <div class="portal-card__body" style="padding:24px 28px;">

            <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:6px;padding:12px 14px;margin-bottom:20px;font-size:0.83rem;color:#92400e;">
                ⚠ Production integrations access real patient data. Approval requires a security review.
                Do not attempt to access production without prior approval — violations result in immediate revocation.
            </div>

            <form method="POST" action="{{ route('portals.developer.production_requests.store') }}">
                @csrf

                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">App (Sandbox Integration Client) *</label>
                    <select name="integration_client_id" required
                            style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.85rem;">
                        <option value="">Select app…</option>
                        @foreach($clients as $client)
                        <option value="{{ $client->client_id }}" {{ old('integration_client_id') === $client->client_id ? 'selected' : '' }}>
                            {{ $client->name ?? $client->client_id }}
                        </option>
                        @endforeach
                    </select>
                    @error('integration_client_id') <div style="color:#dc2626;font-size:0.76rem;margin-top:3px;">{{ $message }}</div> @enderror
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Use Case *</label>
                    <input type="text" name="use_case" value="{{ old('use_case') }}" required
                           placeholder="e.g. Hospital Information System integration for patient record sync"
                           style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.85rem;">
                    @error('use_case') <div style="color:#dc2626;font-size:0.76rem;margin-top:3px;">{{ $message }}</div> @enderror
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Technical Description * <span style="font-weight:400;color:#9ca3af;">(min 50 characters)</span></label>
                    <textarea name="technical_description" rows="4" required minlength="50"
                              placeholder="Describe the integration architecture, data flows, security measures, and how you handle patient data…"
                              style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.85rem;resize:vertical;">{{ old('technical_description') }}</textarea>
                    @error('technical_description') <div style="color:#dc2626;font-size:0.76rem;margin-top:3px;">{{ $message }}</div> @enderror
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:6px;">Requested Scopes * <span style="font-weight:400;color:#9ca3af;">(select all that apply)</span></label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;border:1px solid #e5e7eb;border-radius:6px;padding:12px;">
                        @foreach($scopeOptions as $scope)
                        <label style="display:flex;align-items:center;gap:6px;font-size:0.82rem;cursor:pointer;">
                            <input type="checkbox" name="requested_scopes[]" value="{{ $scope }}"
                                   {{ in_array($scope, old('requested_scopes', [])) ? 'checked' : '' }}>
                            <span style="font-family:monospace;">{{ $scope }}</span>
                        </label>
                        @endforeach
                    </div>
                    @error('requested_scopes') <div style="color:#dc2626;font-size:0.76rem;margin-top:3px;">{{ $message }}</div> @enderror
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
                    <div>
                        <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Estimated Daily Requests</label>
                        <select name="estimated_daily_requests"
                                style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.85rem;">
                            <option value="">Select range…</option>
                            <option value="< 100">Less than 100</option>
                            <option value="100–1 000">100–1 000</option>
                            <option value="1 000–10 000">1 000–10 000</option>
                            <option value="10 000–100 000">10 000–100 000</option>
                            <option value="> 100 000">More than 100 000</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Data Residency Region</label>
                        <input type="text" name="data_residency_region" value="{{ old('data_residency_region') }}"
                               placeholder="e.g. West Africa, EU, US"
                               style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.85rem;">
                    </div>
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:flex;align-items:center;gap:8px;font-size:0.84rem;margin-bottom:8px;cursor:pointer;">
                        <input type="checkbox" name="handles_patient_data" value="1" {{ old('handles_patient_data') ? 'checked' : '' }}>
                        This integration handles identifiable patient data
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;font-size:0.84rem;cursor:pointer;">
                        <input type="checkbox" name="security_review_done" value="1" {{ old('security_review_done') ? 'checked' : '' }}>
                        We have completed an internal security review of this integration
                    </label>
                </div>

                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:12px 14px;margin-bottom:16px;font-size:0.82rem;color:#991b1b;">
                    By submitting this request you confirm this integration complies with all applicable data protection laws and the
                    <a href="{{ route('public.legal', 'api-developer-terms') }}" target="_blank" style="color:#991b1b;">OpesCare API Developer Terms</a>.
                </div>

                <div style="margin-bottom:24px;">
                    <label style="display:flex;align-items:center;gap:8px;font-size:0.84rem;cursor:pointer;">
                        <input type="checkbox" name="terms_accepted" value="1" required {{ old('terms_accepted') ? 'checked' : '' }}>
                        I confirm compliance with OpesCare API Developer Terms and applicable data protection law *
                    </label>
                    @error('terms_accepted') <div style="color:#dc2626;font-size:0.76rem;margin-top:3px;">{{ $message }}</div> @enderror
                </div>

                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn btn--primary">Submit Request</button>
                    <a href="{{ route('portals.developer.production_requests') }}" class="btn btn--outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
