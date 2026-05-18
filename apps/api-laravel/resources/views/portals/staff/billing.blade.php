<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Cashier Billing - OpesCare</title>
</head>
<body>
    <main>
        <h1>Cashier Billing</h1>

        <form method="get">
            <label>
                Facility ID
                <input name="facility_id" value="{{ request('facility_id') }}">
            </label>
            <label>
                Patient ID
                <input name="patient_id" value="{{ request('patient_id') }}">
            </label>
            <button type="submit">Filter</button>
        </form>

        @if ($invoices->isEmpty())
            <p>No invoices match the selected filters.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Patient</th>
                        <th>Status</th>
                        <th>Responsibility</th>
                        <th>Paid</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->invoice_number }}</td>
                            <td>{{ $invoice->patient_id }}</td>
                            <td>{{ $invoice->status }}</td>
                            <td>{{ number_format((float) $invoice->patient_responsibility_amount, 2) }}</td>
                            <td>{{ number_format((float) $invoice->paid_amount, 2) }}</td>
                            <td>{{ number_format((float) $invoice->balance_amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </main>
</body>
</html>
