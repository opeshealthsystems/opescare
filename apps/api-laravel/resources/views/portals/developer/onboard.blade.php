@extends('layouts.portal')
@section('title', 'Developer Portal — Set Up Your Account')

@section('content')
<div class="form-panel">
    <div class="panel">
        <div class="panel-header">
            <h3 class="panel-title"><i data-lucide="user-plus"></i> Complete your developer account</h3>
        </div>
        <div class="panel-body">
            <p class="td-muted mb-6">
                A developer account for <strong>{{ $email }}</strong> was not found. Complete setup to access the OpesCare Developer Portal and obtain sandbox API credentials.
            </p>
            <form method="POST" action="{{ route('portals.developer.onboard.store') }}">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="form-group">
                    <label class="form-label form-label-required">Display name</label>
                    <input type="text" name="display_name" value="{{ old('display_name') }}" required class="form-control">
                    @error('display_name') <div class="form-hint">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Company / organisation (optional)</label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}" class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label">Website (optional)</label>
                    <input type="url" name="website_url" value="{{ old('website_url') }}" placeholder="https://example.com" class="form-control">
                </div>

                <div class="alert alert-info mb-6">
                    <i data-lucide="info"></i>
                    <div><strong>API terms of use:</strong> By creating a developer account you agree to the
                    <a href="{{ route('public.legal', 'api-developer-terms') }}" target="_blank">API / Developer Terms</a>.
                    All API access is sandbox-only until a production access request is approved.</div>
                </div>

                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" name="api_terms_accepted" value="1" required>
                        I have read and agree to the API / Developer Terms of Use
                    </label>
                    @error('api_terms_accepted') <div class="form-hint">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn btn-primary btn-block"><i data-lucide="user-plus"></i> Create developer account</button>
            </form>
        </div>
    </div>
</div>
@endsection
