@extends('layouts.portal')
@section('title', 'Upload File')
@section('breadcrumb_home', 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Upload File')

@section('content')
<div class="page-head">
    <h2>Upload medical file</h2>
    <div class="page-head__spacer"></div>
    <a href="{{ route('portals.staff.files.index') }}" class="btn btn-ghost btn-sm">
        <i data-lucide="arrow-left"></i> Back
    </a>
</div>
<p class="page-subtitle mb-6">Attach a document or image to a clinical resource.</p>

@if(session('error'))
    <div class="alert alert-danger mb-6"><i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div></div>
@endif

<div class="panel">
    <div class="panel-body">
        <form method="POST" action="{{ route('portals.staff.files.store') }}" enctype="multipart/form-data">
            @csrf

            {{-- Resource context --}}
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label form-label-required">Resource Type</label>
                    <select name="resource_type" class="form-control" required>
                        @foreach(['patient','visit','triage_record','clinical_note','invoice','support_ticket'] as $rt)
                            <option value="{{ $rt }}" {{ $resourceType === $rt ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ',$rt)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label form-label-required">Resource ID</label>
                    <input type="text" name="resource_id" value="{{ old('resource_id', $resourceId) }}"
                        class="form-control" required placeholder="Paste UUID of the resource">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label form-label-required">File</label>
                <div id="drop-zone" class="empty-state" style="border:2px dashed var(--p-border);border-radius:var(--p-radius);cursor:pointer;"
                     onclick="document.getElementById('file-input').click()">
                    <div class="empty-state-icon"><i data-lucide="upload-cloud"></i></div>
                    <div class="td-strong">Click to browse or drag file here</div>
                    <div id="file-name" class="td-muted">
                        Max {{ $maxSizeMb }} MB · PDF, Images, Word, Excel, CSV
                    </div>
                </div>
                <input type="file" id="file-input" name="file" required class="sr-only"
                    accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx,.xls,.xlsx,.txt,.csv"
                    onchange="updateFileName(this)">
                @error('file')<div class="form-hint">{{ $message }}</div>@enderror
            </div>

            <div class="form-row">
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

            <div class="row-actions-inline mt-6">
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="upload"></i> Upload &amp; Attach
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
