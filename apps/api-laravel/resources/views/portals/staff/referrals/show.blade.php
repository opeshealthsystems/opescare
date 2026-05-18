@extends('layouts.portal')

@section('title', 'Referral Detail — OpesCare Staff Portal')

@section('breadcrumb_home', 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Referral Detail')

@section('sidebar_role_badge')
    <div class="sidebar-role-badge">
        <i data-lucide="stethoscope" style="width:0.75rem;height:0.75rem;display:inline;vertical-align:middle;margin-right:4px;"></i>
        Clinical Staff
    </div>
@endsection

@section('sidebar_nav')
    <div class="sidebar-section-label">Overview</div>
    <a href="{{ route('portals.staff') }}" class="sidebar-link"><i data-lucide="layout-dashboard"></i> Dashboard</a>
    <div class="sidebar-section-label" style="margin-top:var(--p-space-4);">Clinical</div>
    <a href="{{ route('portals.staff.appointments') }}" class="sidebar-link"><i data-lucide="calendar-check-2"></i> Appointments</a>
    <a href="{{ route('portals.staff.queue') }}" class="sidebar-link"><i data-lucide="list-ordered"></i> Patient Queue</a>
    <a href="{{ route('portals.staff.immunizations') }}" class="sidebar-link"><i data-lucide="syringe"></i> Immunizations</a>
    <a href="{{ route('portals.staff.referrals') }}" class="sidebar-link active"><i data-lucide="send"></i> Referrals</a>
    <div class="sidebar-section-label" style="margin-top:var(--p-space-4);">Operations</div>
    <a href="{{ route('portals.staff.billing') }}" class="sidebar-link"><i data-lucide="receipt"></i> Billing</a>
    <a href="{{ route('portals.staff.support') }}" class="sidebar-link"><i data-lucide="headset"></i> Support</a>
@endsection

@section('sidebar_user_role', 'Clinical Staff')

@section('content')

<div class="page-header">
    <div>
        <a href="{{ route('portals.staff.referrals') }}" class="btn btn-ghost btn-sm" style="margin-bottom:var(--p-space-3);">
            <i data-lucide="arrow-left"></i> Back to Referrals
        </a>
        <h1 class="page-title">Referral Detail</h1>
        <p class="page-subtitle">Referral ID: <span class="font-mono">{{ $referral->id }}</span></p>
    </div>
    <div class="page-actions">
        @php
            $stCls = match($referral->status) {
                'accepted'  => 'badge-success',
                'completed' => 'badge-teal',
                'sent'      => 'badge-primary',
                'rejected'  => 'badge-danger',
                'cancelled' => 'badge-neutral',
                'expired'   => 'badge-neutral',
                default     => 'badge-warning',
            };
        @endphp
        <span class="badge {{ $stCls }}" style="font-size:0.9rem;padding:0.4rem 1rem;">{{ ucfirst($referral->status) }}</span>
    </div>
</div>

<!-- Referral Details -->
<div class="grid-main-side">

    <div style="display:flex;flex-direction:column;gap:var(--p-space-5);">

        <!-- Core Details -->
        <div class="panel">
            <div class="panel-header">
                <h2 class="panel-title"><i data-lucide="file-text"></i> Referral Information</h2>
            </div>
            <div class="panel-body">
                <div class="form-row" style="margin-bottom:var(--p-space-5);">
                    <div>
                        <div class="form-label">Patient ID</div>
                        <div class="font-mono" style="color:var(--p-primary);font-weight:700;">{{ $referral->patient_id }}</div>
                    </div>
                    <div>
                        <div class="form-label">Priority</div>
                        @php
                            $prCls = match($referral->priority ?? 'routine') {
                                'emergency' => 'badge-critical',
                                'urgent'    => 'badge-danger',
                                default     => 'badge-neutral',
                            };
                        @endphp
                        <span class="badge {{ $prCls }}">{{ ucfirst($referral->priority ?? 'routine') }}</span>
                    </div>
                </div>
                <div class="form-row" style="margin-bottom:var(--p-space-5);">
                    <div>
                        <div class="form-label">Referring Facility</div>
                        <div style="font-weight:600;color:var(--p-text);">{{ $referral->referring_facility_id ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="form-label">Receiving Facility</div>
                        <div style="font-weight:600;color:var(--p-text);">{{ $referral->receiving_facility_id ?? '—' }}</div>
                    </div>
                </div>
                <div class="form-row" style="margin-bottom:var(--p-space-5);">
                    <div>
                        <div class="form-label">Specialty</div>
                        <div style="font-weight:600;">{{ $referral->specialty ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="form-label">Expires At</div>
                        <div style="{{ $referral->expires_at && \Carbon\Carbon::parse($referral->expires_at)->isPast() ? 'color:var(--p-danger);font-weight:700;' : 'font-weight:600;' }}">
                            {{ $referral->expires_at ? \Carbon\Carbon::parse($referral->expires_at)->format('d M Y H:i') : '—' }}
                            @if($referral->expires_at && \Carbon\Carbon::parse($referral->expires_at)->isPast())
                                <span class="badge badge-danger">Expired</span>
                            @endif
                        </div>
                    </div>
                </div>
                @if($referral->clinical_summary)
                <div style="margin-bottom:var(--p-space-4);">
                    <div class="form-label">Clinical Summary</div>
                    <div style="margin-top:var(--p-space-2);padding:var(--p-space-4);background:var(--p-surface-2);border:1px solid var(--p-border);border-radius:var(--p-radius);font-size:0.9rem;color:var(--p-text-2);line-height:1.6;">
                        {{ $referral->clinical_summary }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Actions -->
        @if(in_array($referral->status, ['draft','sent','accepted']))
        <div class="panel">
            <div class="panel-header">
                <h2 class="panel-title"><i data-lucide="zap"></i> Actions</h2>
            </div>
            <div class="panel-body">
                <div style="display:flex;gap:var(--p-space-3);flex-wrap:wrap;">
                    @if($referral->status === 'draft')
                    <form method="POST" action="{{ route('portals.staff.referrals.send', $referral->id) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="send"></i> Send Referral
                        </button>
                    </form>
                    @endif

                    @if($referral->status === 'sent')
                    <form method="POST" action="{{ route('portals.staff.referrals.accept', $referral->id) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-teal">
                            <i data-lucide="check-circle-2"></i> Accept
                        </button>
                    </form>
                    <form method="POST" action="{{ route('portals.staff.referrals.reject', $referral->id) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-secondary">
                            <i data-lucide="x-circle"></i> Reject
                        </button>
                    </form>
                    @endif

                    @if($referral->status === 'accepted')
                    <form method="POST" action="{{ route('portals.staff.referrals.complete', $referral->id) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="check-circle-2"></i> Mark Completed
                        </button>
                    </form>
                    @endif

                    @if(in_array($referral->status, ['draft','sent']))
                    <form method="POST" action="{{ route('portals.staff.referrals.cancel', $referral->id) }}" style="display:inline;"
                          onsubmit="return confirm('Cancel this referral?')">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            <i data-lucide="x"></i> Cancel
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @endif

    </div>

    <!-- Side Info -->
    <div style="display:flex;flex-direction:column;gap:var(--p-space-5);">
        <div class="panel">
            <div class="panel-header">
                <h2 class="panel-title"><i data-lucide="clock"></i> Timeline</h2>
            </div>
            <div class="panel-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-dot"><i data-lucide="plus-circle"></i></div>
                        <div class="timeline-body">
                            <div class="timeline-time">{{ $referral->created_at?->format('d M Y H:i') }}</div>
                            <div class="timeline-title">Referral Created</div>
                            <div class="timeline-desc">Status: Draft</div>
                        </div>
                    </div>
                    @if($referral->status !== 'draft')
                    <div class="timeline-item">
                        <div class="timeline-dot teal"><i data-lucide="send"></i></div>
                        <div class="timeline-body">
                            <div class="timeline-time">{{ $referral->updated_at?->format('d M Y H:i') }}</div>
                            <div class="timeline-title">Status Updated</div>
                            <div class="timeline-desc">{{ ucfirst($referral->status) }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-body">
                <div class="alert alert-warning" role="note">
                    <i data-lucide="shield-alert"></i>
                    <div style="font-size:0.8125rem;">All referral actions are fully audited and logged for clinical governance purposes.</div>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection
