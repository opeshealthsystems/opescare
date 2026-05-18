<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facility Go-Live Readiness</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 32px; color: #172033; background: #f7f9fc; }
        main { max-width: 980px; margin: 0 auto; }
        header { display: flex; justify-content: space-between; gap: 16px; align-items: flex-start; margin-bottom: 24px; }
        h1 { margin: 0 0 8px; font-size: 28px; }
        .status { padding: 8px 12px; border-radius: 6px; background: #e8eefc; font-weight: 700; text-transform: uppercase; }
        .approved { background: #dff5e6; color: #166534; }
        .blocked { background: #fee2e2; color: #991b1b; }
        section { background: white; border: 1px solid #dbe3ef; border-radius: 8px; padding: 20px; margin-bottom: 18px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #edf1f7; }
        th { font-size: 12px; text-transform: uppercase; color: #526071; }
        .ok { color: #166534; font-weight: 700; }
        .missing { color: #991b1b; font-weight: 700; }
        .meta { color: #526071; }
        @media (max-width: 640px) {
            body { margin: 16px; }
            header { flex-direction: column; gap: 12px; }
            th, td { padding: 8px 6px; font-size: 13px; }
        }
    </style>
</head>
<body>
<main>
    <header>
        <div>
            <h1>{{ $facility->name }} Go-Live Readiness</h1>
            <div class="meta">Facility status: {{ $facility->status }} | Type: {{ $facility->type }}</div>
        </div>
        <div class="status {{ $readiness->can_go_live ? 'approved' : 'blocked' }}">
            {{ $readiness->status }}
        </div>
    </header>

    <section>
        <h2>Readiness Result</h2>
        <p><strong>Can go live:</strong> {{ $readiness->can_go_live ? 'Yes' : 'No' }}</p>
        <p><strong>Missing:</strong> {{ empty($missingItems) ? 'None' : implode(', ', $missingItems) }}</p>
        <p><strong>Risks:</strong> {{ empty($missingItems) ? 'None' : 'Go-live is blocked until all missing readiness items are complete.' }}</p>
        @if ($readiness->approval_note)
            <p><strong>Approval note:</strong> {{ $readiness->approval_note }}</p>
        @endif
    </section>

    <section>
        <h2>Checklist</h2>
        <table>
            <thead>
            <tr>
                <th>Item</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($labels as $key => $label)
                <tr>
                    <td>{{ $label }}</td>
                    <td class="{{ ($readiness->checklist_json[$key] ?? false) ? 'ok' : 'missing' }}">
                        {{ ($readiness->checklist_json[$key] ?? false) ? 'Complete' : 'Missing' }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>
</main>
</body>
</html>
