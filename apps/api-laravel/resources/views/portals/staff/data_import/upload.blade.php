@extends('layouts.portal')

@section('title', __('staff_data.title_upload', [], app()->getLocale()) ?: 'New Import — Upload File')

@section('sidebar_role_badge')
<div class="sidebar-role-badge">{{ __('public.staff_portal.cdss_sidebar_role') }}</div>
@endsection
@section('sidebar_user_role', __('public.staff_portal.cdss_sidebar_role'))

@section('sidebar_nav')
            @endif

            <form method="POST" action="{{ route('portals.staff.data_import.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="form-group" style="margin-bottom:1rem;">
                    <label class="form-label">{{ __('public.stf_import_import_type') }}</label>
                    <select name="import_type" class="form-control" required onchange="updateFieldHint(this.value)">
                        <option value="">{{ __('public.stf_import_select_type') }}</option>
                        @foreach($importTypes as $key => $def)
                            <option value="{{ $key }}" {{ old('import_type') === $key ? 'selected' : '' }}>{{ $def['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Field hint panel --}}
                <div id="field-hint" style="display:none;background:var(--p-surface-2,#f8f9fa);border:1px solid var(--p-border);border-radius:var(--p-radius);padding:.85rem 1rem;margin-bottom:1rem;font-size:.82rem;">
                    <div style="font-weight:600;margin-bottom:.4rem;">{{ __('staff_data.expected_columns', [], app()->getLocale()) ?: 'Expected columns for this type:' }}</div>
                    <div id="field-hint-required" style="margin-bottom:.3rem;"></div>
                    <div id="field-hint-optional" style="color:var(--p-text-muted);"></div>
                </div>

                <div class="form-group" style="margin-bottom:1.25rem;">
                    <label class="form-label">{{ __('public.stf_import_file_label') }}</label>
                    <input type="file" name="file" class="form-control" required accept=".csv,.xlsx,.xls">
                    <div style="font-size:.75rem;color:var(--p-text-muted);margin-top:.3rem;">
                        {{ __('public.stf_import_header_hint') }}
                    </div>
                </div>

                <div style="display:flex;gap:.5rem;justify-content:flex-end;">
                    <a href="{{ route('portals.staff.data_import.index') }}" class="btn btn-ghost btn-sm">{{ __('public.stf_import_cancel_link') }}</a>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i data-lucide="upload" style="width:13px;height:13px;"></i>
                        {{ __('public.stf_import_upload_btn') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
var importTypeFields = @json(collect($importTypes)->map(fn($d) => ['required' => $d['required'], 'optional' => $d['optional']]));

function updateFieldHint(type) {
    var hint = document.getElementById('field-hint');
    var req  = document.getElementById('field-hint-required');
    var opt  = document.getElementById('field-hint-optional');

    if (!type || !importTypeFields[type]) {
        hint.style.display = 'none';
        return;
    }

    var fields = importTypeFields[type];
    req.innerHTML = '<strong>{{ __('staff_data.js_required', [], app()->getLocale()) ?: 'Required:' }}</strong> ' + fields.required.join(', ');
    opt.innerHTML = fields.optional.length ? '<strong>{{ __('staff_data.js_optional', [], app()->getLocale()) ?: 'Optional:' }}</strong> ' + fields.optional.join(', ') : '';
    hint.style.display = 'block';
}
// Trigger on page load if old value set
var sel = document.querySelector('[name=import_type]');
if (sel && sel.value) updateFieldHint(sel.value);
</script>
@endsection
