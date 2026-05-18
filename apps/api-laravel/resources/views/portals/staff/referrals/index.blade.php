<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Referral Network - OpesCare Staff Portal</title>
    <style>
        body { font-family: sans-serif; margin: 0; background: #f4f6f9; color: #1a1a2e; }
        header { background: #1a1a2e; color: #fff; padding: 1rem 2rem; display: flex; align-items: center; gap: 1rem; }
        header h1 { font-size: 1.2rem; margin: 0; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; }
        .badge-draft      { background: #e2e8f0; color: #475569; }
        .badge-sent       { background: #dbeafe; color: #1d4ed8; }
        .badge-accepted   { background: #dcfce7; color: #15803d; }
        .badge-rejected   { background: #fee2e2; color: #dc2626; }
        .badge-completed  { background: #ede9fe; color: #7c3aed; }
        .badge-cancelled  { background: #fef3c7; color: #b45309; }
        .badge-expired    { background: #f1f5f9; color: #94a3b8; }
        .badge-urgent     { background: #fef3c7; color: #b45309; }
        .badge-emergency  { background: #fee2e2; color: #dc2626; }
        main { padding: 2rem; max-width: 1100px; margin: 0 auto; }
        .card { background: #fff; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 4px rgba(0,0,0,.08); margin-bottom: 1.5rem; }
        .filter-row { display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end; }
        .filter-row label { display: flex; flex-direction: column; font-size: 0.85rem; gap: 4px; }
        .filter-row input, .filter-row select { padding: 6px 10px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.9rem; }
        .filter-row button { padding: 7px 18px; background: #1a1a2e; color: #fff; border: none; border-radius: 6px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        th { text-align: left; padding: 10px 12px; background: #f8fafc; border-bottom: 2px solid #e2e8f0; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; }
        td { padding: 10px 12px; border-bottom: 1px solid #f1f5f9; }
        tr:hover td { background: #f8fafc; }
        .action-link { color: #1d4ed8; text-decoration: none; font-size: 0.8rem; margin-right: 8px; }
        .action-link:hover { text-decoration: underline; }
        .empty { text-align: center; padding: 3rem; color: #94a3b8; }
        .page-title { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
        .btn-primary { display: inline-block; padding: 8px 18px; background: #1a1a2e; color: #fff; border-radius: 6px; text-decoration: none; font-size: 0.9rem; }
        .urgency-emergency { color: #dc2626; font-weight: 700; }
        .urgency-urgent    { color: #b45309; font-weight: 600; }
    </style>
</head>
<body>
<header>
    <h1>OpesCare — Referral Network</h1>
</header>
<main>
    <div class="page-title">
        <h2 style="margin:0">Referrals</h2>
        <a href="{{ route('portals.staff.referrals.create') }}" class="btn-primary">+ New Referral</a>
    </div>

    <div class="card">
        <form method="get" class="filter-row">
            <label>
                Patient ID
                <input name="patient_id" value="{{ request('patient_id') }}" placeholder="UUID">
            </label>
            <label>
                Referring Facility ID
                <input name="referring_facility_id" value="{{ request('referring_facility_id') }}" placeholder="UUID">
            </label>
            <label>
                Receiving Facility ID
                <input name="receiving_facility_id" value="{{ request('receiving_facility_id') }}" placeholder="UUID">
            </label>
            <label>
                Status
                <select name="status">
                    <option value="">All</option>
                    @foreach(['draft','sent','accepted','rejected','completed','cancelled','expired'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit">Filter</button>
        </form>
    </div>

    <div class="card">
        @if ($referrals->isEmpty())
            <div class="empty">No referrals found for the selected filters.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Patient</th>
                        <th>Urgency</th>
                        <th>Specialty / Provider</th>
                        <th>Status</th>
                        <th>Expires</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($referrals as $referral)
                        <tr>
                            <td>{{ $referral->created_at?->format('Y-m-d') }}</td>
                            <td style="font-size:.8rem;color:#64748b">{{ $referral->patient_id }}</td>
                            <td>
                                @if ($referral->urgency === 'emergency')
                                    <span class="badge badge-emergency">Emergency</span>
                                @elseif ($referral->urgency === 'urgent')
                                    <span class="badge badge-urgent">Urgent</span>
                                @else
                                    <span>Routine</span>
                                @endif
                            </td>
                            <td>{{ $referral->receiving_specialty ?? $referral->receiving_provider_name ?? '—' }}</td>
                            <td><span class="badge badge-{{ $referral->status }}">{{ ucfirst($referral->status) }}</span></td>
                            <td style="font-size:.8rem;color:{{ $referral->isExpired() ? '#dc2626' : '#64748b' }}">
                                {{ $referral->expires_at?->format('Y-m-d') ?? '—' }}
                            </td>
                            <td>
                                <a href="{{ route('portals.staff.referrals.show', $referral->id) }}" class="action-link">View</a>
                                @if ($referral->status === 'draft')
                                    <a href="{{ route('portals.staff.referrals.show', $referral->id) }}" class="action-link">Send</a>
                                @endif
                                @if ($referral->status === 'sent')
                                    <a href="{{ route('portals.staff.referrals.show', $referral->id) }}" class="action-link">Accept / Reject</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</main>
</body>
</html>
