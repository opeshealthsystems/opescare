@extends('layouts.portal')
@section('title', 'Drug Interaction Rules')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'CDSS')
@section('content')

<div class="breadcrumb">
    <a href="{{ route('portals.admin.cdss.index') }}">CDSS Rules</a>
    <i data-lucide="chevron-right"></i>
    <span>Drug Interaction Rules</span>
</div>

<div class="page-head">
    <h2>Drug Interaction Rules</h2>
    <div class="page-head__spacer"></div>
    <button onclick="opOpenModal('add-drug-modal')" class="btn btn-danger"><i data-lucide="plus"></i> Add Rule</button>
</div>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

<div class="tabs mb-6">
    <a href="{{ route('portals.admin.cdss.drug-interactions') }}" class="tab active">Drug Interactions</a>
    <a href="{{ route('portals.admin.cdss.allergy-alerts') }}" class="tab">Allergy Alerts</a>
    <a href="{{ route('portals.admin.cdss.lab-alerts') }}" class="tab">Lab Alerts</a>
</div>

<div class="alert alert-warning mb-6">
    <i data-lucide="alert-triangle"></i>
    <div>These rules affect clinical decision support. Review carefully before adding.</div>
</div>

<div class="panel">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Drug A</th>
                    <th>Drug B</th>
                    <th>Severity</th>
                    <th>Description</th>
                    <th>Action Required</th>
                    <th>Created</th>
                    <th class="row-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rules as $rule)
                @php $sBadge=match($rule->severity??''){'mild'=>'badge-primary','moderate'=>'badge-warning','severe'=>'badge-danger','contraindicated'=>'badge-danger',default=>'badge-neutral'}; @endphp
                <tr>
                    <td data-label="Drug A"><span class="td-strong">{{ $rule->drug_a }}</span></td>
                    <td data-label="Drug B"><span class="td-strong">{{ $rule->drug_b }}</span></td>
                    <td data-label="Severity"><span class="badge {{ $sBadge }}">{{ ucfirst($rule->severity) }}</span></td>
                    <td data-label="Description">{{ Str::limit($rule->description, 80) }}</td>
                    <td data-label="Action Required">{{ Str::limit($rule->action_required, 60) }}</td>
                    <td data-label="Created">{{ $rule->created_at->format('d M Y') }}</td>
                    <td class="row-actions" data-label="Actions">
                        <button type="button" class="btn btn-danger btn-sm" onclick="opOpenModal('delete-drug-{{ $rule->id }}')"><i data-lucide="trash-2"></i></button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="td-muted empty-cell">No drug interaction rules found.</td></tr>
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
<div id="delete-drug-{{ $rule->id }}" class="modal-backdrop mt-6" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="delete-drug-{{ $rule->id }}-title">
        <h3 class="modal__title" id="delete-drug-{{ $rule->id }}-title"><i data-lucide="trash-2"></i> Delete interaction rule</h3>
        <form action="{{ route('portals.admin.cdss.destroy-drug', $rule->id) }}" method="POST">
            @csrf @method('DELETE')
            <div class="modal__body"><p>Delete this drug interaction rule (<strong>{{ $rule->drug_a }}</strong> + <strong>{{ $rule->drug_b }}</strong>)?</p></div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" onclick="opCloseModal('delete-drug-{{ $rule->id }}')">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete</button>
            </div>
        </form>
    </div>
</div>
@endforeach

{{-- Add Drug Interaction Modal --}}
<div id="add-drug-modal" class="modal-backdrop mt-6" hidden>
    <div class="modal modal--lg" role="dialog" aria-modal="true" aria-labelledby="add-drug-title">
        <h3 class="modal__title" id="add-drug-title"><i data-lucide="zap"></i> Add Drug Interaction Rule</h3>
        <form action="{{ route('portals.admin.cdss.store-drug') }}" method="POST">
            @csrf
            <div class="modal__body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label form-label-required">Drug A</label>
                        <input type="text" name="drug_a" class="form-control" placeholder="e.g. Warfarin" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label form-label-required">Drug B</label>
                        <input type="text" name="drug_b" class="form-control" placeholder="e.g. Aspirin" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label form-label-required">Severity</label>
                        <select name="severity" class="form-control" required>
                            <option value="">Select…</option>
                            <option value="mild">Mild</option>
                            <option value="moderate">Moderate</option>
                            <option value="severe">Severe</option>
                            <option value="contraindicated">Contraindicated</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Action Required</label>
                        <input type="text" name="action_required" class="form-control" placeholder="e.g. Monitor INR closely">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Describe the interaction and its clinical significance…"></textarea>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" onclick="opCloseModal('add-drug-modal')" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-danger"><i data-lucide="plus"></i> Add Rule</button>
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
