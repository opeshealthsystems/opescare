<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $facility->facility_name }} — {{ $locale === 'fr' ? 'Profil Clinique' : 'Clinical Profile' }}</title>
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
            max-width: 900px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding-bottom: 6mm;
            margin-bottom: 6mm;
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 2mm 4mm;
            border-radius: 6px;
            color: #E2E8F0;
            text-decoration: none;
            font-family: 'Outfit', sans-serif;
            font-size: 13px;
            transition: all 0.2s ease;
        }

        .btn-back:hover {
            border-color: #0DF2C9;
            color: #fff;
        }

        .badge-verified {
            background: rgba(16, 185, 129, 0.1);
            color: #10B981;
            border: 1px solid rgba(16, 185, 129, 0.2);
            font-size: 11px;
            font-weight: 700;
            padding: 1.5mm 3.5mm;
            border-radius: 6px;
            display: inline-block;
            margin-bottom: 3mm;
            text-transform: uppercase;
        }

        .facility-title {
            font-family: 'Outfit', sans-serif;
            font-size: 28px;
            font-weight: 800;
            margin: 0 0 2mm 0;
            color: #fff;
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
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            padding-bottom: 2mm;
        }

        .card {
            background: rgba(17, 24, 39, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 12px;
            padding: 5mm;
            margin-bottom: 6mm;
        }

        .contact-row {
            display: flex;
            justify-content: space-between;
            padding: 2.5mm 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            font-size: 13.5px;
        }

        .contact-row:last-child {
            border-bottom: none;
        }

        .label {
            color: #64748B;
            font-weight: 500;
        }

        .value {
            color: #fff;
            font-weight: 600;
        }

        .catalog-item {
            display: flex;
            justify-content: space-between;
            padding: 3mm 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
        }

        .catalog-item:last-child {
            border-bottom: none;
        }

        .disclaimer-bar {
            background-color: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.2);
            padding: 4mm;
            border-radius: 8px;
            margin-bottom: 6mm;
            font-size: 12px;
            color: #F87171;
            line-height: 1.5;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div>
            <span class="badge-verified">
                {{ $locale === 'fr' ? 'Établissement Agréé' : 'Authorized Provider' }}
            </span>
            <h1 class="facility-title">{{ $facility->facility_name }}</h1>
            <p style="margin: 0; color: #94A3B8; font-size: 14px;">
                {{ ucfirst($facility->facility_type) }} &bull; {{ $facility->city }}, {{ $facility->address }}
            </p>
        </div>
        <a href="{{ route('public.care-map') }}" class="btn-back">
            &larr; {{ $locale === 'fr' ? 'Retour à la carte' : 'Back to directory' }}
        </a>
    </div>

    <!-- Regulatory safety warning -->
    <div class="disclaimer-bar">
        <strong>⚠️ {{ $locale === 'fr' ? 'Avis clinique de sécurité :' : 'Clinical Safety Notice :' }}</strong>
        {{ $locale === 'fr' 
            ? 'Les données de disponibilité ne sont que signalées et peuvent changer rapidement. Toujours contacter l’établissement avant de prendre des décisions médicales.' 
            : 'Medicine or blood availability is reported by the facility and may change. Always confirm with the provider beforehand.' }}
    </div>

    <div class="grid">
        <div>
            <h2 class="section-title">{{ $locale === 'fr' ? 'Services de Santé Offerts' : 'Clinical Service Offerings' }}</h2>
            <div class="card">
                @forelse($facility->services as $service)
                    <div class="catalog-item">
                        <div>
                            <span style="font-weight: 700; color: #fff;">{{ $service->service_name }}</span><br>
                            <span style="font-size: 11px; color: #64748B;">Category: {{ $service->service_category }}</span>
                        </div>
                        <span style="color: #0DF2C9; font-weight: 700; font-size: 13px;">
                            {{ $service->appointment_required ? 'Appointment Required' : 'Walk-in' }}
                        </span>
                    </div>
                @empty
                    <div style="color: #64748B; text-align: center; padding: 4mm 0;">
                        No explicit services catalogs reported for this facility.
                    </div>
                @endforelse
            </div>

            @if($facility->facility_type === 'pharmacy')
                <h2 class="section-title">{{ $locale === 'fr' ? 'Stocks de Médicaments Signalés' : 'Reported Medicine Stocks' }}</h2>
                <div class="card">
                    @forelse($facility->pharmacyStock as $stock)
                        <div class="catalog-item">
                            <div>
                                <span style="font-weight: 700; color: #fff;">{{ $stock->medicine_name }}</span><br>
                                <span style="font-size: 11px; color: #64748B;">Generic: {{ $stock->generic_name ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span style="color: #10B981; font-weight: 700;">{{ $stock->availability_status }}</span><br>
                                <span style="font-size: 11px; color: #64748B; display: block; text-align: right;">{{ $stock->freshness_status }}</span>
                            </div>
                        </div>
                    @empty
                        <div style="color: #64748B; text-align: center; padding: 4mm 0;">
                            No pharmacy stock listings are registered.
                        </div>
                    @endforelse
                </div>
            @endif

            @if($facility->facility_type === 'blood_bank' || $facility->facility_type === 'hospital')
                <h2 class="section-title">{{ $locale === 'fr' ? 'Disponibilité des Produits Sanguins' : 'Blood Bank Availability' }}</h2>
                <div class="card">
                    @forelse($facility->bloodAvailability as $blood)
                        <div class="catalog-item">
                            <div>
                                <span style="font-weight: 700; color: #fff; font-size: 16px;">{{ $blood->blood_group }}</span><br>
                                <span style="font-size: 11px; color: #64748B;">Component: {{ $blood->component_type }}</span>
                            </div>
                            <span style="color: #0DF2C9; font-weight: 700;">{{ $blood->units_available_range }} Units</span>
                        </div>
                    @empty
                        <div style="color: #64748B; text-align: center; padding: 4mm 0;">
                            No blood products are currently listed for this facility.
                        </div>
                    @endforelse
                </div>
            @endif
        </div>

        <div>
            <h2 class="section-title">{{ $locale === 'fr' ? 'Contact & Accès' : 'Contact Details' }}</h2>
            <div class="card">
                <div class="contact-row">
                    <span class="label">{{ $locale === 'fr' ? 'Téléphone' : 'Phone' }}</span>
                    <span class="value">{{ $facility->phone_primary }}</span>
                </div>
                <div class="contact-row">
                    <span class="label">Email</span>
                    <span class="value">{{ $facility->email ?? 'N/A' }}</span>
                </div>
                <div class="contact-row">
                    <span class="label">Website</span>
                    <span class="value">{{ $facility->website ?? 'N/A' }}</span>
                </div>
                <div class="contact-row">
                    <span class="label">Latitude</span>
                    <span class="value">{{ $facility->latitude ?? 'N/A' }}</span>
                </div>
                <div class="contact-row">
                    <span class="label">Longitude</span>
                    <span class="value">{{ $facility->longitude ?? 'N/A' }}</span>
                </div>
            </div>

            <h2 class="section-title">{{ $locale === 'fr' ? 'Assurances Acceptées' : 'Accepted Insurances' }}</h2>
            <div class="card">
                @forelse($facility->insurances as $ins)
                    <div style="margin-bottom: 3.5mm; padding-bottom: 3.5mm; border-bottom: 1px solid rgba(255, 255, 255, 0.04);">
                        <div style="font-weight: 700; color: #fff;">{{ $ins->insurance_name }}</div>
                        <div style="font-size: 11px; color: #64748B; margin-top: 1mm;">
                            {{ $ins->cashless_available ? 'Cashless Facility' : 'Direct Billing' }}
                        </div>
                    </div>
                @empty
                    <div style="color: #64748B; font-size: 12px; text-align: center; padding: 3mm 0;">
                        No accepted insurance networks registered.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

</body>
</html>
