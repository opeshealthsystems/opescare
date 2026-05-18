<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Immunization Records - OpesCare</title>
    <style>
        body { font-family: sans-serif; margin: 0; background: #f4f6f9; color: #1a1a2e; }
        header { background: #1a1a2e; color: #fff; padding: 1rem 2rem; display: flex; align-items: center; gap: 1rem; }
        header h1 { font-size: 1.2rem; margin: 0; }
        main { padding: 2rem; max-width: 1100px; margin: 0 auto; }
        .card { background: #fff; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 4px rgba(0,0,0,.08); margin-bottom: 1.5rem; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; }
        .badge-completed  { background: #dcfce7; color: #15803d; }
        .badge-not_done   { background: #fef3c7; color: #b45309; }
        .badge-historical { background: #e2e8f0; color: #475569; }
        .badge-verified   { background: #dbeafe; color: #1d4ed8; }
        .badge-overdue    { background: #fee2e2; color: #dc2626; }
        .badge-due        { background: #fef3c7; color: #b45309; }
        table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        th { text-align: left; padding: 10px 12px; background: #f8fafc; border-bottom: 2px solid #e2e8f0; font-size: 0.8rem; text-transform: uppercase; letter-spacing: .05em; color: #64748b; }
        td { padding: 10px 12px; border-bottom: 1px solid #f1f5f9; }
        tr:hover td { background: #f8fafc; }
        .filter-row { display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end; }
        .filter-row label { display: flex; flex-direction: column; font-size: 0.85rem; gap: 4px; }
        .filter-row input { padding: 6px 10px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.9rem; }
        .filter-row button { padding: 7px 18px; background: #1a1a2e; color: #fff; border: none; border-radius: 6px; cursor: pointer; }
        .page-title { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
        .btn-primary { display: inline-block; padding: 8px 18px; background: #1a1a2e; color: #fff; border-radius: 6px; text-decoration: none; font-size: 0.9rem; }
        .empty { text-align: center; padding: 3rem; color: #94a3b8; }
        .tabs { display: flex; gap: 0; border-bottom: 2px solid #e2e8f0; margin-bottom: 1rem; }
        .tab { padding: 8px 20px; cursor: pointer; font-size: 0.9rem; color: #64748b; border-bottom: 3px solid transparent; margin-bottom: -2px; }
        .tab.active { color: #1a1a2e; border-bottom-color: #1a1a2e; font-weight: 600; }
    </style>
</head>
<body>
<header>
    <h1>OpesCare — Immunization Records</h1>
</header>
<main>
    <div class="page-title">
        <h2 style="margin:0">Immunizations</h2>
        @if (request()->filled('patient_id'))
            <a href="{{ route('portals.staff.immunizations.record', ['patient_id' => request('patient_id')]) }}" class="btn-primary">+ Record Vaccine</a>
        @endif
    </div>

    <div class="card">
        <form method="get" class="filter-row">
            <label>
                Patient ID *
                <input name="patient_id" value="{{ request('patient_id') }}" placeholder="UUID" required>
            </label>
            <button type="submit">Load Records</button>
        </form>
    </div>

    @if (request()->filled('patient_id'))
        {{-- Immunization history --}}
        <div class="card">
            <div class="tabs">
                <div class="tab active">Vaccine History ({{ $records->count() }})</div>
            </div>

            @if ($records->isEmpty())
                <div class="empty">No immunization records found for this patient.</div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Vaccine</th>
                            <th>Dose</th>
                            <th>Lot #</th>
                            <th>Route / Site</th>
                            <th>Status</th>
                            <th>Source</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $record)
                            <tr>
                                <td>{{ $record->administered_at?->format('Y-m-d') }}</td>
                                <td>
                                    <strong>{{ $record->vaccine_name }}</strong>
                                    <br><small style="color:#94a3b8">{{ $record->vaccine_code }}</small>
                                </td>
                                <td>{{ $record->dose_number ? 'Dose '.$record->dose_number : '—' }}</td>
                                <td style="font-size:.8rem;color:#64748b">{{ $record->lot_number ?? '—' }}</td>
                                <td style="font-size:.85rem">{{ $record->route ?? '—' }} {{ $record->site ? '/ '.$record->site : '' }}</td>
                                <td>
                                    <span class="badge badge-{{ $record->status }}">{{ ucfirst(str_replace('_',' ',$record->status)) }}</span>
                                    @if ($record->verification_status === 'verified')
                                        <span class="badge badge-verified">Verified</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($record->is_historical)
                                        <span class="badge badge-historical">Historical</span>
                                    @else
                                        <span style="font-size:.8rem;color:#94a3b8">Recorded</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Due / overdue schedule --}}
        @if ($schedule->isNotEmpty())
        <div class="card">
            <h3 style="margin-top:0">Upcoming / Overdue Vaccines</h3>
            <table>
                <thead>
                    <tr>
                        <th>Vaccine</th>
                        <th>Dose</th>
                        <th>Due Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($schedule as $item)
                        <tr>
                            <td>{{ $item->vaccine_name }} <small style="color:#94a3b8">({{ $item->vaccine_code }})</small></td>
                            <td>{{ $item->dose_number ? 'Dose '.$item->dose_number : '—' }}</td>
                            <td>{{ $item->due_date?->format('Y-m-d') ?? '—' }}</td>
                            <td>
                                <span class="badge badge-{{ $item->isOverdue() ? 'overdue' : 'due' }}">
                                    {{ $item->isOverdue() ? 'Overdue' : 'Due' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    @endif
</main>
</body>
</html>
