@extends('layouts.portal')
@section('title', 'Allergy Alert Rules')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'CDSS')
@section('content')

<div class="breadcrumb">
    <a href="{{ route('portals.admin.cdss.index') }}">CDSS Rules</a>
    <i data-lucide="chevron-right"></i>
    <span>Allergy Alert Rules</span>
</div>

<div class="page-head">
    <h2>Allergy Alert Rules</h2>
    <div class="page-head__spacer"></div>
    <button onclick="opOpenModal('add-allergy-modal')" class="btn btn-warning"><i data-lucide="plus"></i> Add Rule</button>
</div>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

<div class="tabs mb-6">
    <a href="{{ route('portals.admin.cdss.drug-interactions') }}" class="tab">Drug Interactions</a>
    <a href="{{ route('portals.admin.cdss.allergy-alerts') }}" class="tab active">Allergy Alerts</a>
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
                    <th>Drug Name</th>
                    <th>Allergen Class</th>
                    <th>Severity</th>
                    <th>Reaction Type</th>
                    <th>Created</th>
                    <th class="row-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rules as $rule)
                @php $sBadge=match(strtolower($rule->severity??'')){'mild'=>'badge-primary','moderate'=>'badge-warning','severe'=>'badge-danger',default=>'badge-neutral'}; @endphp
                <tr>
                    <td data-label="Drug Name"><span class="td-strong">{{ $rule->drug_name }}</span></td>
                    <td data-label="Allergen Class">{{ $rule->allergen_class }}</td>
                    <td data-label="Severity"><span class="badge {{ $sBadge }}">{{ ucfirst($rule->severity) }}</span></td>
                    <td data-label="Reaction Type">{{ $rule->reaction_type }}</td>
                    <td data-label="Created">{{ $rule->created_at->format('d M Y') }}</td>
                    <td class="row-actions" data-label="Actions">
                        <button type="button" class="btn btn-danger btn-sm" onclick="opOpenModal('delete-allergy-{{ $rule->id }}')"><i data-lucide="trash-2"></i></button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="td-muted empty-cell">No allergy alert rules found.</td></tr>
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
<div id="delete-allergy-{{ $rule->id }}" class="modal-backdrop mt-6" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="delete-allergy-{{ $rule->id }}-title">
        <h3 class="modal__title" id="delete-allergy-{{ $rule->id }}-title"><i data-lucide="trash-2"></i> Delete allergy rule</h3>
        <form action="{{ route('portals.admin.cdss.destroy-allergy', $rule->id) }}" method="POST">
            @csrf @method('DELETE')
            <div class="modal__body"><p>Delete this allergy alert rule for <strong>{{ $rule->drug_name }}</strong>?</p></div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" onclick="opCloseModal('delete-allergy-{{ $rule->id }}')">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete</button>
            </div>
        </form>
    </div>
</div>
@endforeach

{{-- Add Allergy Modal --}}
<div id="add-allergy-modal" class="modal-backdrop mt-6" hidden>
    <div class="modal modal--md" role="dialog" aria-modal="true" aria-labelledby="add-allergy-title">
        <h3 class="modal__title" id="add-allergy-title"><i data-lucide="shield-alert"></i> Add Allergy Alert Rule</h3>
        <form action="{{ route('portals.admin.cdss.store-allergy') }}" method="POST">
            @csrf
            <div class="modal__body">
                <div class="form-group">
                    <label class="form-label form-label-required">Drug Name</label>
                    <input type="text" name="drug_name" class="form-control" placeholder="e.g. Amoxicillin" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label form-label-required">Allergen Class</label>
                        <input type="text" name="allergen_class" class="form-control" placeholder="e.g. Penicillin" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label form-label-required">Severity</label>
                        <select name="severity" class="form-control" required>
                            <option value="">Select…</option>
                            <option value="mild">Mild</option>
                            <option value="moderate">Moderate</option>
                            <option value="severe">Severe</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Reaction Type</label>
                    <input type="text" name="reaction_type" class="form-control" placeholder="e.g. Anaphylaxis, Rash, Urticaria">
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" onclick="opCloseModal('add-allergy-modal')" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-warning"><i data-lucide="plus"></i> Add Rule</button>
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
