@extends('layouts.portal')
@section('title', 'Legal Documents')
@section('sidebar') @include('portals.admin.control_center._sidebar') @endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <h1 class="portal-page-title">
                <i data-lucide="scale" style="width:22px;height:22px;vertical-align:middle;margin-right:8px;color:#7c3aed;"></i>
                Legal Documents
            </h1>
            <p class="portal-page-subtitle">Terms, policies, consent forms, and partner agreements</p>
        </div>
        <button onclick="document.getElementById('newDocModal').style.display='flex'" class="btn btn--primary btn--sm">
            <i data-lucide="plus" style="width:14px;height:14px;"></i> New Document
        </button>
    </div>

    {{-- KPI Strip --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:20px;">
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#ede9fe;"><i data-lucide="file-text" style="color:#7c3aed;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value">{{ $stats['total_documents'] }}</div><div class="stat-card__label">Documents</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#eff6ff;"><i data-lucide="layers" style="color:#2563eb;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value">{{ $stats['total_versions'] }}</div><div class="stat-card__label">Versions</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#f0fdf4;"><i data-lucide="check-circle" style="color:#16a34a;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value">{{ number_format($stats['user_acceptances']) }}</div><div class="stat-card__label">User Acceptances</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-card__icon" style="background:#fff7ed;"><i data-lucide="handshake" style="color:#d97706;"></i></div>
            <div class="stat-card__body"><div class="stat-card__value">{{ number_format($stats['partner_acceptances']) }}</div><div class="stat-card__label">Partner Agreements</div></div>
        </div>
    </div>

    <div class="portal-card">
        <div class="portal-card__header">
            <h2 class="portal-card__title">All Legal Documents</h2>
            <div style="display:flex;gap:6px;">
                <a href="{{ route('portals.admin.legal.closures') }}" class="btn btn--outline btn--sm">Account Closures</a>
                <a href="{{ route('portals.admin.legal.complaints') }}" class="btn btn--outline btn--sm">Privacy Complaints</a>
                <a href="{{ route('portals.admin.legal.minor_transitions') }}" class="btn btn--outline btn--sm">Minor Transitions</a>
                <a href="{{ route('public.legal') }}" target="_blank" class="btn btn--outline btn--sm">
                    <i data-lucide="external-link" style="width:12px;height:12px;"></i> Public View
                </a>
            </div>
        </div>
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table">
                <thead>
                    <tr><th>Title</th><th>Type</th><th>Language</th><th>Current Version</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                        @php $ver = $doc->versions->first(); @endphp
                        <tr>
                            <td style="font-weight:600;font-size:0.88rem;">{{ $doc->title }}</td>
                            <td>
                                <span class="badge badge--info" style="font-size:0.72rem;">
                                    {{ str_replace('_', ' ', ucfirst($doc->document_type)) }}
                                </span>
                            </td>
                            <td style="font-size:0.82rem;text-transform:uppercase;">{{ $doc->language }}</td>
                            <td style="font-size:0.82rem;">
                                @if($ver)
                                    <span style="font-family:monospace;color:#7c3aed;">v{{ $ver->version }}</span>
                                    @if($ver->requires_reacceptance)
                                        <span class="badge badge--warning" style="font-size:0.68rem;margin-left:4px;">Re-accept req.</span>
                                    @endif
                                @else
                                    <span style="color:#9ca3af;font-size:0.8rem;">No version yet</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge--{{ $doc->is_active ? 'success' : 'default' }}" style="font-size:0.72rem;">
                                    {{ $doc->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td style="text-align:right;">
                                <a href="{{ route('portals.admin.legal.show', $doc) }}" class="btn btn--outline btn--sm">Manage</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center;padding:40px;color:#9ca3af;">
                            No legal documents yet. Add your Terms, Privacy Policy, and Consent Policy to get started.
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- New Document Modal --}}
<div id="newDocModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;"
     onclick="if(event.target===this)this.style.display='none'">
    <div style="background:#fff;border-radius:12px;padding:28px;width:90%;max-width:500px;">
        <h3 style="margin:0 0 20px;font-size:1rem;font-weight:700;">New Legal Document</h3>
        <form method="POST" action="{{ route('portals.admin.legal.store') }}">
            @csrf
            <div style="margin-bottom:12px;">
                <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Slug *</label>
                <input type="text" name="slug" class="portal-input" placeholder="terms-of-use" required
                       style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.88rem;">
            </div>
            <div style="margin-bottom:12px;">
                <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Title *</label>
                <input type="text" name="title" class="portal-input" placeholder="Terms of Use" required
                       style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.88rem;">
            </div>
            <div style="margin-bottom:12px;">
                <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Type *</label>
                <select name="document_type" required style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.88rem;">
                    <option value="terms">Terms of Use</option>
                    <option value="privacy">Privacy Policy</option>
                    <option value="consent">Patient Consent Policy</option>
                    <option value="dpa">Data Processing Agreement</option>
                    <option value="facility_agreement">Facility Agreement</option>
                    <option value="api_terms">API / Developer Terms</option>
                </select>
            </div>
            <div style="display:flex;gap:10px;margin-top:20px;">
                <button type="submit" class="btn btn--primary" style="flex:1;">Create Document</button>
                <button type="button" onclick="document.getElementById('newDocModal').style.display='none'"
                        class="btn btn--outline" style="flex:1;">Cancel</button>
            </div>
        </form>
    </div>
</div>

@endsection
