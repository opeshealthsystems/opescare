<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Legal Centre — OpesCare</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
    <style>
        body { background:#f8fafc; font-family:'Inter',sans-serif; margin:0; }
        .legal-header { background:#0F4C81; color:#fff; padding:32px 24px; text-align:center; }
        .legal-header h1 { font-size:1.8rem; font-weight:800; margin:0 0 8px; }
        .legal-header p  { margin:0; font-size:0.95rem; opacity:.8; }
        .legal-container { max-width:900px; margin:0 auto; padding:32px 24px; }
        .legal-card {
            background:#fff; border:1px solid #e2e8f0; border-radius:12px;
            padding:20px 24px; margin-bottom:12px;
            display:flex; align-items:center; justify-content:space-between;
            text-decoration:none; color:#1e293b;
            transition:box-shadow .15s;
        }
        .legal-card:hover { box-shadow:0 4px 12px rgba(0,0,0,.08); }
        .legal-card__title { font-weight:700; font-size:0.95rem; }
        .legal-card__meta  { font-size:0.78rem; color:#64748b; margin-top:3px; }
        .legal-card__arrow { color:#7c3aed; font-size:1.2rem; }
        .legal-footer { text-align:center; padding:32px 24px; font-size:0.83rem; color:#94a3b8; }
    </style>
</head>
<body>
<div class="legal-header">
    <h1>OpesCare Legal Centre</h1>
    <p>Our policies, terms, and commitment to your privacy</p>
</div>

<div class="legal-container">
    <p style="font-size:0.88rem;color:#64748b;margin-bottom:24px;">
        The following legal documents govern the use of OpesCare services. By using our platform,
        you agree to the applicable terms. Always review the current version of each document.
    </p>

    @forelse($documents as $doc)
        @php $ver = $doc->versions->first(); @endphp
        <a href="{{ route('public.legal.show', $doc->slug) }}" class="legal-card">
            <div>
                <div class="legal-card__title">{{ $doc->title }}</div>
                <div class="legal-card__meta">
                    @if($ver)
                        Version {{ $ver->version }}
                        @if($ver->effective_at)
                            · Effective {{ $ver->effective_at->format('d F Y') }}
                        @endif
                    @else
                        No current version
                    @endif
                </div>
            </div>
            <span class="legal-card__arrow">→</span>
        </a>
    @empty
        <div style="text-align:center;padding:40px;color:#94a3b8;">
            No legal documents published yet.
        </div>
    @endforelse

    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:16px 20px;margin-top:24px;font-size:0.85rem;color:#1e3a8a;">
        <strong>Questions?</strong>  Contact our privacy team at
        <a href="mailto:privacy@opescare.com" style="color:#1d4ed8;">privacy@opescare.com</a>
        or visit our <a href="/support" style="color:#1d4ed8;">Help Centre</a>.
    </div>
</div>

<div class="legal-footer">
    © {{ date('Y') }} OpesCare. All rights reserved.
    <a href="/" style="color:#94a3b8;margin-left:12px;">← Back to Portal</a>
</div>
</body>
</html>
