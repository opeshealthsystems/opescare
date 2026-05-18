<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OpesCare Academy — Learner Dashboard</title>
    <meta name="theme-color" content="#090D16">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap');
        
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
            max-width: 1000px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8mm;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding-bottom: 4mm;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 4mm;
        }

        .logo {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0F4C81 0%, #0DF2C9 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            color: #fff;
        }

        .title {
            font-family: 'Outfit', sans-serif;
            font-size: 24px;
            font-weight: 700;
            margin: 0;
        }

        .disclaimer-bar {
            background-color: rgba(15, 76, 129, 0.15);
            border: 1px solid rgba(13, 242, 201, 0.2);
            padding: 4mm;
            border-radius: 8px;
            margin-bottom: 8mm;
            font-size: 11.5px;
            color: #94A3B8;
            line-height: 1.5;
        }

        .grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 8mm;
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
            transition: border-color 0.2s ease;
        }

        .card:hover {
            border-color: rgba(13, 242, 201, 0.15);
        }

        .progress-bar-bg {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 6px;
            height: 6px;
            width: 100%;
            margin: 3mm 0;
            overflow: hidden;
        }

        .progress-bar-fill {
            background: linear-gradient(90deg, #0F4C81 0%, #0DF2C9 100%);
            height: 100%;
            border-radius: 6px;
        }

        .meta {
            display: flex;
            justify-content: space-between;
            font-size: 11.5px;
            color: #64748B;
        }

        .cert-list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 3mm 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
        }

        .cert-list-item:last-child {
            border-bottom: none;
        }

        .badge {
            background: rgba(16, 185, 129, 0.1);
            color: #10B981;
            padding: 0.8mm 2mm;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        @media (max-width: 768px) {
            body { padding: 4mm; }
            .grid { grid-template-columns: 1fr; gap: 4mm; }
            .header { flex-direction: column; align-items: flex-start; gap: 2mm; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div class="brand">
            <div class="logo">OA</div>
            <h1 class="title">OpesCare Academy Learner Console</h1>
        </div>
        <div style="font-size: 12px; color: #64748B;">
            Logged in as Certified Professional
        </div>
    </div>

    <!-- Mandatory Regulatory Disclaimers -->
    <div class="disclaimer-bar">
        <strong>EN:</strong> This certification confirms completion of OpesCare digital health workflow training. It does not replace professional licensing, clinical qualification, statutory registration, or authorization to practice a regulated health profession.<br>
        <strong>FR:</strong> Ce certificat confirme l’achèvement d’une formation aux flux de travail numériques d’OpesCare. Il ne remplace pas l’autorisation professionnelle, la qualification clinique, l’inscription réglementaire ni le droit d’exercer une profession de santé réglementée.
    </div>

    <div class="grid">
        <div>
            <h2 class="section-title">My Active Training Tracks</h2>
            @forelse($enrollments as $enrollment)
                <div class="card">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 2mm;">
                        <span style="font-weight: 700; color: #fff;">{{ $enrollment->course->title_en }}</span>
                        <span style="color: #0DF2C9; font-weight: 700;">{{ $enrollment->progress_percentage }}%</span>
                    </div>
                    <div style="font-size: 12px; color: #94A3B8; margin-bottom: 3mm;">
                        {{ $enrollment->course->description_en }}
                    </div>
                    <div class="progress-bar-bg">
                        <div class="progress-bar-fill" style="width: {{ $enrollment->progress_percentage }}%;"></div>
                    </div>
                    <div class="meta">
                        <span>Code: {{ $enrollment->course->course_code }}</span>
                        <span>Status: {{ strtoupper($enrollment->status) }}</span>
                    </div>
                </div>
            @empty
                <div class="card" style="text-align: center; color: #64748B;">
                    You are not currently enrolled in any active training tracks.
                </div>
            @endforelse
        </div>

        <div>
            <h2 class="section-title">My Issued Credentials</h2>
            <div class="card">
                @forelse($certificates as $cert)
                    <div class="cert-list-item">
                        <div>
                            <div style="font-weight: 600; color: #fff; font-size: 13px;">{{ $cert->course->title_en }}</div>
                            <div style="font-family: monospace; font-size: 11px; color: #64748B; margin-top: 1mm;">
                                {{ $cert->certificate_number }}
                            </div>
                        </div>
                        <span class="badge">{{ strtoupper($cert->status) }}</span>
                    </div>
                @empty
                    <div style="text-align: center; color: #64748B; padding: 4mm 0;">
                        No digital health competency certificates have been issued yet.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

</body>
</html>
