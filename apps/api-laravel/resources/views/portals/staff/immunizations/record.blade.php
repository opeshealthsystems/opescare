<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Record Vaccine - OpesCare</title>
    <style>
        body { font-family: sans-serif; margin: 0; background: #f4f6f9; color: #1a1a2e; }
        header { background: #1a1a2e; color: #fff; padding: 1rem 2rem; }
        header h1 { font-size: 1.2rem; margin: 0; }
        main { padding: 2rem; max-width: 720px; margin: 0 auto; }
        .card { background: #fff; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 4px rgba(0,0,0,.08); margin-bottom: 1.5rem; }
        .form-group { display: flex; flex-direction: column; gap: 4px; margin-bottom: 1.1rem; }
        label { font-size: 0.82rem; color: #475569; font-weight: 600; }
        input, select, textarea { padding: 8px 10px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.9rem; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .form-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; }
        .btn-primary { padding: 10px 24px; background: #1a1a2e; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 1rem; }
        .back-link { display: inline-block; margin-bottom: 1rem; color: #1d4ed8; text-decoration: none; }
        h2 { margin-top: 0; }
        .hint { font-size: 0.78rem; color: #94a3b8; }
        .section-title { font-size: 0.85rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .05em; border-bottom: 1px solid #f1f5f9; padding-bottom: 6px; margin: 1.2rem 0 0.8rem; }
        .alert-warn { background: #fef3c7; border: 1px solid #fcd34d; border-radius: 6px; padding: 0.75rem 1rem; color: #92400e; font-size: 0.85rem; margin-bottom: 1rem; }
    </style>
</head>
<body>
<header>
    <h1>OpesCare — Record Vaccine Administration</h1>
</header>
<main>
    <a href="{{ route('portals.staff.immunizations', ['patient_id' => request('patient_id')]) }}" class="back-link">← Back to Immunization Records</a>

    <div class="alert-warn">
        Duplicate prevention is enforced: recording the same vaccine code on the same date with the same lot number will be rejected. Check existing records before proceeding.
    </div>

    <div class="card">
        <h2>Record Vaccine</h2>
        <form method="post" action="{{ route('portals.staff.immunizations.store') }}">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label for="patient_id">Patient ID *</label>
                    <input id="patient_id" name="patient_id" value="{{ old('patient_id', request('patient_id')) }}" required>
                </div>
                <div class="form-group">
                    <label for="facility_id">Facility ID *</label>
                    <input id="facility_id" name="facility_id" value="{{ old('facility_id') }}" required>
                </div>
            </div>

            <div class="section-title">Vaccine Details</div>

            <div class="form-row">
                <div class="form-group">
                    <label for="vaccine_code">Vaccine Code *</label>
                    <input id="vaccine_code" name="vaccine_code" value="{{ old('vaccine_code') }}" placeholder="e.g. BCG, OPV, DPT" required>
                    <span class="hint">WHO-EPI code or local code</span>
                </div>
                <div class="form-group">
                    <label for="vaccine_name">Vaccine Name *</label>
                    <input id="vaccine_name" name="vaccine_name" value="{{ old('vaccine_name') }}" placeholder="e.g. Bacillus Calmette-Guérin" required>
                </div>
            </div>

            <div class="form-row-3">
                <div class="form-group">
                    <label for="dose_number">Dose Number</label>
                    <input type="number" id="dose_number" name="dose_number" value="{{ old('dose_number') }}" min="1" placeholder="1, 2, 3...">
                </div>
                <div class="form-group">
                    <label for="lot_number">Lot Number</label>
                    <input id="lot_number" name="lot_number" value="{{ old('lot_number') }}">
                </div>
                <div class="form-group">
                    <label for="expiry_date">Vaccine Expiry Date</label>
                    <input type="date" id="expiry_date" name="expiry_date" value="{{ old('expiry_date') }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="manufacturer">Manufacturer</label>
                    <input id="manufacturer" name="manufacturer" value="{{ old('manufacturer') }}">
                </div>
                <div class="form-group">
                    <label for="administered_at">Date Administered *</label>
                    <input type="datetime-local" id="administered_at" name="administered_at" value="{{ old('administered_at', now()->format('Y-m-d\TH:i')) }}" required>
                </div>
            </div>

            <div class="section-title">Administration Details</div>

            <div class="form-row-3">
                <div class="form-group">
                    <label for="route">Route</label>
                    <select id="route" name="route">
                        <option value="">— Select —</option>
                        <option value="IM" @selected(old('route')==='IM')>IM (Intramuscular)</option>
                        <option value="SC" @selected(old('route')==='SC')>SC (Subcutaneous)</option>
                        <option value="oral" @selected(old('route')==='oral')>Oral</option>
                        <option value="intradermal" @selected(old('route')==='intradermal')>Intradermal</option>
                        <option value="IN" @selected(old('route')==='IN')>Intranasal</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="site">Injection Site</label>
                    <input id="site" name="site" value="{{ old('site') }}" placeholder="e.g. Left deltoid">
                </div>
                <div class="form-group">
                    <label for="dose_quantity">Dose Qty</label>
                    <input type="number" step="0.01" id="dose_quantity" name="dose_quantity" value="{{ old('dose_quantity') }}" placeholder="0.5">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="completed" @selected(old('status','completed')==='completed')>Completed</option>
                        <option value="not_done" @selected(old('status')==='not_done')>Not Done</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="not_done_reason">Not Done Reason</label>
                    <input id="not_done_reason" name="not_done_reason" value="{{ old('not_done_reason') }}" placeholder="If status is Not Done">
                </div>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_historical" value="1" @checked(old('is_historical'))>
                    This is a historical / self-reported record
                </label>
                <span class="hint">Historical records are clearly labeled and not treated as facility-verified administrations.</span>
            </div>

            <button type="submit" class="btn-primary">Record Immunization</button>
        </form>
    </div>
</main>
</body>
</html>
