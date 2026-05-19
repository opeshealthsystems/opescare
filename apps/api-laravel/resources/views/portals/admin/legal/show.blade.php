@extends('layouts.portal')
@section('title', $document->title)
@section('sidebar') @include('portals.admin.control_center._sidebar') @endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <a href="{{ route('portals.admin.legal') }}" style="font-size:0.83rem;color:#6b7280;text-decoration:none;">← Legal Documents</a>
            <h1 class="portal-page-title" style="margin-top:4px;">{{ $document->title }}</h1>
            <p class="portal-page-subtitle">
                <span class="badge badge--info" style="font-size:0.72rem;">{{ str_replace('_', ' ', ucfirst($document->document_type)) }}</span>
                <span style="margin-left:8px;font-size:0.8rem;color:#6b7280;">{{ strtoupper($document->language) }}</span>
            </p>
        </div>
        <button onclick="document.getElementById('publishModal').style.display='flex'" class="btn btn--primary btn--sm">
            <i data-lucide="upload" style="width:14px;height:14px;"></i> Publish New Version
        </button>
    </div>

    {{-- Versions table --}}
    <div class="portal-card">
        <div class="portal-card__header"><h2 class="portal-card__title">Versions</h2></div>
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table">
                <thead>
                    <tr><th>Version</th><th>Status</th><th>Re-accept?</th><th>Published</th><th>Effective</th><th>Change Summary</th></tr>
                </thead>
                <tbody>
                    @forelse($versions as $ver)
                        <tr style="{{ $ver->is_current ? 'background:#f9f7ff;' : '' }}">
                            <td style="font-family:monospace;font-weight:600;color:#7c3aed;">
                                v{{ $ver->version }}
                                @if($ver->is_current)
                                    <span class="badge badge--success" style="font-size:0.68rem;margin-left:4px;">Current</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge--{{ $ver->isEffective() ? 'success' : 'warning' }}" style="font-size:0.72rem;">
                                    {{ $ver->isEffective() ? 'Live' : 'Scheduled' }}
                                </span>
                            </td>
                            <td style="font-size:0.82rem;color:{{ $ver->requires_reacceptance ? '#dc2626' : '#9ca3af' }};">
                                {{ $ver->requires_reacceptance ? 'Yes' : 'No' }}
                            </td>
                            <td style="font-size:0.79rem;color:#6b7280;">
                                {{ $ver->published_at?->format('d M Y H:i') ?? '—' }}
                            </td>
                            <td style="font-size:0.79rem;color:#6b7280;">
                                {{ $ver->effective_at?->format('d M Y') ?? '—' }}
                            </td>
                            <td style="font-size:0.82rem;color:#374151;">{{ $ver->change_summary ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center;padding:30px;color:#9ca3af;">
                            No versions published yet.
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Current version preview --}}
    @php $current = $versions->where('is_current', true)->first(); @endphp
    @if($current)
    <div class="portal-card" style="margin-top:16px;">
        <div class="portal-card__header">
            <h2 class="portal-card__title">Current Content (v{{ $current->version }})</h2>
            <a href="{{ route('public.legal.show', $document->slug) }}" target="_blank"
               class="btn btn--outline btn--sm">
                <i data-lucide="external-link" style="width:12px;height:12px;"></i> Public View
            </a>
        </div>
        <div class="portal-card__body">
            <div style="max-height:400px;overflow-y:auto;border:1px solid #e5e7eb;border-radius:8px;padding:16px;font-size:0.88rem;line-height:1.6;">
                {!! $current->content_html !!}
            </div>
        </div>
    </div>
    @endif

</div>

{{-- Publish New Version Modal --}}
<div id="publishModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:flex-start;justify-content:center;overflow-y:auto;padding:40px 0;"
     onclick="if(event.target===this)this.style.display='none'">
    <div style="background:#fff;border-radius:12px;padding:28px;width:90%;max-width:700px;margin:auto;">
        <h3 style="margin:0 0 20px;font-size:1rem;font-weight:700;">Publish New Version — {{ $document->title }}</h3>
        <form method="POST" action="{{ route('portals.admin.legal.publish_version', $document) }}">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                <div>
                    <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Version *</label>
                    <input type="text" name="version" placeholder="1.0" required
                           style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.88rem;">
                </div>
                <div>
                    <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Effective Date</label>
                    <input type="date" name="effective_at"
                           style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.88rem;">
                </div>
            </div>
            <div style="margin-bottom:12px;">
                <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Change Summary</label>
                <input type="text" name="change_summary" placeholder="What changed in this version?"
                       style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.88rem;">
            </div>
            <div style="margin-bottom:12px;">
                <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Content (HTML) *</label>
                <textarea name="content_html" rows="12" required placeholder="<h1>Terms of Use</h1><p>...</p>"
                          style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.82rem;font-family:monospace;resize:vertical;"></textarea>
            </div>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;">
                <input type="checkbox" id="reaccept" name="requires_reacceptance" value="1">
                <label for="reaccept" style="font-size:0.83rem;cursor:pointer;">
                    Require all users to re-accept this new version
                </label>
            </div>
            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn btn--primary" style="flex:1;">Publish Version</button>
                <button type="button" onclick="document.getElementById('publishModal').style.display='none'"
                        class="btn btn--outline" style="flex:1;">Cancel</button>
            </div>
        </form>
    </div>
</div>

@endsection
