@extends('layouts.portal')
@section('title', 'Drug Interaction Rules')
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
        <h1 class="page-title">Drug Interaction Rules</h1>
    </div>
    <button onclick="document.getElementById('add-drug-modal').style.display='flex'" class="btn btn-danger">
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
                    <th>Drug A</th>
                    <th>Drug B</th>
                    <th>Severity</th>
                    <th>Description</th>
                    <th>Action Required</th>
                    <th>Created</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rules as $rule)
                @php $sBadge=match($rule->severity??''){'mild'=>'badge-primary','moderate'=>'badge-warning','severe'=>'badge-danger','contraindicated'=>'badge-danger',default=>'badge-neutral'}; @endphp
                <tr>
                    <td style="font-weight:600;">{{ $rule->drug_a }}</td>
                    <td style="font-weight:600;">{{ $rule->drug_b }}</td>
                    <td><span class="badge {{ $sBadge }}">{{ ucfirst($rule->severity) }}</span></td>
                    <td style="font-size:.82rem;color:var(--p-text-muted);max-width:220px;">{{ Str::limit($rule->description, 80) }}</td>
                    <td style="font-size:.82rem;color:var(--p-text-muted);max-width:180px;">{{ Str::limit($rule->action_required, 60) }}</td>
                    <td style="font-size:.82rem;color:var(--p-text-muted);">{{ $rule->created_at->format('d M Y') }}</td>
                    <td style="text-align:right;">
                        <form action="{{ route('portals.admin.cdss.destroy-drug', $rule->id) }}" method="POST" onsubmit="return confirm('Delete this drug interaction rule?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-xs"><i data-lucide="trash-2"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--p-text-muted);">No drug interaction rules found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(isset($rules) && $rules->hasPages())
    <div style="padding:.75rem 1.25rem;">{{ $rules->links() }}</div>
    @endif
</div>

{{-- Add Drug Interaction Modal --}}
<div id="add-drug-modal" style="display:none;position:fixed;inset:0;z-index:60;align-items:center;justify-content:center;background:rgba(15,23,42,.7);backdrop-filter:blur(4px);padding:1rem;" role="dialog">
    <div style="background:var(--p-surface);border:1px solid var(--p-border);border-radius:var(--p-radius-xl);width:100%;max-width:620px;overflow:hidden;box-shadow:var(--p-shadow-lg);">
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--p-border);display:flex;justify-content:space-between;align-items:center;">
            <h3 style="margin:0;font-size:1rem;font-weight:700;display:flex;align-items:center;gap:.5rem;">
                <i data-lucide="zap" style="width:16px;height:16px;color:var(--p-danger);"></i> Add Drug Interaction Rule
            </h3>
            <button onclick="document.getElementById('add-drug-modal').style.display='none'" class="topbar-icon-btn"><i data-lucide="x"></i></button>
        </div>
        <form action="{{ route('portals.admin.cdss.store-drug') }}" method="POST">
            @csrf
            <div style="padding:1.5rem;display:flex;flex-direction:column;gap:1rem;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Drug A <span style="color:var(--p-danger);">*</span></label>
                        <input type="text" name="drug_a" class="form-control" placeholder="e.g. Warfarin" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Drug B <span style="color:var(--p-danger);">*</span></label>
                        <input type="text" name="drug_b" class="form-control" placeholder="e.g. Aspirin" required>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 2fr;gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Severity <span style="color:var(--p-danger);">*</span></label>
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
            <div style="padding:1rem 1.5rem;border-top:1px solid var(--p-border);display:flex;gap:.75rem;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('add-drug-modal').style.display='none'" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-danger"><i data-lucide="plus"></i> Add Rule</button>
            </div>
        </form>
    </div>
</div>
@endsection
@section('scripts')
<script>document.addEventListener('keydown',e=>{if(e.key==='Escape'){document.querySelectorAll('[id$="-modal"]').forEach(m=>m.style.display='none');}});</script>
@endsection
