@extends('layouts.portal')
@section('title', 'Add Code Mapping')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Code Mappings')

@section('content')

<div class="breadcrumb">
    <a href="{{ route('portals.admin.code_mappings.index') }}">Code System Mappings</a>
    <i data-lucide="chevron-right"></i>
    <span>Add Mapping</span>
</div>

<div class="page-head">
    <h2>Add Code Mapping</h2>
    <div class="page-head__spacer"></div>
</div>
<p class="td-muted mb-6">Map a local OpesCare code to a standard terminology</p>

<div class="panel">
    <div class="panel-body">
        <form method="POST" action="{{ route('portals.admin.code_mappings.store') }}">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label form-label-required">Local Code</label>
                    <input type="text" name="local_code" value="{{ old('local_code') }}" required placeholder="e.g. CBC-001, DIAG-A09" class="form-control">
                    @error('local_code') <div class="form-hint">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label form-label-required">Resource Type</label>
                    <select name="resource_type" required class="form-control">
                        <option value="">Select type…</option>
                        @foreach($resourceTypes as $rt)
                        <option value="{{ $rt }}" {{ old('resource_type') === $rt ? 'selected' : '' }}>{{ $rt }}</option>
                        @endforeach
                    </select>
                    @error('resource_type') <div class="form-hint">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Local Name</label>
                <input type="text" name="local_name" value="{{ old('local_name') }}" placeholder="e.g. Complete Blood Count, Acute gastroenteritis" class="form-control">
            </div>

            <div class="form-group">
                <label class="form-label">Local Unit <span class="td-muted">(lab tests)</span></label>
                <input type="text" name="local_unit" value="{{ old('local_unit') }}" placeholder="e.g. g/dL, mmol/L, cells/µL" class="form-control">
            </div>

            <div class="panel-header mt-6 mb-6"><h3 class="panel-title">Standard Terminology</h3></div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label form-label-required">Standard System</label>
                    <select name="standard_system" required class="form-control">
                        <option value="">Select system…</option>
                        @foreach($systems as $sys)
                        <option value="{{ $sys }}" {{ old('standard_system') === $sys ? 'selected' : '' }}>{{ strtoupper($sys) }}</option>
                        @endforeach
                    </select>
                    @error('standard_system') <div class="form-hint">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label form-label-required">Standard Code</label>
                    <input type="text" name="standard_code" value="{{ old('standard_code') }}" required placeholder="e.g. 58410-2, A09, J01CA01" class="form-control mono">
                    @error('standard_code') <div class="form-hint">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Standard Display Name</label>
                <input type="text" name="standard_display" value="{{ old('standard_display') }}" placeholder="e.g. CBC W Auto Differential panel, Infectious gastroenteritis NOS" class="form-control">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label form-label-required">Mapping Confidence</label>
                    <select name="mapping_confidence" required class="form-control">
                        @foreach($confidences as $c)
                        <option value="{{ $c }}" {{ old('mapping_confidence', 'manual') === $c ? 'selected' : '' }}>{{ ucfirst($c) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Standard Version</label>
                    <input type="text" name="standard_version" value="{{ old('standard_version') }}" placeholder="e.g. 2.77, 11th Rev" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="2" placeholder="Optional mapping notes or justification…" class="form-control">{{ old('notes') }}</textarea>
            </div>

            <div class="alert alert-warning mb-6">
                <i data-lucide="info"></i>
                <div>New mappings are created with <strong>Pending</strong> status and must be approved by a super_admin or data_steward before they are used in FHIR output and public health reports.</div>
            </div>

            <div class="row-actions">
                <button type="submit" class="btn btn-primary">Add Mapping</button>
                <a href="{{ route('portals.admin.code_mappings.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
