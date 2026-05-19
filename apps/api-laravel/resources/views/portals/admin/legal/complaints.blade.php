@extends('layouts.portal')
@section('title', 'Privacy Complaints')
@section('sidebar') @include('portals.admin.control_center._sidebar') @endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <a href="{{ route('portals.admin.legal') }}" style="font-size:0.83rem;color:#6b7280;text-decoration:none;">← Legal Documents</a>
            <h1 class="portal-page-title" style="margin-top:4px;">Privacy Complaints</h1>
            <p class="portal-page-subtitle">NDPR/GDPR Article 77 complaint log</p>
        </div>
    </div>

    <div class="portal-card">
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table">
                <thead>
                    <tr><th>Complainant</th><th>Type</th><th>Description</th><th>Status</th><th>Received</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($complaints as $c)
                        <tr>
                            <td style="font-weight:600;font-size:0.88rem;">
                                {{ $c->complainant_name ?: ($c->patient?->first_name . ' ' . $c->patient?->last_name) ?: '—' }}
                                @if($c->complainant_email)
                                    <div style="font-size:0.72rem;color:#6b7280;">{{ $c->complainant_email }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge--warning" style="font-size:0.72rem;">
                                    {{ ucwords(str_replace('_', ' ', $c->complaint_type)) }}
                                </span>
                            </td>
                            <td style="font-size:0.82rem;max-width:220px;">{{ \Illuminate\Support\Str::limit($c->description, 80) }}</td>
                            <td>
                                <span class="badge badge--{{ $c->statusColor() }}" style="font-size:0.72rem;">{{ ucfirst(str_replace('_', ' ', $c->status)) }}</span>
                            </td>
                            <td style="font-size:0.79rem;color:#6b7280;">{{ $c->created_at->format('d M Y') }}</td>
                            <td>
                                @if($c->status === 'open' || $c->status === 'under_review')
                                <form method="POST" action="{{ route('portals.admin.legal.complaints.resolve', $c) }}"
                                      onsubmit="return confirm('Mark as resolved?')">
                                    @csrf
                                    <input type="hidden" name="resolution" value="Reviewed and resolved by OpesCare compliance team.">
                                    <button type="submit" class="btn btn--success btn--sm">Resolve</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center;padding:40px;color:#9ca3af;">No privacy complaints on file.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {{ $complaints->links() }}

</div>
@endsection
