@extends('layouts.portal')
@section('title', 'New Integration Certification')
@section('sidebar') @include('portals.admin.control_center._sidebar') @endsection

@section('content')

<div class="breadcrumb">
    <a href="{{ route('portals.admin.certifications.index') }}">Integration Certifications</a>
    <i data-lucide="chevron-right"></i>
    <span>New</span>
</div>

<div class="page-head">
    <h2>New integration certification</h2>
</div>

<div class="panel form-panel">
    <div class="panel-body">
        <form method="POST" action="{{ route('portals.admin.certifications.store') }}">
            @csrf

            <div class="form-group mb-4">
                <label class="form-label form-label-required">Integration Name</label>
                <input type="text" name="integration_name" value="{{ old('integration_name') }}" required
                       class="form-control" placeholder="e.g. Sagex HIS, OpenMRS Bridge">
                @error('integration_name') <div class="form-hint">{{ $message }}</div> @enderror
            </div>

            <div class="form-row mb-4">
                <div class="form-group">
                    <label class="form-label form-label-required">Integration Type</label>
                    <select name="integration_type" class="form-control" required>
                        <option value="">Select type…</option>
                        @foreach($types as $t)
                        <option value="{{ $t }}" {{ old('integration_type') === $t ? 'selected' : '' }}>{{ strtoupper($t) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Version</label>
                    <input type="text" name="version" value="{{ old('version') }}" class="form-control" placeholder="e.g. 3.1.2">
                </div>
            </div>

            <div class="form-row mb-4">
                <div class="form-group">
                    <label class="form-label">Vendor Name</label>
                    <input type="text" name="vendor_name" value="{{ old('vendor_name') }}" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Vendor Contact</label>
                    <input type="text" name="vendor_contact" value="{{ old('vendor_contact') }}" class="form-control" placeholder="email or name">
                </div>
            </div>

            <div class="form-group mb-6">
                <label class="form-label">Scope Description</label>
                <textarea name="scope_description" rows="3" class="form-control"
                          placeholder="Describe what aspects of the integration will be certified…">{{ old('scope_description') }}</textarea>
            </div>

            <div class="row-actions-inline">
                <button type="submit" class="btn btn-primary">Start Certification</button>
                <a href="{{ route('portals.admin.certifications.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
