<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚨 @if($locale === ‘fr’)Services d’urgence
@else Emergency Service Navigation
@endif</title>
    <meta name="theme-color" content="#090D16">
    <link rel="icon" type="image/svg+xml" href="{{ asset(‘favicon.svg’) }}">
    <style>
        @verbatim
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap');
        @endverbatim
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0F0505; /* Deep alarm dark red-black */
            color: #FEE2E2;
            margin: 0;
            padding: 8mm;
            min-height: 100vh;
            box-sizing: border-box;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            border-bottom: 2px solid #EF4444;
            padding-bottom: 5mm;
            margin-bottom: 8mm;
            text-align: center;
        }

        .emergency-title {
            font-family: 'Outfit', sans-serif;
            font-size: 32px;
            font-weight: 900;
            color: #EF4444;
            margin: 0 0 2mm 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .red-alert-box {
            background: rgba(239, 68, 68, 0.1);
            border: 2px solid #EF4444;
            border-radius: 12px;
            padding: 6mm;
            margin-bottom: 8mm;
            color: #FCA5A5;
            font-size: 13.5px;
            line-height: 1.6;
        }

        .section-title {
            font-family: 'Outfit', sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 5mm;
        }

        .emergency-card {
            background: rgba(20, 10, 10, 0.85);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 12px;
            padding: 6mm;
            margin-bottom: 5mm;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .facility-name {
            font-family: 'Outfit', sans-serif;
            font-size: 20px;
            font-weight: 800;
            color: #fff;
            margin: 0 0 1.5mm 0;
        }

        .btn-call {
            background: #EF4444;
            color: #fff;
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 15px;
            text-decoration: none;
            padding: 3mm 6mm;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(239, 68, 68, 0.4);
            transition: all 0.2s ease;
            text-transform: uppercase;
        }

        .btn-call:hover {
            background: #DC2626;
            transform: scale(1.05);
        }

        .btn-back {
            display: inline-block;
            margin-top: 6mm;
            color: #94A3B8;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 600;
        }

        .btn-back:hover {
            color: #fff;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1 class="emergency-title">🚨 @if($locale === ‘fr’)Services d’urgence OpesCare
@else OpesCare Emergency Care Access
@endif</h1>
        <p style="margin: 0; font-size: 14px; color: #FCA5A5;">
            @if($locale === ‘fr’)Localisateur de centres de traumatologie et d’urgence ouverts 24/7
@else 24/7 Active Trauma & Emergency Center Locator
@endif
        </p>
    </div>

    <!-- Red alarm mandatory regulatory disclosure -->
    <div class="red-alert-box">
        <strong>⚠️ @if($locale === ‘fr’)AVIS D’URGENCE CRITIQUE :
@else CRITICAL EMERGENCY WARNING :
@endif</strong><br>
        @if($locale === ‘fr’)S’il s’agit d’une urgence vitale, composez immédiatement le 15, le 112 ou rendez-vous au centre de secours le plus proche. Les données cartographiques ne remplacent pas les services de secours officiels.
@else If this is a life-threatening emergency, contact local emergency services or go to the nearest emergency facility immediately. OpesCare does not guarantee immediate treatment availability.
@endif
    </div>

    <h2 class="section-title">@if($locale === ‘fr’)Centres d’urgence les plus proches
@else Closest Verified Emergency Facilities
@endif</h2>

    <div class="emergency-list">
        @forelse($facilities as $facility)
            <div class="emergency-card">
                <div>
                    <h3 class="facility-name">{{ $facility->facility_name }}</h3>
                    <p style="margin: 0; font-size: 13px; color: #FCA5A5; font-weight: 600;">
                        {{ $locale === 'fr' ? 'Urgences 24/7 & Traumatologie' : '24/7 Emergency Department & Trauma Unit' }}
                    </p>
                    <p style="margin: 1.5mm 0 0 0; font-size: 12.5px; color: #94A3B8;">
                        {{ $facility->city }}, {{ $facility->address }}
                    </p>
                </div>
                <a href="tel:{{ $facility->emergency_contact ?? $facility->phone_primary }}" class="btn-call">
                    📞 {{ $locale === 'fr' ? 'Appeler' : 'Call Department' }}
                </a>
            </div>
        @empty
            <div class="emergency-card" style="justify-content: center; text-align: center; color: #94A3B8;">
                @if($locale === ‘fr’)Aucun hôpital d’urgence actif trouvé à proximité.
@else No active emergency hospitals found nearby.
@endif
            </div>
        @endforelse
    </div>

    <div style="text-align: center;">
        <a href="{{ route('public.care-map') }}" class="btn-back">
            &larr; {{ $locale === 'fr' ? 'Retour à la carte générale' : 'Back to interactive map' }}
        </a>
    </div>
</div>

</body>
</html>
