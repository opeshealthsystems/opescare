@extends('layouts.portal')
@section('title', 'New Integration Certification')
@section('sidebar') @include('portals.admin.control_center._sidebar') @endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <a href="{{ route('portals.admin.certifications.index') }}" style="font-size:0.83rem;color:#6b7280;text-decoration:none;">← Integration Certifications</a>
            <h1 class="portal-page-title" style="margin-top:4px;">New Integration Certification</h1>
        </div>
    </div>

    <div class="portal-card" style="max-width:600px;">
        <div class="portal-card__body" style="padding:24px 28px;">
            <form method="POST" action="{{ route('portals.admin.certifications.store') }}">
                @csrf

                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Integration Name *</label>
                    <input type="text" name="integration_name" value="{{ old('integration_name') }}" required
                           placeholder="e.g. Sagex HIS, OpenMRS Bridge"
                           style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.85rem;">
                    @error('integration_name') <div style="color:#dc2626;font-size:0.76rem;margin-top:3px;">{{ $message }}</div> @enderror
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
                    <div>
                        <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Integration Type *</label>
                        <select name="integration_type" required
                                style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.85rem;">
                            <option value="">Select type…</option>
                            @foreach($types as $t)
                            <option value="{{ $t }}" {{ old('integration_type') === $t ? 'selected' : '' }}>{{ strtoupper($t) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Version</label>
                        <input type="text" name="version" value="{{ old('version') }}"
                               placeholder="e.g. 3.1.2"
                               style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.85rem;">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
                    <div>
                        <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Vendor Name</label>
                        <input type="text" name="vendor_name" value="{{ old('vendor_name') }}"
                               style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.85rem;">
                    </div>
                    <div>
                        <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Vendor Contact</label>
                        <input type="text" name="vendor_contact" value="{{ old('vendor_contact') }}"
                               placeholder="email or name"
                               style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.85rem;">
                    </div>
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Scope Description</label>
                    <textarea name="scope_description" rows="3"
                              placeholder="Describe what aspects of the integration will be certified…"
                              style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.85rem;resize:vertical;">{{ old('scope_description') }}</textarea>
                </div>

                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn btn--primary">Start Certification</button>
                    <a href="{{ route('portals.admin.certifications.index') }}" class="btn btn--outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
