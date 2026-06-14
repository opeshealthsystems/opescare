@extends('layouts.portal')
@section('title', 'Minor-to-Adult Transitions')
@section('sidebar') @include('portals.admin.control_center._sidebar') @endsection

@section('content')

<div class="breadcrumb">
    <a href="{{ route('portals.admin.legal') }}">Legal Documents</a>
    <i data-lucide="chevron-right"></i>
    <span>Minor Transitions</span>
</div>

<div class="page-head">
    <h2>Minor-to-adult transitions</h2>
</div>
<p class="td-muted mb-6">Patients approaching 18 — consent and account transition reviews.</p>

<div class="panel">
    <div class="panel-body panel-body--flush">
        <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>Patient</th><th>Date of Birth</th><th>Turns 18</th><th>Days Until 18</th><th>Status</th></tr>
            </thead>
            <tbody>
                @forelse($transitions as $t)
                    @php
                        $days = $t->daysUntil18();
                        $daysBadge = $days <= 30 ? 'badge-danger' : ($days <= 90 ? 'badge-warning' : 'badge-success');
                    @endphp
                    <tr>
                        <td data-label="Patient" class="td-strong">
                            {{ $t->patient?->first_name }} {{ $t->patient?->last_name }}
                            <div class="code-muted">{{ $t->patient?->health_id }}</div>
                        </td>
                        <td data-label="Date of Birth">{{ $t->date_of_birth->format('d M Y') }}</td>
                        <td data-label="Turns 18" class="td-strong">{{ $t->turns_18_on->format('d M Y') }}</td>
                        <td data-label="Days Until 18">
                            <span class="badge {{ $daysBadge }} badge-sm">
                                @if($days < 0) Overdue @elseif($days === 0) Today @else {{ $days }} days @endif
                            </span>
                        </td>
                        <td data-label="Status">
                            <span class="badge badge--{{ $t->statusColor() }} badge-sm">{{ ucfirst($t->status) }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="td-muted empty-cell">No minor transitions pending.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
<div class="mt-6">{{ $transitions->links() }}</div>

@endsection
