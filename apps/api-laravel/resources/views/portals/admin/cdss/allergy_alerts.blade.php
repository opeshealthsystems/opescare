@extends('layouts.portal')
@section('title', 'Allergy Alert Rules')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'CDSS')
@section('content')

<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;">
    <div>
        <a href="{{ route('portals.admin.cdss.index') }}" style="font-size:.82rem;color:var(--p-text-muted);display:inline-flex;align-items:center;gap:.3rem;margin-bottom:.4rem;">
            <i data-lucide="arrow-left" style="width:13px;height:13px;"></i> CDSS Rules
        </a>
        <h1 class="page-title">Allergy Alert Rules</h1>
    </div>
    <button onclick="document.getElementById('add-allergy-modal').style.display='flex'" class="btn btn-warning">
        <i data-lucide="plus"></i> Add Rule
    </button>
</div>

@if(session('success'))<div class="auth-alert auth-alert-success" style="margin-bottom:1rem;"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

<div style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.3);border-radius:var(--p-radius);padding:.875rem 1.25rem;margin-bottom:1.25rem;display:flex;align-items:flex-start;gap:.75rem;">
    <i data-lucide="alert-triangle" style="width:16px;height:16px;color:var(--p-warning);flex-shrink:0;margin-top:.15rem;"></i>
    <div style="font-size:.85rem;">These rules affect clinical decision support. Review carefully before adding.</div>
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
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rules as $rule)
                @php $sBadge=match(strtolower($rule->severity??'')){'mild'=>'badge-primary','moderate'=>'badge-warning','severe'=>'badge-danger',default=>'badge-neutral'}; @endphp
                <tr>
                    <td style="font-weight:600;">{{ $rule->drug_name }}</td>
                    <td>{{ $rule->allergen_class }}</td>
                    <td><span class="badge {{ $sBadge }}">{{ ucfirst($rule->severity) }}</span></td>
                    <td style="color:var(--p-text-muted);font-size:.85rem;">{{ $rule->reaction_type }}</td>
                    <td style="font-size:.82rem;color:var(--p-text-muted);">{{ $rule->created_at->format('d M Y') }}</td>
                    <td style="text-align:right;">
                        <form action="{{ route('portals.admin.cdss.destroy-allergy', $rule->id) }}" method="POST" onsubmit="return confirm('Delete this allergy alert rule?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-xs"><i data-lucide="trash-2"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--p-text-muted);">No allergy alert rules found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(isset($rules) && $rules->hasPages())
    <div style="padding:.75rem 1.25rem;">{{ $rules->links() }}</div>
    @endif
</div>

{{-- Add Allergy Modal --}}
<div id="add-allergy-modal" style="display:none;position:fixed;inset:0;z-index:60;align-items:center;justify-content:center;background:rgba(15,23,42,.7);backdrop-filter:blur(4px);padding:1rem;" role="dialog">
    <div style="background:var(--p-surface);border:1px solid var(--p-border);border-radius:var(--p-radius-xl);width:100%;max-width:540px;overflow:hidden;box-shadow:var(--p-shadow-lg);">
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--p-border);display:flex;justify-content:space-between;align-items:center;">
            <h3 style="margin:0;font-size:1rem;font-weight:700;display:flex;align-items:center;gap:.5rem;">
                <i data-lucide="shield-alert" style="width:16px;height:16px;color:var(--p-warning);"></i> Add Allergy Alert Rule
            </h3>
            <button onclick="document.getElementById('add-allergy-modal').style.display='none'" class="topbar-icon-btn"><i data-lucide="x"></i></button>
        </div>
        <form action="{{ route('portals.admin.cdss.store-allergy') }}" method="POST">
            @csrf
            <div style="padding:1.5rem;display:flex;flex-direction:column;gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Drug Name <span style="color:var(--p-danger);">*</span></label>
                    <input type="text" name="drug_name" class="form-control" placeholder="e.g. Amoxicillin" required>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Allergen Class <span style="color:var(--p-danger);">*</span></label>
                        <input type="text" name="allergen_class" class="form-control" placeholder="e.g. Penicillin" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Severity <span style="color:var(--p-danger);">*</span></label>
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
            <div style="padding:1rem 1.5rem;border-top:1px solid var(--p-border);display:flex;gap:.75rem;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('add-allergy-modal').style.display='none'" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-warning"><i data-lucide="plus"></i> Add Rule</button>
            </div>
        </form>
    </div>
</div>
@endsection
@section('scripts')
<script>document.addEventListener('keydown',e=>{if(e.key==='Escape'){document.querySelectorAll('[id$="-modal"]').forEach(m=>m.style.display='none');}});</script>
@endsection
