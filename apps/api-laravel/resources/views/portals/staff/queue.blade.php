<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Patient Queue - OpesCare</title>
</head>
<body>
    <main>
        <h1>Patient Queue</h1>

        <form method="get">
            <label>
                Facility ID
                <input name="facility_id" value="{{ request('facility_id') }}">
            </label>
            <label>
                Queue
                <input name="queue_name" value="{{ request('queue_name') }}">
            </label>
            <button type="submit">Filter</button>
        </form>

        @if ($tickets->isEmpty())
            <p>No active queue tickets match the selected filters.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Number</th>
                        <th>Queue</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Patient</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tickets as $ticket)
                        <tr>
                            <td>{{ $ticket->queue_number }}</td>
                            <td>{{ $ticket->current_queue }}</td>
                            <td>{{ $ticket->status }}</td>
                            <td>{{ $ticket->priority_level }}</td>
                            <td>{{ $ticket->patient?->first_name }} {{ $ticket->patient?->last_name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </main>
</body>
</html>
