<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>OpesCare Academy — Facility Readiness Cockpit</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700&display=swap" rel="stylesheet">
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js" defer></script>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --brand:#0F4C81;--brand-hover:#0a3560;--brand-subtle:#EFF6FF;
  --bg:#F1F5F9;--surface:#fff;--border:#E2E8F0;
  --text-primary:#0F172A;--text-secondary:#475569;--text-muted:#94A3B8;
  --radius:0.5rem;--radius-lg:0.75rem;--radius-xl:1rem;
  --shadow-sm:0 1px 3px rgba(0,0,0,.07);--shadow:0 4px 12px rgba(0,0,0,.08);
}
html,body{font-family:'Inter',system-ui,sans-serif;background:var(--bg);color:var(--text-primary);-webkit-font-smoothing:antialiased}

.page-topbar{background:#0A1628;border-bottom:1px solid rgba(255,255,255,.08);padding:0 1.5rem;height:60px;display:flex;align-items:center;gap:1rem}
.page-topbar-logo{display:flex;align-items:center;gap:.625rem;text-decoration:none}
.page-topbar-mark{width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,#0F4C81,#0a3560);display:flex;align-items:center;justify-content:center}
.page-topbar-mark i{color:#fff;width:16px;height:16px}
.page-topbar-name{font-family:'Outfit',sans-serif;font-size:1rem;font-weight:700;color:#fff}
.page-topbar-tag{display:block;font-size:.52rem;color:rgba(255,255,255,.4);font-weight:500;text-transform:uppercase;letter-spacing:.07em}
.page-topbar-spacer{flex:1}
.page-topbar-user{font-size:.78rem;color:rgba(255,255,255,.5)}

.cm-header{background:linear-gradient(135deg,#0F4C81,#0a3560);color:#fff;padding:2rem 1.5rem;border-radius:0 0 20px 20px;margin-bottom:1.5rem}
.cm-header h1{font-size:1.5rem;font-weight:700;margin:0 0 .375rem}
.cm-header p{opacity:.8;margin:0;font-size:.9rem}

.page-wrap{max-width:1050px;margin:0 auto;padding:0 1.25rem 3rem}

.disclaimer-note{background:var(--brand-subtle);border:1.5px solid #BFDBFE;border-radius:var(--radius-lg);padding:.875rem 1rem;font-size:.78rem;color:#1e40af;line-height:1.55;margin-bottom:1.5rem;display:flex;gap:.625rem}
.disclaimer-note i{width:16px;height:16px;flex-shrink:0;margin-top:1px;color:var(--brand)}

.stat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem}
.stat-card{background:var(--surface);border:1.5px solid var(--border);border-radius:var(--radius-xl);padding:1.25rem;text-align:center}
.stat-card__value{font-family:'Outfit',sans-serif;font-size:2.25rem;font-weight:700;color:var(--brand);margin-bottom:.25rem}
.stat-card__label{font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;font-weight:600}

.readiness-section{background:var(--surface);border:1.5px solid var(--border);border-radius:var(--radius-xl);padding:1.25rem;margin-bottom:1rem}
.readiness-section h3{font-size:.9375rem;font-weight:700;color:var(--text-primary);margin:0 0 .875rem}

.check-row{display:flex;align-items:center;gap:.75rem;padding:.5rem 0;border-bottom:1px solid #f1f5f9}
.check-row:last-child{border-bottom:none}
.check-row i.pass{color:#16a34a}.check-row i.fail{color:#ef4444}.check-row i.warn{color:#d97706}
.check-row__label{flex:1;font-size:.8125rem;color:var(--text-secondary)}
.check-row i{width:16px;height:16px;flex-shrink:0}

.readiness-body{font-size:.8125rem;color:var(--text-secondary);line-height:1.65}

@media(max-width:640px){.stat-grid{grid-template-columns:1fr 1fr}}
</style>
</head>
<body>

<header class="page-topbar">
  <a href="#" class="page-topbar-logo">
    <div class="page-topbar-mark"><i data-lucide="graduation-cap"></i></div>
    <div>
      <span class="page-topbar-name">OpesCare Academy</span>
      <span class="page-topbar-tag">Facility Readiness</span>
    </div>
  </a>
  <div class="page-topbar-spacer"></div>
  <span class="page-topbar-user">Facility ID: {{ $facilityId }}</span>
</header>

<div class="cm-header">
  <h1>Facility Readiness &amp; Competency Cockpit</h1>
  <p>Real-time training progress and digital workflow compliance for Facility {{ $facilityId }}</p>
</div>

<div class="page-wrap">

  <div class="disclaimer-note">
    <i data-lucide="info"></i>
    <div>
      <strong>EN:</strong> This certification confirms completion of OpesCare digital health workflow training. It does not replace professional licensing, clinical qualification, statutory registration, or authorization to practice a regulated health profession.<br>
      <strong>FR:</strong> Ce certificat confirme l'achèvement d'une formation aux flux de travail numériques d'OpesCare. Il ne remplace pas l'autorisation professionnelle, la qualification clinique, l'inscription réglementaire ni le droit d'exercer une profession de santé réglementée.
    </div>
  </div>

  <div class="stat-grid">
    <div class="stat-card">
      <div class="stat-card__value">{{ $result['readiness_percentage'] ?? 0 }}%</div>
      <div class="stat-card__label">Digital Health Readiness</div>
    </div>
    <div class="stat-card">
      <div class="stat-card__value">{{ $result['total_staff'] ?? 0 }}</div>
      <div class="stat-card__label">Total Facility Staff</div>
    </div>
    <div class="stat-card">
      <div class="stat-card__value">{{ $result['fully_certified_staff'] ?? 0 }}</div>
      <div class="stat-card__label">Fully Certified Staff</div>
    </div>
    <div class="stat-card">
      <div class="stat-card__value">{{ $result['active_learners'] ?? 0 }}</div>
      <div class="stat-card__label">Active Learners</div>
    </div>
  </div>

  <div class="readiness-section">
    <h3>Institutional Readiness Actions</h3>
    <div class="readiness-body">
      This cockpit tracks real-time training progress and digital workflow competencies across all active staff members inside
      Facility <strong>{{ $facilityId }}</strong>. Staff members must successfully pass core modules in order to pull clinical
      summaries or complete advanced workflows, ensuring maximum safety, interoperability, and compliance under regional directives.
    </div>
  </div>

  @if(!empty($result['readiness_checks']) && is_array($result['readiness_checks']))
  <div class="readiness-section">
    <h3>Readiness Checklist</h3>
    @foreach($result['readiness_checks'] as $check)
      @php
        $status = $check['status'] ?? 'warn';
        $iconClass = $status === 'pass' ? 'pass' : ($status === 'fail' ? 'fail' : 'warn');
        $iconName  = $status === 'pass' ? 'circle-check' : ($status === 'fail' ? 'circle-x' : 'triangle-alert');
      @endphp
      <div class="check-row">
        <i data-lucide="{{ $iconName }}" class="{{ $iconClass }}"></i>
        <span class="check-row__label">{{ $check['label'] ?? '' }}</span>
      </div>
    @endforeach
  </div>
  @endif

</div>

<script>
window.addEventListener('load', () => { if (window.lucide) lucide.createIcons(); });
</script>
</body>
</html>
