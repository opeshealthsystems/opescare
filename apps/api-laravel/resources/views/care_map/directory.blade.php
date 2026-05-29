<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@if($locale === ‘fr’)Carte vérifiée d’accès aux soins
@else OpesCare Verified Care Access Map
@endif</title>
    <meta name="theme-color" content="#090D16">
    <link rel="icon" type="image/svg+xml" href="{{ asset(‘favicon.svg’) }}">
    <link rel="manifest" href="{{ asset(‘site.webmanifest’) }}">
    <style>
        @verbatim
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap');
        @endverbatim
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #090D16;
            color: #E2E8F0;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4mm 8mm;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(17, 24, 39, 0.8);
            backdrop-filter: blur(12px);
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
            font-size: 22px;
            font-weight: 700;
            margin: 0;
            background: linear-gradient(to right, #ffffff, #0DF2C9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .lang-switch {
            font-family: 'Outfit', sans-serif;
            font-size: 13px;
            color: #94A3B8;
            text-decoration: none;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1.5mm 4mm;
            border-radius: 20px;
            transition: all 0.2s ease;
        }

        .lang-switch:hover {
            border-color: #0DF2C9;
            color: #fff;
        }

        .layout {
            display: grid;
            grid-template-columns: 380px 1fr;
            flex: 1;
            height: calc(100vh - 65px);
            overflow: hidden;
        }

        .sidebar {
            background: rgba(11, 17, 30, 0.95);
            border-right: 1px solid rgba(255, 255, 255, 0.06);
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            padding: 6mm;
        }

        .search-box {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            padding: 3.5mm;
            display: flex;
            flex-direction: column;
            gap: 3mm;
            margin-bottom: 6mm;
        }

        .search-input {
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 6px;
            padding: 2.5mm;
            color: #fff;
            font-family: 'Inter', sans-serif;
            font-size: 13.5px;
        }

        .search-input:focus {
            outline: none;
            border-color: #0DF2C9;
        }

        .btn-search {
            background: linear-gradient(135deg, #0F4C81 0%, #0DF2C9 100%);
            border: none;
            border-radius: 6px;
            padding: 2.5mm;
            color: #fff;
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            cursor: pointer;
            transition: opacity 0.2s ease;
        }

        .btn-search:hover {
            opacity: 0.9;
        }

        .facilities-list {
            display: flex;
            flex-direction: column;
            gap: 4mm;
        }

        .facility-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 12px;
            padding: 5mm;
            transition: all 0.2s ease;
            position: relative;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .facility-card:hover {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(13, 242, 201, 0.3);
            transform: translateY(-1px);
        }

        .badge-verified {
            background: rgba(16, 185, 129, 0.1);
            color: #10B981;
            border: 1px solid rgba(16, 185, 129, 0.2);
            font-size: 10.5px;
            font-weight: 700;
            padding: 0.5mm 2mm;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 2mm;
        }

        .badge-freshness {
            background: rgba(15, 76, 129, 0.15);
            color: #0DF2C9;
            border: 1px solid rgba(13, 242, 201, 0.25);
            font-size: 10.5px;
            font-weight: 700;
            padding: 0.5mm 2mm;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 2mm;
            margin-left: 1.5mm;
        }

        .facility-name {
            font-family: 'Outfit', sans-serif;
            font-size: 16.5px;
            font-weight: 600;
            color: #fff;
            margin: 0 0 1.5mm 0;
        }

        .facility-meta {
            font-size: 12px;
            color: #94A3B8;
            margin-bottom: 3mm;
            line-height: 1.4;
        }

        .card-actions {
            display: flex;
            gap: 2mm;
        }

        .action-link {
            font-family: 'Outfit', sans-serif;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            padding: 1.5mm 3.5mm;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.05);
            color: #E2E8F0;
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.2s ease;
        }

        .action-link:hover {
            border-color: #0DF2C9;
            color: #fff;
        }

        .map-pane {
            position: relative;
            background: #060910;
        }

        /* High-fidelity Vector Map Mockup */
        .vector-map {
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 30% 20%, rgba(15, 76, 129, 0.1) 0%, transparent 40%),
                radial-gradient(circle at 75% 60%, rgba(13, 242, 201, 0.08) 0%, transparent 40%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .vector-grid {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                linear-gradient(rgba(255, 255, 255, 0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        .map-pin {
            position: absolute;
            width: 14px;
            height: 14px;
            background: #0DF2C9;
            border: 2.5px solid rgba(255,255,255,0.9);
            border-radius: 50%;
            box-shadow: 0 0 10px #0DF2C9;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            display: block;
            text-decoration: none;
        }

        .map-pin:hover {
            transform: scale(1.5);
            box-shadow: 0 0 18px currentColor;
            z-index: 10;
        }

        .safety-bar {
            position: absolute;
            bottom: 6mm;
            left: 50%;
            transform: translateX(-50%);
            width: 85%;
            max-width: 750px;
            background: rgba(11, 17, 30, 0.92);
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(16px);
            border-radius: 12px;
            padding: 4mm 6mm;
            display: flex;
            flex-direction: column;
            gap: 2mm;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            font-size: 11.5px;
            color: #94A3B8;
            line-height: 1.5;
        }

        @media (max-width: 900px) {
            .layout {
                grid-template-columns: 320px 1fr;
            }
        }

        @media (max-width: 700px) {
            .header {
                padding: 3mm 4mm;
                flex-wrap: wrap;
                gap: 2mm;
            }
            .title { font-size: 16px; }
            .layout {
                grid-template-columns: 1fr;
                grid-template-rows: auto 300px;
                height: auto;
                overflow: visible;
            }
            .sidebar {
                max-height: 55vh;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
            }
            .map-pane {
                height: 300px;
                position: relative;
            }
            .safety-bar {
                width: calc(100% - 8mm);
                font-size: 10.5px;
                padding: 3mm 4mm;
            }
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="brand">
            <div class="logo">CM</div>
            <h1 class="title">@if($locale === ‘fr’)Carte d’accès aux soins OpesCare
@else OpesCare Verified Care Access Map
@endif</h1>
        </div>
        <div style="display: flex; align-items: center; gap: 4mm;">
            <a href="{{ route('public.care-map.emergency') }}" style="background: rgba(239, 68, 68, 0.15); color: #F87171; border: 1px solid rgba(239, 68, 68, 0.3); font-weight: 700; padding: 2mm 5mm; border-radius: 6px; text-decoration: none; font-size: 13px;">
                {{ $locale === 'fr' ? 'URGENCE' : 'EMERGENCY' }}
            </a>
            <a href="{{ route('lang.switch', ['locale' => $locale === 'en' ? 'fr' : 'en']) }}" class="lang-switch">
                {{ $locale === 'en' ? 'Version Française' : 'English Version' }}
            </a>
        </div>
    </div>

    <div class="layout">
        <div class="sidebar">
            <div class="search-box">
                <span style="font-family: 'Outfit', sans-serif; font-weight: 600; font-size: 14px; color: #0DF2C9;">
                    {{ $locale === 'fr' ? 'Rechercher des services' : 'Search Healthcare Directory' }}
                </span>
                <form action="{{ route('public.care-map') }}" method="GET" style="display: flex; flex-direction: column; gap: 3mm;">
                    <input type="text" name="query" class="search-input" placeholder="{{ $locale === 'fr' ? 'Médicament, service, hôpital...' : 'Medicine, service, hospital...' }}" value="{{ request('query') }}">
                    <input type="text" name="city" class="search-input" placeholder="{{ $locale === 'fr' ? 'Ville ou région' : 'City or Region' }}" value="{{ request('city') }}">
                    <select name="facility_type" class="search-input" style="cursor:pointer;">
                        <option value="">{{ $locale === 'fr' ? 'Tous les types' : 'All facility types' }}</option>
                        <option value="hospital"    {{ request('facility_type') === 'hospital'    ? 'selected' : '' }}>{{ $locale === 'fr' ? 'Hôpital' : 'Hospital' }}</option>
                        <option value="pharmacy"    {{ request('facility_type') === 'pharmacy'    ? 'selected' : '' }}>{{ $locale === 'fr' ? 'Pharmacie' : 'Pharmacy' }}</option>
                        <option value="laboratory"  {{ request('facility_type') === 'laboratory'  ? 'selected' : '' }}>{{ $locale === 'fr' ? 'Laboratoire' : 'Laboratory' }}</option>
                        <option value="clinic"      {{ request('facility_type') === 'clinic'      ? 'selected' : '' }}>{{ $locale === 'fr' ? 'Clinique' : 'Clinic' }}</option>
                        <option value="blood_bank"  {{ request('facility_type') === 'blood_bank'  ? 'selected' : '' }}>{{ $locale === 'fr' ? 'Banque de Sang' : 'Blood Bank' }}</option>
                        <option value="dental"      {{ request('facility_type') === 'dental'      ? 'selected' : '' }}>{{ $locale === 'fr' ? 'Cabinet Dentaire' : 'Dental' }}</option>
                        <option value="imaging"     {{ request('facility_type') === 'imaging'     ? 'selected' : '' }}>{{ $locale === 'fr' ? 'Imagerie Médicale' : 'Imaging' }}</option>
                    </select>
                    <button type="submit" class="btn-search">{{ $locale === 'fr' ? 'Rechercher' : 'Search' }}</button>
                </form>
            </div>

            <div style="font-size:12px;color:#64748B;margin-bottom:3mm;padding-left:1mm;">
                {{ $facilities->count() }} {{ $locale === 'fr' ? 'établissement(s) trouvé(s)' : 'facility/facilities found' }}
                @if(request()->hasAny(['query','city','facility_type']))
                    — <a href="{{ route('public.care-map') }}" style="color:#0DF2C9;text-decoration:none;">
                        {{ $locale === 'fr' ? 'Effacer les filtres' : 'Clear filters' }}
                    </a>
                @endif
            </div>

            <div class="facilities-list">
                @forelse($facilities as $facility)
                    <div class="facility-card">
                        <div>
                            <span class="badge-verified">
                                {{ $locale === 'fr' ? 'Établissement Vérifié' : 'Verified Facility' }}
                            </span>
                            <span class="badge-freshness">
                                {{ $locale === 'fr' ? 'Données Récentes' : 'Recent Data' }}
                            </span>
                        </div>
                        <h3 class="facility-name">{{ $facility->facility_name }}</h3>
                        <div class="facility-meta">
                            <strong>{{ ucfirst($facility->facility_type) }}</strong> &bull; {{ $facility->city }}, {{ $facility->address }}<br>
                            <span style="font-size: 11px; color: #64748B;">{{ $locale === 'fr' ? 'Tél :' : 'Phone:' }} {{ $facility->phone_primary }}</span>
                        </div>
                        <div class="card-actions">
                            <a href="{{ route('public.care-map.profile', ['id' => $facility->id]) }}" class="action-link">
                                {{ $locale === 'fr' ? 'Voir Profil' : 'View Profile' }}
                            </a>
                            <a href="tel:{{ $facility->phone_primary }}" class="action-link">
                                {{ $locale === 'fr' ? 'Appeler' : 'Call' }}
                            </a>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; color: #64748B; padding: 10mm 0; font-size: 13.5px;">
                        {{ $locale === 'fr' ? 'Aucun établissement trouvé.' : 'No facilities found in your area.' }}
                    </div>
                @endforelse
            </div>
        </div>

        <div class="map-pane">
            <div class="vector-map" id="map-pane">
                <div class="vector-grid"></div>
                @if($facilities->isEmpty())
                    <div style="color:#64748B;font-size:14px;z-index:2;position:relative;">
                        {{ $locale === 'fr' ? 'Aucun établissement à afficher.' : 'No facilities to display.' }}
                    </div>
                @else
                    @php
                        // Compute bounding box for normalisation; fall back to spread-by-index
                        $withCoords = $facilities->filter(fn($f) => $f->latitude && $f->longitude);
                        $useCoords  = $withCoords->count() >= 2;
                        if ($useCoords) {
                            $minLat = $withCoords->min('latitude');
                            $maxLat = $withCoords->max('latitude');
                            $minLon = $withCoords->min('longitude');
                            $maxLon = $withCoords->max('longitude');
                            $latRange = max($maxLat - $minLat, 0.01);
                            $lonRange = max($maxLon - $minLon, 0.01);
                        }
                        $total = min($facilities->count(), 20); // render at most 20 pins
                    @endphp
                    @foreach($facilities->take(20) as $idx => $fac)
                        @php
                            if ($useCoords && $fac->latitude && $fac->longitude) {
                                // Normalise to 8%–88% of the map pane
                                $top  = 88 - (($fac->latitude  - $minLat) / $latRange) * 80;
                                $left = 8  + (($fac->longitude - $minLon) / $lonRange) * 80;
                            } else {
                                // Deterministic spread across grid when no coords available
                                $cols = max(ceil(sqrt($total)), 1);
                                $row  = floor($idx / $cols);
                                $col  = $idx % $cols;
                                $top  = 15 + ($row / max(ceil($total / $cols), 1)) * 70;
                                $left = 10 + ($col / max($cols - 1, 1)) * 80;
                            }
                            $typeColor = match($fac->facility_type) {
                                'hospital'   => '#0DF2C9',
                                'pharmacy'   => '#F59E0B',
                                'laboratory' => '#818CF8',
                                'blood_bank' => '#EF4444',
                                'clinic'     => '#34D399',
                                default      => '#94A3B8',
                            };
                        @endphp
                        <a href="{{ route('public.care-map.profile', ['id' => $fac->id]) }}"
                           class="map-pin"
                           style="top:{{ round($top, 1) }}%;left:{{ round($left, 1) }}%;background:{{ $typeColor }};box-shadow:0 0 10px {{ $typeColor }};"
                           title="{{ $fac->facility_name }} — {{ ucfirst(str_replace('_', ' ', $fac->facility_type)) }}">
                        </a>
                    @endforeach
                @endif
            </div>

            <!-- Pin colour legend -->
            <div style="position:absolute;top:4mm;right:4mm;background:rgba(11,17,30,0.88);border:1px solid rgba(255,255,255,0.08);border-radius:8px;padding:3mm 4mm;font-size:10.5px;color:#94A3B8;backdrop-filter:blur(8px);z-index:5;">
                <div style="font-weight:700;color:#E2E8F0;margin-bottom:2mm;">{{ $locale === 'fr' ? 'Légende' : 'Legend' }}</div>
                @foreach([
                    ['color'=>'#0DF2C9', 'en'=>'Hospital',   'fr'=>'Hôpital'],
                    ['color'=>'#F59E0B', 'en'=>'Pharmacy',   'fr'=>'Pharmacie'],
                    ['color'=>'#818CF8', 'en'=>'Laboratory', 'fr'=>'Laboratoire'],
                    ['color'=>'#EF4444', 'en'=>'Blood Bank', 'fr'=>'Banque de Sang'],
                    ['color'=>'#34D399', 'en'=>'Clinic',     'fr'=>'Clinique'],
                    ['color'=>'#94A3B8', 'en'=>'Other',      'fr'=>'Autre'],
                ] as $leg)
                    <div style="display:flex;align-items:center;gap:2mm;margin-bottom:1.5mm;">
                        <div style="width:9px;height:9px;border-radius:50%;background:{{ $leg['color'] }};flex-shrink:0;"></div>
                        <span>{{ $leg[$locale] ?? $leg['en'] }}</span>
                    </div>
                @endforeach
            </div>

            <div class="safety-bar">
                <div>
                    <strong>🛡️ {{ $locale === 'fr' ? 'Clause de non-responsabilité :' : 'Clinical Safety Disclaimer :' }}</strong>
                    @if($locale === ‘fr’)La disponibilité indiquée est purement informative et ne constitue pas une garantie de stock ou de traitement. Veuillez appeler l’établissement avant de vous déplacer.@else Information may change. Please contact the facility before travelling or making medical decisions.@endif
                </div>
            </div>
        </div>
    </div>

</body>
</html>
