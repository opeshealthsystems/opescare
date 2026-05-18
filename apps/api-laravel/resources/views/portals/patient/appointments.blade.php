<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>My Appointments - OpesCare</title>
</head>
<body>
    <main>
        <h1>My Appointments</h1>

        @if ($appointments->isEmpty())
            <p>No appointments are scheduled.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Facility</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($appointments as $appointment)
                        <tr>
                            <td>{{ $appointment->scheduled_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $appointment->appointment_type }}</td>
                            <td>{{ $appointment->status }}</td>
                            <td>{{ $appointment->facility_id }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </main>
</body>
</html>
