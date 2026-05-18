<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OpesCare Academy — Facility Readiness Cockpit</title>
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

        .metric-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 6mm;
            margin-bottom: 8mm;
        }

        .metric-card {
            background: rgba(17, 24, 39, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 12px;
            padding: 6mm;
            text-align: center;
        }

        .metric-number {
            font-family: 'Outfit', sans-serif;
            font-size: 32px;
            font-weight: 700;
            color: #0DF2C9;
            margin-bottom: 1.5mm;
        }

        .metric-label {
            font-size: 11.5px;
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .main-card {
            background: rgba(17, 24, 39, 0.65);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            padding: 8mm;
        }

        .section-title {
            font-family: 'Outfit', sans-serif;
            font-size: 20px;
            font-weight: 600;
            color: #fff;
            margin: 0 0 4mm 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            padding-bottom: 3mm;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div class="brand">
            <div class="logo">OA</div>
            <h1 class="title">Facility Readiness & Competency Cockpit</h1>
        </div>
        <div style="font-size: 12px; color: #64748B;">
            Facility ID: {{ $facilityId }}
        </div>
    </div>

    <!-- Mandatory Regulatory Disclaimers -->
    <div class="disclaimer-bar">
        <strong>EN:</strong> This certification confirms completion of OpesCare digital health workflow training. It does not replace professional licensing, clinical qualification, statutory registration, or authorization to practice a regulated health profession.<br>
        <strong>FR:</strong> Ce certificat confirme l’achèvement d’une formation aux flux de travail numériques d’OpesCare. Il ne remplace pas l’autorisation professionnelle, la qualification clinique, l’inscription réglementaire ni le droit d’exercer une profession de santé réglementée.
    </div>

    <div class="metric-grid">
        <div class="metric-card">
            <div class="metric-number">{{ $result['readiness_percentage'] ?? 0 }}%</div>
            <div class="metric-label">Digital Health Readiness</div>
        </div>
        <div class="metric-card">
            <div class="metric-number">{{ $result['total_staff'] ?? 0 }}</div>
            <div class="metric-label">Total Facility Staff</div>
        </div>
        <div class="metric-card">
            <div class="metric-number">{{ $result['fully_certified_staff'] ?? 0 }}</div>
            <div class="metric-label">Fully Certified Staff</div>
        </div>
        <div class="metric-card">
            <div class="metric-number">{{ $result['active_learners'] ?? 0 }}</div>
            <div class="metric-label">Active Learners</div>
        </div>
    </div>

    <div class="main-card">
        <h2 class="section-title">Institutional Readiness Actions</h2>
        <p style="font-size: 13px; line-height: 1.6; color: #94A3B8; margin: 0;">
            This cockpit tracks real-time training progress and digital workflow competencies across all active staff members inside Facility <strong style="color: #fff;">{{ $facilityId }}</strong>. Staff members must successfully pass core modules in order to pull clinical summaries or complete advanced workflows, ensuring maximum safety, interoperability, and compliance under regional directives.
        </p>
    </div>
</div>

</body>
</html>
