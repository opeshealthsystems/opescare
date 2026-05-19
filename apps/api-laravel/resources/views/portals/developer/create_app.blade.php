@extends('layouts.portal')
@section('title', 'New App')
@section('sidebar') @include('portals.developer._sidebar') @endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <a href="{{ route('portals.developer.apps') }}" style="font-size:0.83rem;color:#6b7280;text-decoration:none;">← My Apps</a>
            <h1 class="portal-page-title" style="margin-top:4px;">New App</h1>
        </div>
    </div>

    <div class="portal-card" style="max-width:560px;">
        <div class="portal-card__body" style="padding:24px 28px;">

            <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:6px;padding:12px 14px;margin-bottom:20px;font-size:0.83rem;color:#0369a1;">
                New apps receive <strong>sandbox credentials</strong> only. To access production data, submit a
                <a href="{{ route('portals.developer.production_requests.create') }}" style="color:#0369a1;">production access request</a>
                after your app is ready.
            </div>

            <form method="POST" action="{{ route('portals.developer.apps.store') }}">
                @csrf
                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">App Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           placeholder="e.g. MyHospital Connector"
                           style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.85rem;">
                    @error('name') <div style="color:#dc2626;font-size:0.76rem;margin-top:3px;">{{ $message }}</div> @enderror
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Description</label>
                    <textarea name="description" rows="3"
                              placeholder="Brief description of what your integration does…"
                              style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.85rem;resize:vertical;">{{ old('description') }}</textarea>
                </div>

                <div style="margin-bottom:24px;">
                    <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Website / Redirect URL (optional)</label>
                    <input type="url" name="website_url" value="{{ old('website_url') }}"
                           placeholder="https://yourapp.example.com"
                           style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.85rem;">
                </div>

                <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:6px;padding:10px 14px;margin-bottom:20px;font-size:0.81rem;color:#92400e;">
                    Your sandbox client secret will be shown <strong>once</strong> after creation. Store it securely.
                </div>

                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn btn--primary">Create App</button>
                    <a href="{{ route('portals.developer.apps') }}" class="btn btn--outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
