<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OpesCare Governance Desk</title>
    <meta name="theme-color" content="#090D16">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <style>
        @verbatim
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap');
        @endverbatim
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #090D16;
            color: #E2E8F0;
            margin: 0;
            padding: 8mm;
            min-height: 100vh;
            box-sizing: border-box;
        }

        .container {
            max-width: 1050px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding-bottom: 5mm;
            margin-bottom: 8mm;
        }

        .title {
            font-family: 'Outfit', sans-serif;
            font-size: 24px;
            font-weight: 700;
            margin: 0;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8mm;
            margin-bottom: 8mm;
        }

        .section-title {
            font-family: 'Outfit', sans-serif;
            font-size: 18px;
            font-weight: 600;
            color: #0DF2C9;
            margin: 0 0 4mm 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card {
            background: rgba(17, 24, 39, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 12px;
            padding: 5mm;
            margin-bottom: 5mm;
        }

        .list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 3.5mm 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
        }

        .list-item:last-child {
            border-bottom: none;
        }

        .badge-pending {
            background: rgba(245, 158, 11, 0.1);
            color: #F59E0B;
            border: 1px solid rgba(245, 158, 11, 0.2);
            padding: 0.8mm 2mm;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
        }

        .badge-stale {
            background: rgba(239, 68, 68, 0.15);
            color: #F87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
            padding: 0.8mm 2mm;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
        }

        .btn-action {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 4px;
            padding: 1.5mm 3.5mm;
            color: #fff;
            font-family: 'Outfit', sans-serif;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-action:hover {
            border-color: #0DF2C9;
        }

        @media (max-width: 768px) {
            body { padding: 4mm; }
            .grid { grid-template-columns: 1fr; gap: 4mm; }
            .header { flex-direction: column; align-items: flex-start; gap: 2mm; }
            .list-item { flex-wrap: wrap; gap: 2mm; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div>
            <h1 class="title">OpesCare Verified Care Map Moderation Desk</h1>
            <p style="margin: 3px 0 0 0; color: #64748B; font-size: 13px;">Clinical Listing Verification & Quality Audit Plane</p>
        </div>
        <div style="font-size: 12px; color: #64748B;">
            Logged in as Care Map Governance Officer
        </div>
    </div>

    <div class="grid">
        <div>
            <h2 class="section-title">Pending Claims Queue</h2>
            <div class="card">
                @forelse($pendingClaims as $claim)
                    <div class="list-item">
                        <div>
                            <div style="font-weight: 700; color: #fff;">{{ $claim->facility->facility_name }}</div>
                            <div style="font-size: 11.5px; color: #64748B; margin-top: 1mm;">
                                Claimant: {{ $claim->claimant->name ?? 'User Request' }}
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 3mm;">
                            <span class="badge-pending">SUBMITTED</span>
                            <button class="btn-action">Review Authority</button>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; color: #64748B; padding: 4mm 0;">
                        No pending listing ownership claims currently in the queue.
                    </div>
                @endforelse
            </div>
        </div>

        <div>
            <h2 class="section-title">Listing Correction Warnings</h2>
            <div class="card">
                @forelse($reports as $report)
                    <div class="list-item">
                        <div>
                            <div style="font-weight: 700; color: #fff;">{{ $report->facility->facility_name }}</div>
                            <div style="font-size: 11.5px; color: #64748B; margin-top: 1mm;">
                                Type: {{ str_replace('_', ' ', $report->report_type) }}
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 3mm;">
                            <span class="badge-stale">WARNING</span>
                            <button class="btn-action">Investigate</button>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; color: #64748B; padding: 4mm 0;">
                        No outstanding corrections or listings warnings reported.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <h2 class="section-title">Stale Stock Alerts (Pharmacy & Blood Bank)</h2>
    <div class="card">
        @forelse($staleStock as $stale)
            <div class="list-item">
                <div>
                    <div style="font-weight: 700; color: #fff;">{{ $stale->medicine_name }}</div>
                    <div style="font-size: 11.5px; color: #64748B; margin-top: 1.5mm;">
                        Facility: {{ $stale->facility->facility_name }} &bull; Brand: {{ $stale->brand_name }}
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 4mm;">
                    <span style="font-size: 11.5px; color: #94A3B8;">Last Sync: {{ $stale->last_updated_at ? $stale->last_updated_at->diffForHumans() : 'Never' }}</span>
                    <span class="badge-stale">STALE > 72H</span>
                    <button class="btn-action">Force Partner Re-sync</button>
                </div>
            </div>
        @empty
            <div style="text-align: center; color: #64748B; padding: 5mm 0;">
                All partner medicine and blood stocks are fresh and within synchronization limits.
            </div>
        @endforelse
    </div>
</div>

</body>
</html>
