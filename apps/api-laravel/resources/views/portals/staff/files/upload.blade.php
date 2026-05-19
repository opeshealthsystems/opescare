@extends('layouts.portal')
@section('title', 'Upload File')
@section('breadcrumb_home', 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Upload File')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Upload Medical File</h1>
        <p class="page-subtitle">Attach a document or image to a clinical resource.</p>
    </div>
    <a href="{{ route('portals.staff.files.index') }}" class="btn btn-ghost btn-sm">
        <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Back
    </a>
</div>

@if(session('error'))
    <div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;"><i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div></div>
@endif

<div class="panel" style="max-width:560px;">
    <div class="panel-body">
        <form method="POST" action="{{ route('portals.staff.files.store') }}" enctype="multipart/form-data">
            @csrf

            {{-- Resource context --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1rem;">
                <div class="form-group">
                    <label class="form-label form-label-required">Resource Type *</label>
                    <select name="resource_type" class="form-control" required>
                        @foreach(['patient','visit','triage_record','clinical_note','invoice','support_ticket'] as $rt)
                            <option value="{{ $rt }}" {{ $resourceType === $rt ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ',$rt)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label form-label-required">Resource ID *</label>
                    <input type="text" name="resource_id" value="{{ old('resource_id', $resourceId) }}"
                        class="form-control" required placeholder="Paste UUID of the resource">
                </div>
            </div>

            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label form-label-required">File *</label>
                <div id="drop-zone" style="border:2px dashed var(--p-border);border-radius:var(--p-radius);padding:2rem 1.5rem;text-align:center;cursor:pointer;transition:border-color .15s;"
                     onclick="document.getElementById('file-input').click()">
                    <i data-lucide="upload-cloud" style="width:32px;height:32px;color:var(--p-text-muted);margin:0 auto .5rem;display:block;"></i>
                    <div style="font-size:.85rem;font-weight:500;">Click to browse or drag file here</div>
                    <div id="file-name" style="font-size:.75rem;color:var(--p-text-muted);margin-top:.25rem;">
                        Max {{ $maxSizeMb }} MB · PDF, Images, Word, Excel, CSV
                    </div>
                </div>
                <input type="file" id="file-input" name="file" required style="display:none;"
                    accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx,.xls,.xlsx,.txt,.csv"
                    onchange="updateFileName(this)">
                @error('file')<div style="font-size:.78rem;color:var(--p-danger);margin-top:3px;">{{ $message }}</div>@enderror
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-control">
                        <option value="">— Select category —</option>
                        @foreach($categories as $val => $label)
                            <option value="{{ $val }}" {{ old('category') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" value="{{ old('description') }}" class="form-control"
                        maxlength="300" placeholder="Optional short note">
                </div>
            </div>

            <div style="display:flex;gap:.75rem;margin-top:1.25rem;">
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="upload" style="width:14px;height:14px;"></i> Upload & Attach
                </button>
                <a href="{{ route('portals.staff.files.index') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function updateFileName(input) {
    const label = document.getElementById('file-name');
    if (input.files && input.files[0]) {
        const f = input.files[0];
        const sizeMb = (f.size / (1024*1024)).toFixed(2);
        label.textContent = f.name + ' (' + sizeMb + ' MB)';
        label.style.color = 'var(--p-text)';
        document.getElementById('drop-zone').style.borderColor = 'var(--p-primary)';
    }
}

// Drag-and-drop
const dz = document.getElementById('drop-zone');
dz.addEventListener('dragover', e => { e.preventDefault(); dz.style.borderColor='var(--p-primary)'; });
dz.addEventListener('dragleave', () => dz.style.borderColor='');
dz.addEventListener('drop', e => {
    e.preventDefault();
    dz.style.borderColor='var(--p-primary)';
    const fi = document.getElementById('file-input');
    fi.files = e.dataTransfer.files;
    updateFileName(fi);
});
</script>
@endsection
