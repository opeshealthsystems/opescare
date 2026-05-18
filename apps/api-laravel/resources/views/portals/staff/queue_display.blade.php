<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Queue Display - OpesCare</title>
</head>
<body>
    <main>
        <h1>Queue Display</h1>

        @if ($tickets->isEmpty())
            <p>No patients are waiting.</p>
        @else
            <ol>
                @foreach ($tickets as $ticket)
                    <li>
                        <strong>{{ $ticket['queue_number'] }}</strong>
                        <span>{{ $ticket['masked_patient_name'] }}</span>
                        <span>{{ $ticket['status'] }}</span>
                    </li>
                @endforeach
            </ol>
        @endif
    </main>
</body>
</html>
