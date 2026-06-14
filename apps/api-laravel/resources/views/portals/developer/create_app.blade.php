@extends('layouts.portal')
@section('title', 'New App')
@section('sidebar_nav') @include('portals.developer._sidebar') @endsection

@section('content')

    <div class="breadcrumb">
        <a href="{{ route('portals.developer.apps') }}">My apps</a>
        <i data-lucide="chevron-right"></i>
        <span>New app</span>
    </div>

    <div class="page-head">
        <h2>New app</h2>
    </div>

    <div class="panel form-panel">
        <div class="panel-body">

            <div class="alert alert-info mb-6">
                <i data-lucide="info"></i>
                <div>New apps receive <strong>sandbox credentials</strong> only. To access production data, submit a
                <a href="{{ route('portals.developer.production_requests.create') }}">production access request</a>
                after your app is ready.</div>
            </div>

            <form method="POST" action="{{ route('portals.developer.apps.store') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label form-label-required">App name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           placeholder="e.g. MyHospital Connector" class="form-control">
                    @error('name') <div class="form-hint">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="3" class="form-control"
                              placeholder="Brief description of what your integration does…">{{ old('description') }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Website / redirect URL (optional)</label>
                    <input type="url" name="website_url" value="{{ old('website_url') }}"
                           placeholder="https://yourapp.example.com" class="form-control">
                </div>

                <div class="alert alert-warning mb-6">
                    <i data-lucide="alert-triangle"></i>
                    <div>Your sandbox client secret will be shown <strong>once</strong> after creation. Store it securely.</div>
                </div>

                <div class="form-actions-end">
                    <a href="{{ route('portals.developer.apps') }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i data-lucide="plus"></i> Create app</button>
                </div>
            </form>
        </div>
    </div>
@endsection
