@extends('layouts.portal')
@section('title', 'Lab Alert Rules')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'CDSS')
@section('content')

<div class="breadcrumb">
    <a href="{{ route('portals.admin.cdss.index') }}">CDSS Rules</a>
    <i data-lucide="chevron-right"></i>
    <span>Lab Alert Rules</span>
</div>

<div class="page-head">
    <h2>Lab Alert Rules</h2>
    <div class="page-head__spacer"></div>
    <button onclick="opOpenModal('add-lab-modal')" class="btn btn-primary"><i data-lucide="plus"></i> Add Rule</button>
</div>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

<div class="tabs mb-6">
    <a href="{{ route('portals.admin.cdss.drug-interactions') }}" class="tab">Drug Interactions</a>
    <a href="{{ route('portals.admin.cdss.allergy-alerts') }}" class="tab">Allergy Alerts</a>
    <a href="{{ route('portals.admin.cdss.lab-alerts') }}" class="tab active">Lab Alerts</a>
</div>

<div class="alert alert-info mb-6">
    <i data-lucide="flask-conical"></i>
    <div>These rules trigger alerts when lab values fall outside defined thresholds. Review carefully before adding.</div>
</div>

<div class="panel">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Test Name</th>
                    <th>Condition</th>
                    <th>Threshold</th>
                    <th>Unit</th>
                    <th>Severity</th>
                    <th>Message</th>
                    <th>Created</th>
                    <th class="row-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rules as $rule)
                @php $sBadge=match(strtolower($rule->severity??'')){'info'=>'badge-neutral','warning'=>'badge-warning','critical'=>'badge-danger',default=>'badge-neutral'}; @endphp
                <tr>
                    <td data-label="Test Name"><span class="td-strong">{{ $rule->test_name }}</span></td>
                    <td data-label="Condition">{{ $rule->condition }}</td>
                    <td data-label="Threshold"><span class="mono td-strong">{{ $rule->threshold_value }}</span></td>
                    <td data-label="Unit">{{ $rule->unit ?? '—' }}</td>
                    <td data-label="Severity"><span class="badge {{ $sBadge }}">{{ ucfirst($rule->severity) }}</span></td>
                    <td data-label="Message">{{ Str::limit($rule->alert_message, 60) }}</td>
                    <td data-label="Created">{{ $rule->created_at->format('d M Y') }}</td>
                    <td class="row-actions" data-label="Actions">
                        <button type="button" class="btn btn-danger btn-sm" onclick="opOpenModal('delete-lab-{{ $rule->id }}')"><i data-lucide="trash-2"></i></button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="td-muted empty-cell">No lab alert rules found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(isset($rules) && $rules->hasPages())
    <div class="panel-body">{{ $rules->links() }}</div>
    @endif
</div>

{{-- Delete confirm modals --}}
@foreach($rules as $rule)
<div id="delete-lab-{{ $rule->id }}" class="modal-backdrop mt-6" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="delete-lab-{{ $rule->id }}-title">
        <h3 class="modal__title" id="delete-lab-{{ $rule->id }}-title"><i data-lucide="trash-2"></i> Delete lab alert rule</h3>
        <form action="{{ route('portals.admin.cdss.destroy-lab', $rule->id) }}" method="POST">
            @csrf @method('DELETE')
            <div class="modal__body"><p>Delete this lab alert rule for <strong>{{ $rule->test_name }}</strong>?</p></div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" onclick="opCloseModal('delete-lab-{{ $rule->id }}')">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete</button>
            </div>
        </form>
    </div>
</div>
@endforeach

{{-- Add Lab Alert Modal --}}
<div id="add-lab-modal" class="modal-backdrop mt-6" hidden>
    <div class="modal modal--lg" role="dialog" aria-modal="true" aria-labelledby="add-lab-title">
        <h3 class="modal__title" id="add-lab-title"><i data-lucide="flask-conical"></i> Add Lab Alert Rule</h3>
        <form action="{{ route('portals.admin.cdss.store-lab') }}" method="POST">
            @csrf
            <div class="modal__body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label form-label-required">Test Name</label>
                        <input type="text" name="test_name" class="form-control" placeholder="e.g. Serum Potassium" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Unit</label>
                        <input type="text" name="unit" class="form-control" placeholder="e.g. mmol/L">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label form-label-required">Condition</label>
                        <select name="condition" class="form-control" required>
                            <option value="">Select…</option>
                            <option value="greater_than">Greater than (&gt;)</option>
                            <option value="less_than">Less than (&lt;)</option>
                            <option value="greater_than_or_equal">≥</option>
                            <option value="less_than_or_equal">≤</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label form-label-required">Threshold</label>
                        <input type="number" name="threshold_value" step="any" class="form-control" placeholder="e.g. 6.0" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label form-label-required">Severity</label>
                        <select name="severity" class="form-control" required>
                            <option value="">Select…</option>
                            <option value="info">Info</option>
                            <option value="warning">Warning</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label form-label-required">Alert Message</label>
                    <input type="text" name="alert_message" class="form-control" placeholder="e.g. Hyperkalemia detected — review cardiac risk" required>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" onclick="opCloseModal('add-lab-modal')" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary"><i data-lucide="plus"></i> Add Rule</button>
            </div>
        </form>
    </div>
</div>
@endsection
@section('scripts')
<script>
function opOpenModal(id){ document.getElementById(id).removeAttribute('hidden'); }
function opCloseModal(id){ document.getElementById(id).setAttribute('hidden',''); }
document.addEventListener('keydown', function(e){
    if(e.key==='Escape'){ document.querySelectorAll('.modal-backdrop').forEach(function(m){ m.setAttribute('hidden',''); }); }
});
</script>
@endsection
