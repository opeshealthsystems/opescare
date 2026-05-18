<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Facility Appointments - OpesCare</title>
</head>
<body>
    <main>
        <h1>Facility Appointments</h1>

        <form method="get">
            <label>
                Facility ID
                <input name="facility_id" value="{{ request('facility_id') }}">
            </label>
            <label>
                Provider ID
                <input name="provider_id" value="{{ request('provider_id') }}">
            </label>
            <button type="submit">Filter</button>
        </form>

        @if ($appointments->isEmpty())
            <p>No appointments match the selected filters.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Patient</th>
                        <th>Provider</th>
                        <th>Type</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($appointments as $appointment)
                        <tr>
                            <td>{{ $appointment->scheduled_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $appointment->patient_id }}</td>
                            <td>{{ $appointment->provider_id }}</td>
                            <td>{{ $appointment->appointment_type }}</td>
                            <td>{{ $appointment->status }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </main>
</body>
</html>
