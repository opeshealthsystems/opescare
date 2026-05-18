<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Desk</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 32px; color: #172033; background: #f7f9fc; }
        main { max-width: 1100px; margin: 0 auto; }
        h1 { margin: 0 0 20px; font-size: 28px; }
        section { background: white; border: 1px solid #dbe3ef; border-radius: 8px; padding: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; border-bottom: 1px solid #edf1f7; text-align: left; vertical-align: top; }
        th { font-size: 12px; text-transform: uppercase; color: #526071; }
        .priority { font-weight: 700; text-transform: uppercase; }
        .meta { color: #526071; font-size: 13px; }
    </style>
</head>
<body>
<main>
    <h1>Support Desk</h1>
    <section>
        <table>
            <thead>
            <tr>
                <th>Subject</th>
                <th>Requester</th>
                <th>Status</th>
                <th>Priority</th>
                <th>SLA Due</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($tickets as $ticket)
                <tr>
                    <td>
                        <strong>{{ $ticket->subject }}</strong>
                        <div class="meta">{{ $ticket->category }}</div>
                    </td>
                    <td>{{ $ticket->requester_type }}</td>
                    <td>{{ $ticket->status }}</td>
                    <td class="priority">{{ $ticket->priority }}</td>
                    <td>{{ optional($ticket->sla_due_at)->format('Y-m-d H:i') ?? 'Not set' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No support tickets found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </section>
</main>
</body>
</html>
