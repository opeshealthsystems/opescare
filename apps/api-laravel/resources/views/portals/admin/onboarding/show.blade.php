@extends('layouts.portal')
@section('title', $facility->name . ' — Onboarding')
@section('sidebar') @include('portals.admin.control_center._sidebar') @endsection

@section('content')
<div class="portal-content">

    <div class="portal-page-header">
        <div>
            <a href="{{ route('portals.admin.onboarding') }}" style="font-size:0.83rem;color:#6b7280;text-decoration:none;">← Facility Onboarding</a>
            <h1 class="portal-page-title" style="margin-top:4px;">{{ $facility->name }}</h1>
            <p class="portal-page-subtitle" style="display:flex;align-items:center;gap:8px;">
                @php
                    $statusColor = match($readiness->status) {
                        'approved' => 'success', 'ready_for_approval' => 'info', default => 'warning',
                    };
                @endphp
                <span class="badge badge--{{ $statusColor }}">{{ str_replace('_', ' ', ucfirst($readiness->status)) }}</span>
                <span style="font-size:0.8rem;color:#6b7280;">{{ $completedCount }}/{{ $totalCount }} items completed</span>
            </p>
        </div>
        @if($readiness->status === 'ready_for_approval' && !$readiness->can_go_live)
        <button onclick="document.getElementById('approveModal').style.display='flex'" class="btn btn--primary btn--sm">
            <i data-lucide="check-circle" style="width:14px;height:14px;"></i> Approve Go-Live
        </button>
        @elseif($readiness->status === 'approved')
        <span class="badge badge--success" style="font-size:0.85rem;padding:8px 16px;">
            <i data-lucide="check-circle-2" style="width:14px;height:14px;vertical-align:middle;margin-right:4px;"></i>
            Go-Live Approved
        </span>
        @endif
    </div>

    @if(session('success'))
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 16px;margin-bottom:16px;color:#166534;font-size:0.88rem;">
        ✓ {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 16px;margin-bottom:16px;color:#991b1b;font-size:0.88rem;">
        ✗ {{ session('error') }}
    </div>
    @endif

    {{-- Progress bar --}}
    @php $pct = $totalCount > 0 ? round($completedCount / $totalCount * 100) : 0; @endphp
    <div class="portal-card" style="margin-bottom:16px;">
        <div class="portal-card__body" style="padding:16px 20px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                <span style="font-size:0.88rem;font-weight:600;color:#374151;">Onboarding Progress</span>
                <span style="font-size:0.88rem;font-weight:700;color:{{ $pct === 100 ? '#16a34a' : '#7c3aed' }};">{{ $pct }}%</span>
            </div>
            <div style="background:#e5e7eb;border-radius:99px;height:10px;">
                <div style="width:{{ $pct }}%;background:{{ $pct === 100 ? '#16a34a' : '#7c3aed' }};border-radius:99px;height:10px;transition:width .4s;"></div>
            </div>
            @if(!empty($missingItems))
            <p style="margin:8px 0 0;font-size:0.8rem;color:#dc2626;">
                Remaining: {{ implode(', ', array_values($missingItems)) }}
            </p>
            @endif
        </div>
    </div>

    {{-- Checklist --}}
    <div class="portal-card">
        <div class="portal-card__header">
            <h2 class="portal-card__title">Go-Live Checklist</h2>
        </div>
        <div class="portal-card__body" style="padding:0;">
            <table class="portal-table">
                <thead>
                    <tr><th style="width:60px;">Done</th><th>Checklist Item</th><th style="text-align:right;">Action</th></tr>
                </thead>
                <tbody>
                    @foreach($labels as $key => $label)
                        @php $done = $checklist[$key] ?? false; @endphp
                        <tr style="{{ $done ? 'background:#f9fff9;' : '' }}">
                            <td style="text-align:center;font-size:1.1rem;">
                                {{ $done ? '✅' : '⬜' }}
                            </td>
                            <td style="font-size:0.88rem;{{ $done ? 'color:#6b7280;text-decoration:line-through;' : 'font-weight:600;' }}">
                                {{ $label }}
                            </td>
                            <td style="text-align:right;">
                                @if($readiness->status !== 'approved')
                                <form method="POST" action="{{ route('portals.admin.onboarding.mark', $facility) }}" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="item" value="{{ $key }}">
                                    <input type="hidden" name="complete" value="{{ $done ? '0' : '1' }}">
                                    <button type="submit" class="btn btn--outline btn--sm" style="font-size:0.75rem;padding:4px 10px;">
                                        {{ $done ? 'Mark Incomplete' : 'Mark Complete' }}
                                    </button>
                                </form>
                                @else
                                <span style="font-size:0.75rem;color:#9ca3af;">Locked</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Approval info --}}
    @if($readiness->approved_at)
    <div class="portal-card" style="margin-top:16px;border-color:#bbf7d0;">
        <div class="portal-card__body" style="padding:16px 20px;">
            <h3 style="margin:0 0 8px;font-size:0.9rem;font-weight:700;color:#166534;">
                <i data-lucide="check-circle-2" style="width:14px;height:14px;vertical-align:middle;margin-right:6px;"></i>
                Go-Live Approved
            </h3>
            <div style="font-size:0.83rem;color:#374151;">
                <strong>Approved on:</strong> {{ $readiness->approved_at->format('d F Y H:i') }}<br>
                @if($readiness->approval_note)
                <strong>Note:</strong> {{ $readiness->approval_note }}
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- Risks --}}
    @if(!empty($risks))
    <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:16px 20px;margin-top:16px;font-size:0.85rem;color:#92400e;">
        <strong>⚠ Risks:</strong>
        <ul style="margin:6px 0 0;padding-left:18px;">
            @foreach($risks as $risk)
            <li>{{ $risk }}</li>
            @endforeach
        </ul>
    </div>
    @endif

</div>

{{-- Approve Go-Live Modal --}}
@if($readiness->status !== 'approved')
<div id="approveModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;"
     onclick="if(event.target===this)this.style.display='none'">
    <div style="background:#fff;border-radius:12px;padding:28px;width:90%;max-width:500px;">
        <h3 style="margin:0 0 8px;font-size:1rem;font-weight:700;color:#0F4C81;">Approve Go-Live</h3>
        <p style="font-size:0.83rem;color:#6b7280;margin:0 0 16px;">
            Approving go-live for <strong>{{ $facility->name }}</strong> marks this facility as ready for live patient operations.
            Ensure all checklist items are completed before approving.
        </p>
        <form method="POST" action="{{ route('portals.admin.onboarding.approve', $facility) }}">
            @csrf
            <div style="margin-bottom:12px;">
                <label style="display:block;font-size:0.82rem;font-weight:600;margin-bottom:4px;">Approval Note *</label>
                <textarea name="approval_note" rows="4" required
                          placeholder="Describe the approval basis, any conditions, and the approving authority..."
                          style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.85rem;resize:vertical;"></textarea>
            </div>
            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn btn--primary" style="flex:1;">Confirm Approval</button>
                <button type="button" onclick="document.getElementById('approveModal').style.display='none'"
                        class="btn btn--outline" style="flex:1;">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection
