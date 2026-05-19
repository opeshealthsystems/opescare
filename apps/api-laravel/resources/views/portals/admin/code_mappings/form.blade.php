@extends('layouts.portal')
@section('title', 'Add Code Mapping')
@section('sidebar') @include('portals.admin.control_center._sidebar') @endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <a href="{{ route('portals.admin.code_mappings.index') }}" style="font-size:0.83rem;color:#6b7280;text-decoration:none;">← Code System Mappings</a>
            <h1 class="portal-page-title" style="margin-top:4px;">Add Code Mapping</h1>
            <p class="portal-page-subtitle">Map a local OpesCare code to a standard terminology</p>
        </div>
    </div>

    <div class="portal-card" style="max-width:680px;">
        <div class="portal-card__body" style="padding:24px 28px;">
            <form method="POST" action="{{ route('portals.admin.code_mappings.store') }}">
                @csrf

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                    <div>
                        <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Local Code *</label>
                        <input type="text" name="local_code" value="{{ old('local_code') }}" required
                               placeholder="e.g. CBC-001, DIAG-A09"
                               style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.85rem;">
                        @error('local_code') <div style="color:#dc2626;font-size:0.76rem;margin-top:3px;">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Resource Type *</label>
                        <select name="resource_type" required
                                style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.85rem;">
                            <option value="">Select type…</option>
                            @foreach($resourceTypes as $rt)
                            <option value="{{ $rt }}" {{ old('resource_type') === $rt ? 'selected' : '' }}>{{ $rt }}</option>
                            @endforeach
                        </select>
                        @error('resource_type') <div style="color:#dc2626;font-size:0.76rem;margin-top:3px;">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Local Name</label>
                    <input type="text" name="local_name" value="{{ old('local_name') }}"
                           placeholder="e.g. Complete Blood Count, Acute gastroenteritis"
                           style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.85rem;">
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Local Unit <span style="font-weight:400;color:#9ca3af;">(lab tests)</span></label>
                    <input type="text" name="local_unit" value="{{ old('local_unit') }}"
                           placeholder="e.g. g/dL, mmol/L, cells/µL"
                           style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.85rem;">
                </div>

                <hr style="border:none;border-top:1px solid #f3f4f6;margin:20px 0;">
                <div style="font-size:0.8rem;font-weight:700;color:#374151;margin-bottom:12px;text-transform:uppercase;letter-spacing:.04em;">Standard Terminology</div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                    <div>
                        <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Standard System *</label>
                        <select name="standard_system" required
                                style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.85rem;">
                            <option value="">Select system…</option>
                            @foreach($systems as $sys)
                            <option value="{{ $sys }}" {{ old('standard_system') === $sys ? 'selected' : '' }}>{{ strtoupper($sys) }}</option>
                            @endforeach
                        </select>
                        @error('standard_system') <div style="color:#dc2626;font-size:0.76rem;margin-top:3px;">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Standard Code *</label>
                        <input type="text" name="standard_code" value="{{ old('standard_code') }}" required
                               placeholder="e.g. 58410-2, A09, J01CA01"
                               style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.85rem;font-family:monospace;">
                        @error('standard_code') <div style="color:#dc2626;font-size:0.76rem;margin-top:3px;">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Standard Display Name</label>
                    <input type="text" name="standard_display" value="{{ old('standard_display') }}"
                           placeholder="e.g. CBC W Auto Differential panel, Infectious gastroenteritis NOS"
                           style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.85rem;">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                    <div>
                        <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Mapping Confidence *</label>
                        <select name="mapping_confidence" required
                                style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.85rem;">
                            @foreach($confidences as $c)
                            <option value="{{ $c }}" {{ old('mapping_confidence', 'manual') === $c ? 'selected' : '' }}>{{ ucfirst($c) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Standard Version</label>
                        <input type="text" name="standard_version" value="{{ old('standard_version') }}"
                               placeholder="e.g. 2.77, 11th Rev"
                               style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.85rem;">
                    </div>
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Notes</label>
                    <textarea name="notes" rows="2" placeholder="Optional mapping notes or justification…"
                              style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.85rem;resize:vertical;">{{ old('notes') }}</textarea>
                </div>

                <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:6px;padding:10px 14px;font-size:0.79rem;color:#92400e;margin-bottom:20px;">
                    ⓘ New mappings are created with <strong>Pending</strong> status and must be approved by a super_admin or data_steward before they are used in FHIR output and public health reports.
                </div>

                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn btn--primary">Add Mapping</button>
                    <a href="{{ route('portals.admin.code_mappings.index') }}" class="btn btn--outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
