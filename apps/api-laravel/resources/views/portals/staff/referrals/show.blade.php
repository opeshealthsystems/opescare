<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Referral Detail - OpesCare</title>
    <style>
        body { font-family: sans-serif; margin: 0; background: #f4f6f9; color: #1a1a2e; }
        header { background: #1a1a2e; color: #fff; padding: 1rem 2rem; }
        header h1 { font-size: 1.2rem; margin: 0; }
        main { padding: 2rem; max-width: 860px; margin: 0 auto; }
        .card { background: #fff; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 4px rgba(0,0,0,.08); margin-bottom: 1.5rem; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: 600; }
        .badge-draft      { background: #e2e8f0; color: #475569; }
        .badge-sent       { background: #dbeafe; color: #1d4ed8; }
        .badge-accepted   { background: #dcfce7; color: #15803d; }
        .badge-rejected   { background: #fee2e2; color: #dc2626; }
        .badge-completed  { background: #ede9fe; color: #7c3aed; }
        .badge-cancelled  { background: #fef3c7; color: #b45309; }
        .badge-expired    { background: #f1f5f9; color: #94a3b8; }
        .field-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem 2rem; }
        .field label { font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; letter-spacing: .05em; }
        .field p { margin: 2px 0 0; font-size: 0.95rem; }
        h3 { margin-top: 0; }
        .action-section { display: flex; gap: 1rem; flex-wrap: wrap; }
        form.action-form { display: inline; }
        .btn { padding: 8px 18px; border: none; border-radius: 6px; cursor: pointer; font-size: 0.9rem; }
        .btn-success  { background: #15803d; color: #fff; }
        .btn-danger   { background: #dc2626; color: #fff; }
        .btn-complete { background: #7c3aed; color: #fff; }
        .btn-cancel   { background: #b45309; color: #fff; }
        .btn-send     { background: #1d4ed8; color: #fff; }
        textarea { width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.9rem; }
        .back-link { display: inline-block; margin-bottom: 1rem; color: #1d4ed8; text-decoration: none; }
        .expiry-warn { color: #dc2626; font-weight: 600; }
    </style>
</head>
<body>
<header>
    <h1>OpesCare — Referral Detail</h1>
</header>
<main>
    <a href="{{ route('portals.staff.referrals') }}" class="back-link">← Back to Referrals</a>

    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.2rem">
            <h3>Referral <small style="font-size:.75rem;color:#64748b">{{ $referral->id }}</small></h3>
            <span class="badge badge-{{ $referral->status }}">{{ ucfirst($referral->status) }}</span>
        </div>

        <div class="field-grid">
            <div class="field">
                <label>Patient</label>
                <p>{{ $referral->patient_id }}</p>
            </div>
            <div class="field">
                <label>Urgency</label>
                <p>{{ ucfirst($referral->urgency) }}</p>
            </div>
            <div class="field">
                <label>Referring Facility</label>
                <p>{{ $referral->referringFacility->name ?? $referral->referring_facility_id }}</p>
            </div>
            <div class="field">
                <label>Receiving Facility</label>
                <p>{{ $referral->receivingFacility->name ?? ($referral->receiving_facility_id ?? '—') }}</p>
            </div>
            <div class="field">
                <label>Specialty / Provider</label>
                <p>{{ $referral->receiving_specialty ?? $referral->receiving_provider_name ?? '—' }}</p>
            </div>
            <div class="field">
                <label>Expires At</label>
                <p class="{{ $referral->isExpired() ? 'expiry-warn' : '' }}">
                    {{ $referral->expires_at?->format('Y-m-d H:i') ?? '—' }}
                    @if ($referral->isExpired()) (EXPIRED) @endif
                </p>
            </div>
            @if ($referral->accepted_at)
            <div class="field">
                <label>Accepted At</label>
                <p>{{ $referral->accepted_at->format('Y-m-d H:i') }}</p>
            </div>
            @endif
            @if ($referral->completed_at)
            <div class="field">
                <label>Completed At</label>
                <p>{{ $referral->completed_at->format('Y-m-d H:i') }}</p>
            </div>
            @endif
        </div>

        <div style="margin-top:1.2rem">
            <div class="field">
                <label>Reason</label>
                <p>{{ $referral->reason }}</p>
            </div>
            @if ($referral->clinical_summary)
            <div class="field" style="margin-top:0.8rem">
                <label>Clinical Summary</label>
                <p>{{ $referral->clinical_summary }}</p>
            </div>
            @endif
            @if ($referral->feedback)
            <div class="field" style="margin-top:0.8rem">
                <label>Receiving Facility Feedback</label>
                <p>{{ $referral->feedback }}</p>
            </div>
            @endif
            @if ($referral->rejection_reason)
            <div class="field" style="margin-top:0.8rem">
                <label>Rejection Reason</label>
                <p style="color:#dc2626">{{ $referral->rejection_reason }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Actions --}}
    @if ($referral->status === 'draft')
    <div class="card">
        <h3>Send Referral</h3>
        <form method="post" action="{{ route('portals.staff.referrals.send', $referral->id) }}">
            @csrf
            <button type="submit" class="btn btn-send">Send to Receiving Facility</button>
        </form>
    </div>
    @endif

    @if ($referral->status === 'sent' && !$referral->isExpired())
    <div class="card">
        <h3>Actions — Receiving Facility</h3>
        <div class="action-section">
            <form method="post" action="{{ route('portals.staff.referrals.accept', $referral->id) }}">
                @csrf
                <input type="hidden" name="accepted_by_id" value="{{ auth()->id() ?? '00000000-0000-0000-0000-000000000000' }}">
                <button type="submit" class="btn btn-success">Accept Referral</button>
            </form>

            <form method="post" action="{{ route('portals.staff.referrals.reject', $referral->id) }}" style="display:flex;flex-direction:column;gap:6px;min-width:280px">
                @csrf
                <textarea name="reason" rows="2" placeholder="Rejection reason (required)"></textarea>
                <button type="submit" class="btn btn-danger">Reject Referral</button>
            </form>
        </div>
    </div>
    @endif

    @if ($referral->status === 'accepted')
    <div class="card">
        <h3>Complete Referral</h3>
        <form method="post" action="{{ route('portals.staff.referrals.complete', $referral->id) }}" style="display:flex;flex-direction:column;gap:8px;max-width:400px">
            @csrf
            <textarea name="feedback" rows="3" placeholder="Feedback for referring provider (optional)"></textarea>
            <button type="submit" class="btn btn-complete">Mark as Completed</button>
        </form>
    </div>
    @endif

    @if (!in_array($referral->status, ['completed','cancelled','expired']))
    <div class="card">
        <h3>Cancel Referral</h3>
        <form method="post" action="{{ route('portals.staff.referrals.cancel', $referral->id) }}" style="display:flex;flex-direction:column;gap:8px;max-width:400px">
            @csrf
            <textarea name="reason" rows="2" placeholder="Cancellation reason (required)"></textarea>
            <button type="submit" class="btn btn-cancel">Cancel Referral</button>
        </form>
    </div>
    @endif
</main>
</body>
</html>
