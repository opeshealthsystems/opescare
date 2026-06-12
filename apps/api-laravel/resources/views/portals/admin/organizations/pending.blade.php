@extends('layouts.portal')
@section('title', 'Pending Approvals')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Organizations')
@section('content')

<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;">
    <div>
        <a href="{{ route('admin.organizations.index') }}" style="font-size:.82rem;color:var(--p-text-muted);display:inline-flex;align-items:center;gap:.3rem;margin-bottom:.4rem;">
            <i data-lucide="arrow-left" style="width:13px;height:13px;"></i> All Organizations
        </a>
        <h1 class="page-title">Pending Approvals</h1>
        <p class="page-subtitle">Organizations awaiting admin review and approval.</p>
    </div>
</div>

@if(session('success'))<div class="auth-alert auth-alert-success" style="margin-bottom:1rem;"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

@if($organizations->isEmpty())
<div class="panel" style="text-align:center;padding:3rem;">
    <i data-lucide="check-circle" style="width:48px;height:48px;color:#10b981;margin-bottom:1rem;display:block;margin-left:auto;margin-right:auto;"></i>
    <p style="color:var(--p-text-muted);margin:0;">No organizations pending approval.</p>
</div>
@else
<div class="panel">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Region</th>
                    <th>Status</th>
                    <th>Applied</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($organizations as $org)
                @php $tBadge=match($org->type??''){'hospital'=>'badge-primary','clinic'=>'badge-success','pharmacy'=>'badge-warning','lab'=>'badge-neutral',default=>'badge-neutral'}; @endphp
                <tr>
                    <td>
                        <div style="font-weight:600;">{{ $org->name }}</div>
                        @if($org->email)<div style="font-size:.78rem;color:var(--p-text-muted);">{{ $org->email }}</div>@endif
                    </td>
                    <td><span class="badge {{ $tBadge }}">{{ ucfirst($org->type??'—') }}</span></td>
                    <td style="font-size:.85rem;">{{ $org->region ?? '—' }}</td>
                    <td>
                        @if(($org->status??'')==='submitted')<span class="badge badge-primary">Submitted</span>
                        @else<span class="badge badge-warning">Pending</span>@endif
                    </td>
                    <td style="font-size:.82rem;">{{ $org->created_at->format('d M Y') }}</td>
                    <td style="text-align:right;">
                        <div style="display:flex;gap:.35rem;justify-content:flex-end;">
                            <form method="POST" action="{{ route('portals.admin.organizations.approve', $org) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-success btn-xs"><i data-lucide="check"></i> Approve</button>
                            </form>
                            <button onclick="openRejectModal('{{ $org->id }}','{{ addslashes($org->name) }}')" class="btn btn-warning btn-xs">
                                <i data-lucide="x"></i> Reject
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($organizations->hasPages())
    <div style="padding:.75rem 1.25rem;">{{ $organizations->links() }}</div>
    @endif
</div>
@endif

{{-- Reject Modal --}}
<div id="reject-modal" style="display:none;position:fixed;inset:0;z-index:60;align-items:center;justify-content:center;background:rgba(15,23,42,.7);backdrop-filter:blur(4px);padding:1rem;" role="dialog">
    <div style="background:var(--p-surface);border:1px solid var(--p-border);border-radius:var(--p-radius-xl);width:100%;max-width:520px;overflow:hidden;box-shadow:var(--p-shadow-lg);">
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--p-border);display:flex;justify-content:space-between;align-items:center;">
            <h3 style="margin:0;font-size:1rem;font-weight:700;">Reject Organization</h3>
            <button onclick="document.getElementById('reject-modal').style.display='none'" class="topbar-icon-btn"><i data-lucide="x"></i></button>
        </div>
        <form method="POST" id="reject-form" action="">
            @csrf @method('PATCH')
            <div style="padding:1.5rem;">
                <p style="margin-bottom:1rem;">Rejecting: <strong id="reject-org-name"></strong></p>
                <div class="form-group">
                    <label class="form-label">Reason for rejection <span style="color:var(--p-danger);">*</span></label>
                    <textarea name="reason" class="form-control" rows="4" placeholder="Provide a clear reason for rejection…" required></textarea>
                    <div style="font-size:.75rem;color:var(--p-text-muted);margin-top:.25rem;">This reason may be communicated to the applicant.</div>
                </div>
            </div>
            <div style="padding:1rem 1.5rem;border-top:1px solid var(--p-border);display:flex;gap:.75rem;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('reject-modal').style.display='none'" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-warning"><i data-lucide="x-circle"></i> Confirm Rejection</button>
            </div>
        </form>
    </div>
</div>
@endsection
@section('scripts')
<script>
function openRejectModal(id, name) {
    document.getElementById('reject-org-name').textContent = name;
    document.getElementById('reject-form').action = '/admin/organizations/' + id + '/reject';
    document.getElementById('reject-modal').style.display = 'flex';
}
document.addEventListener('keydown',e=>{if(e.key==='Escape'){document.querySelectorAll('[id$="-modal"]').forEach(m=>m.style.display='none');}});
</script>
@endsection
