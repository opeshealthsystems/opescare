@extends('layouts.portal')
@section('title', 'Minor-to-Adult Transitions')
@section('sidebar') @include('portals.admin.control_center._sidebar') @endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <a href="{{ route('portals.admin.legal') }}" style="font-size:0.83rem;color:#6b7280;text-decoration:none;">← Legal Documents</a>
            <h1 class="portal-page-title" style="margin-top:4px;">Minor-to-Adult Transitions</h1>
            <p class="portal-page-subtitle">Patients approaching 18 — consent and account transition reviews</p>
        </div>
    </div>

    <div class="portal-card">
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table">
                <thead>
                    <tr><th>Patient</th><th>Date of Birth</th><th>Turns 18</th><th>Days Until 18</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @forelse($transitions as $t)
                        @php $days = $t->daysUntil18(); @endphp
                        <tr>
                            <td style="font-weight:600;font-size:0.88rem;">
                                {{ $t->patient?->first_name }} {{ $t->patient?->last_name }}
                                <div style="font-family:monospace;font-size:0.72rem;color:#9ca3af;">{{ $t->patient?->health_id }}</div>
                            </td>
                            <td style="font-size:0.82rem;">{{ $t->date_of_birth->format('d M Y') }}</td>
                            <td style="font-size:0.82rem;font-weight:600;">{{ $t->turns_18_on->format('d M Y') }}</td>
                            <td style="font-size:0.82rem;font-weight:700;color:{{ $days <= 30 ? '#dc2626' : ($days <= 90 ? '#d97706' : '#16a34a') }};">
                                @if($days < 0) Overdue @elseif($days === 0) Today @else {{ $days }} days @endif
                            </td>
                            <td>
                                <span class="badge badge--{{ $t->statusColor() }}" style="font-size:0.72rem;">{{ ucfirst($t->status) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="text-align:center;padding:40px;color:#9ca3af;">No minor transitions pending.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {{ $transitions->links() }}

</div>
@endsection
