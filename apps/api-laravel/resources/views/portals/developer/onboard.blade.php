@extends('layouts.portal')
@section('title', 'Developer Portal — Set Up Your Account')

@section('content')
<div class="portal-content" style="max-width:580px;margin:0 auto;padding-top:40px;">
    <div class="portal-card">
        <div class="portal-card__header">
            <h2 class="portal-card__title">Complete Your Developer Account</h2>
        </div>
        <div class="portal-card__body" style="padding:24px 28px;">
            <p style="font-size:0.88rem;color:#6b7280;margin-bottom:20px;">
                A developer account for <strong>{{ $email }}</strong> was not found. Complete setup to access the OpesCare Developer Portal and obtain sandbox API credentials.
            </p>
            <form method="POST" action="{{ route('portals.developer.onboard.store') }}">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">

                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Display Name *</label>
                    <input type="text" name="display_name" value="{{ old('display_name') }}" required
                           style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.85rem;">
                    @error('display_name') <div style="color:#dc2626;font-size:0.76rem;margin-top:3px;">{{ $message }}</div> @enderror
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Company / Organisation (optional)</label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}"
                           style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.85rem;">
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Website (optional)</label>
                    <input type="url" name="website_url" value="{{ old('website_url') }}"
                           placeholder="https://example.com"
                           style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.85rem;">
                </div>

                <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:6px;padding:12px 14px;margin-bottom:16px;font-size:0.82rem;color:#0369a1;">
                    <strong>API Terms of Use:</strong> By creating a developer account you agree to the
                    <a href="{{ route('public.legal', 'api-developer-terms') }}" target="_blank" style="color:#0369a1;">API / Developer Terms</a>.
                    All API access is sandbox-only until a production access request is approved.
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:flex;align-items:center;gap:8px;font-size:0.84rem;cursor:pointer;">
                        <input type="checkbox" name="api_terms_accepted" value="1" required>
                        I have read and agree to the API / Developer Terms of Use
                    </label>
                    @error('api_terms_accepted') <div style="color:#dc2626;font-size:0.76rem;margin-top:3px;">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn btn--primary" style="width:100%;">Create Developer Account</button>
            </form>
        </div>
    </div>
</div>
@endsection
