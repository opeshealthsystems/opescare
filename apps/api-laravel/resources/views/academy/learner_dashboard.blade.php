<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>OpesCare Academy — Learner Dashboard</title>
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

.page-wrap{max-width:1050px;margin:0 auto;padding:1.5rem 1.25rem 3rem}

.cm-header{background:linear-gradient(135deg,#0F4C81,#0a3560);color:#fff;padding:2rem 1.5rem;border-radius:0 0 20px 20px;margin-bottom:1.5rem}
.cm-header h1{font-size:1.5rem;font-weight:700;margin:0 0 .375rem}
.cm-header p{opacity:.8;margin:0;font-size:.9rem}

.disclaimer-note{background:var(--brand-subtle);border:1.5px solid #BFDBFE;border-radius:var(--radius-lg);padding:.875rem 1rem;font-size:.78rem;color:#1e40af;line-height:1.55;margin-bottom:1.5rem;display:flex;gap:.625rem}
.disclaimer-note i{width:16px;height:16px;flex-shrink:0;margin-top:1px;color:var(--brand)}

.content-grid{display:grid;grid-template-columns:2fr 1fr;gap:1.25rem}

.section-label{font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:.875rem}

.course-card{background:var(--surface);border:1.5px solid var(--border);border-radius:var(--radius-xl);padding:1.25rem;margin-bottom:.875rem;transition:border-color .15s,box-shadow .15s}
.course-card:hover{border-color:var(--brand);box-shadow:0 4px 12px rgba(15,76,129,.1)}
.course-card__top{display:flex;justify-content:space-between;align-items:flex-start;gap:.75rem;margin-bottom:.5rem}
.course-card__title{font-weight:700;font-size:.9375rem;color:var(--text-primary);flex:1}
.course-card__pct{font-size:.875rem;font-weight:700;color:var(--brand)}
.course-card__desc{font-size:.8125rem;color:var(--text-secondary);margin-bottom:.75rem;line-height:1.5}
.progress-bar-wrap{background:rgba(255,255,255,.2);border-radius:999px;height:8px;overflow:hidden;margin-bottom:.5rem;background:#E2E8F0}
.progress-bar-fill{height:100%;background:linear-gradient(90deg,#0F4C81,#1a6cb5);border-radius:999px}
.course-card__meta{display:flex;justify-content:space-between;font-size:.75rem;color:var(--text-muted)}

.creds-panel{background:var(--surface);border:1.5px solid var(--border);border-radius:var(--radius-xl);overflow:hidden}
.creds-panel__head{padding:.875rem 1rem;border-bottom:1px solid var(--border);font-size:.875rem;font-weight:700;color:var(--text-primary)}
.creds-panel__body{padding:.875rem 1rem}
.cert-row{display:flex;align-items:center;justify-content:space-between;padding:.625rem 0;border-bottom:1px solid var(--border);gap:.5rem}
.cert-row:last-child{border-bottom:none}
.cert-row__name{font-size:.8125rem;font-weight:600;color:var(--text-primary)}
.cert-row__num{font-family:monospace;font-size:.7rem;color:var(--text-muted);margin-top:.125rem}
.badge-cert{display:inline-flex;align-items:center;padding:.15rem .5rem;border-radius:999px;font-size:.68rem;font-weight:700;background:#ECFDF5;color:#059669;border:1px solid #A7F3D0}
.empty-row{text-align:center;padding:2rem 1rem;font-size:.82rem;color:var(--text-muted)}

.course-card--empty{background:var(--surface);border:1.5px solid var(--border);border-radius:var(--radius-xl);padding:2rem;text-align:center;color:var(--text-muted);font-size:.82rem}

@media(max-width:768px){.content-grid{grid-template-columns:1fr}}
</style>
</head>
<body>

<header class="page-topbar">
  <a href="#" class="page-topbar-logo">
    <div class="page-topbar-mark"><i data-lucide="graduation-cap"></i></div>
    <div>
      <span class="page-topbar-name">OpesCare Academy</span>
      <span class="page-topbar-tag">Learner Console</span>
    </div>
  </a>
  <div class="page-topbar-spacer"></div>
  <span class="page-topbar-user">Certified Professional</span>
</header>

<div class="cm-header">
  <h1>My Learning Dashboard</h1>
  <p>Track your training progress and digital health competencies</p>
</div>

<div class="page-wrap">

  <div class="disclaimer-note">
    <i data-lucide="info"></i>
    <div>
      <strong>EN:</strong> This certification confirms completion of OpesCare digital health workflow training. It does not replace professional licensing, clinical qualification, statutory registration, or authorization to practice a regulated health profession.<br>
      <strong>FR:</strong> Ce certificat confirme l'achèvement d'une formation aux flux de travail numériques d'OpesCare. Il ne remplace pas l'autorisation professionnelle, la qualification clinique, l'inscription réglementaire ni le droit d'exercer une profession de santé réglementée.
    </div>
  </div>

  <div class="content-grid">

    <div>
      <div class="section-label">My Active Training Tracks</div>
      @forelse($enrollments as $enrollment)
        <div class="course-card">
          <div class="course-card__top">
            <span class="course-card__title">{{ $enrollment->course->title_en }}</span>
            <span class="course-card__pct">{{ $enrollment->progress_percentage }}%</span>
          </div>
          <div class="course-card__desc">{{ $enrollment->course->description_en }}</div>
          <div class="progress-bar-wrap">
            <div class="progress-bar-fill" style="width:{{ $enrollment->progress_percentage }}%"></div>
          </div>
          <div class="course-card__meta">
            <span>Code: {{ $enrollment->course->course_code }}</span>
            <span>Status: {{ strtoupper($enrollment->status) }}</span>
          </div>
        </div>
      @empty
        <div class="course-card--empty">You are not currently enrolled in any active training tracks.</div>
      @endforelse
    </div>

    <div>
      <div class="section-label">My Issued Credentials</div>
      <div class="creds-panel">
        <div class="creds-panel__head">Digital Certificates</div>
        <div class="creds-panel__body">
          @forelse($certificates as $cert)
            <div class="cert-row">
              <div>
                <div class="cert-row__name">{{ $cert->course->title_en }}</div>
                <div class="cert-row__num">{{ $cert->certificate_number }}</div>
              </div>
              <span class="badge-cert">{{ strtoupper($cert->status) }}</span>
            </div>
          @empty
            <div class="empty-row">No certificates issued yet.</div>
          @endforelse
        </div>
      </div>
    </div>

  </div>
</div>

<script>
window.addEventListener('load', () => { if (window.lucide) lucide.createIcons(); });
</script>
</body>
</html>
