<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>New Referral - OpesCare</title>
    <style>
        body { font-family: sans-serif; margin: 0; background: #f4f6f9; color: #1a1a2e; }
        header { background: #1a1a2e; color: #fff; padding: 1rem 2rem; }
        header h1 { font-size: 1.2rem; margin: 0; }
        main { padding: 2rem; max-width: 680px; margin: 0 auto; }
        .card { background: #fff; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        .form-group { display: flex; flex-direction: column; gap: 4px; margin-bottom: 1.1rem; }
        label { font-size: 0.82rem; color: #475569; font-weight: 600; }
        input, select, textarea { padding: 8px 10px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.9rem; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .btn-primary { padding: 10px 24px; background: #1a1a2e; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 1rem; }
        .back-link { display: inline-block; margin-bottom: 1rem; color: #1d4ed8; text-decoration: none; }
        h2 { margin-top: 0; }
        .hint { font-size: 0.78rem; color: #94a3b8; }
    </style>
</head>
<body>
<header>
    <h1>OpesCare — New Referral</h1>
</header>
<main>
    <a href="{{ route('portals.staff.referrals') }}" class="back-link">← Back to Referrals</a>

    <div class="card">
        <h2>Create Referral</h2>
        <form method="post" action="{{ route('portals.staff.referrals.store') }}">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label for="patient_id">Patient ID *</label>
                    <input id="patient_id" name="patient_id" value="{{ old('patient_id') }}" required placeholder="Patient UUID">
                </div>
                <div class="form-group">
                    <label for="urgency">Urgency *</label>
                    <select id="urgency" name="urgency" required>
                        <option value="routine" @selected(old('urgency','routine')==='routine')>Routine</option>
                        <option value="urgent" @selected(old('urgency')==='urgent')>Urgent</option>
                        <option value="emergency" @selected(old('urgency')==='emergency')>Emergency</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="referring_facility_id">Referring Facility ID *</label>
                    <input id="referring_facility_id" name="referring_facility_id" value="{{ old('referring_facility_id') }}" required>
                </div>
                <div class="form-group">
                    <label for="referring_provider_id">Referring Provider ID</label>
                    <input id="referring_provider_id" name="referring_provider_id" value="{{ old('referring_provider_id') }}" placeholder="Optional">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="receiving_facility_id">Receiving Facility ID</label>
                    <input id="receiving_facility_id" name="receiving_facility_id" value="{{ old('receiving_facility_id') }}" placeholder="Optional — can add later">
                </div>
                <div class="form-group">
                    <label for="receiving_specialty">Specialty / Department</label>
                    <input id="receiving_specialty" name="receiving_specialty" value="{{ old('receiving_specialty') }}" placeholder="e.g. Cardiology">
                </div>
            </div>

            <div class="form-group">
                <label for="reason">Reason for Referral *</label>
                <textarea id="reason" name="reason" rows="3" required>{{ old('reason') }}</textarea>
            </div>

            <div class="form-group">
                <label for="clinical_summary">Clinical Summary</label>
                <textarea id="clinical_summary" name="clinical_summary" rows="4">{{ old('clinical_summary') }}</textarea>
                <span class="hint">Include relevant diagnoses, medications, allergies, and care context.</span>
            </div>

            <div class="form-group">
                <label for="expires_at">Access Expires At</label>
                <input type="datetime-local" id="expires_at" name="expires_at" value="{{ old('expires_at') }}">
                <span class="hint">Defaults to 30 days if not set. The receiving facility's access grant will expire at this time.</span>
            </div>

            <button type="submit" class="btn-primary">Create Referral (Draft)</button>
        </form>
    </div>
</main>
</body>
</html>
