@push('head')
<style>
.nav-section-label{font-size:.65rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--p-text-muted,rgba(255,255,255,.45));padding:.75rem 1rem .25rem;margin-top:.25rem}
.nav-section-label:first-child{margin-top:0}
</style>
@endpush

<nav class="sidebar-nav">

  {{-- Platform --}}
  <div class="nav-section-label">Platform</div>
  <a href="{{ route('portals.admin') }}" class="nav-item {{ request()->routeIs('portals.admin') && !request()->routeIs('portals.admin.*') ? 'active' : '' }}">
    <i data-lucide="layout-dashboard"></i><span>Dashboard</span>
  </a>
  <a href="{{ route('portals.admin.kpi.index') }}" class="nav-item {{ request()->routeIs('portals.admin.kpi.*') ? 'active' : '' }}">
    <i data-lucide="bar-chart-2"></i><span>KPI & Analytics</span>
  </a>
  <a href="{{ route('portals.admin.onboarding') }}" class="nav-item {{ request()->routeIs('portals.admin.onboarding*') ? 'active' : '' }}">
    <i data-lucide="rocket"></i><span>Onboarding</span>
  </a>
  <a href="{{ route('portals.admin.go-live') }}" class="nav-item {{ request()->routeIs('portals.admin.go-live*') ? 'active' : '' }}">
    <i data-lucide="check-circle-2"></i><span>Go-Live Readiness</span>
  </a>

  {{-- Organizations --}}
  <div class="nav-section-label">Organizations</div>
  <a href="{{ route('portals.admin.organizations.index') }}" class="nav-item {{ request()->routeIs('portals.admin.organizations.*') ? 'active' : '' }}">
    <i data-lucide="building-2"></i><span>Organizations</span>
  </a>
  <a href="{{ route('portals.admin.facilities.index') }}" class="nav-item {{ request()->routeIs('portals.admin.facilities.*') ? 'active' : '' }}">
    <i data-lucide="hospital"></i><span>Facilities</span>
  </a>
  <a href="{{ route('portals.admin.subscription') }}" class="nav-item {{ request()->routeIs('portals.admin.subscription*') ? 'active' : '' }}">
    <i data-lucide="credit-card"></i><span>Subscriptions</span>
  </a>
  <a href="{{ route('portals.admin.financial.index') }}" class="nav-item {{ request()->routeIs('portals.admin.financial.*') ? 'active' : '' }}">
    <i data-lucide="banknote"></i><span>Finance</span>
  </a>

  {{-- People --}}
  <div class="nav-section-label">People</div>
  <a href="{{ route('portals.admin.users.index') }}" class="nav-item {{ request()->routeIs('portals.admin.users.*') ? 'active' : '' }}">
    <i data-lucide="users"></i><span>Users</span>
  </a>
  <a href="{{ route('portals.admin.roles.index') }}" class="nav-item {{ request()->routeIs('portals.admin.roles.*') ? 'active' : '' }}">
    <i data-lucide="shield-check"></i><span>Roles</span>
  </a>
  <a href="{{ route('portals.admin.staff.index') }}" class="nav-item {{ request()->routeIs('portals.admin.staff.*') ? 'active' : '' }}">
    <i data-lucide="stethoscope"></i><span>Staff</span>
  </a>
  <a href="{{ route('portals.admin.patients.index') }}" class="nav-item {{ request()->routeIs('portals.admin.patients.*') ? 'active' : '' }}">
    <i data-lucide="heart-pulse"></i><span>Patients</span>
  </a>

  {{-- Clinical --}}
  <div class="nav-section-label">Clinical</div>
  <a href="{{ route('portals.admin.cdss.index') }}" class="nav-item {{ request()->routeIs('portals.admin.cdss.*') ? 'active' : '' }}">
    <i data-lucide="brain"></i><span>CDSS Rules</span>
  </a>
  <a href="{{ route('portals.admin.code_mappings.index') }}" class="nav-item {{ request()->routeIs('portals.admin.code_mappings.*') ? 'active' : '' }}">
    <i data-lucide="tags"></i><span>Code Mappings</span>
  </a>
  <a href="{{ route('portals.admin.certifications.index') }}" class="nav-item {{ request()->routeIs('portals.admin.certifications.*') ? 'active' : '' }}">
    <i data-lucide="award"></i><span>Certifications</span>
  </a>

  {{-- Integration --}}
  <div class="nav-section-label">Integration</div>
  <a href="{{ route('portals.admin.connect') }}" class="nav-item {{ request()->routeIs('portals.admin.connect*') ? 'active' : '' }}">
    <i data-lucide="plug"></i><span>Connect / API</span>
  </a>
  <a href="{{ route('portals.admin.bridge') }}" class="nav-item {{ request()->routeIs('portals.admin.bridge*') ? 'active' : '' }}">
    <i data-lucide="git-branch"></i><span>Bridge</span>
  </a>
  <a href="{{ route('portals.admin.developer.accounts') }}" class="nav-item {{ request()->routeIs('portals.admin.developer.*') ? 'active' : '' }}">
    <i data-lucide="code-2"></i><span>Developers</span>
  </a>

  {{-- Compliance --}}
  <div class="nav-section-label">Compliance</div>
  <a href="{{ route('portals.admin.security') }}" class="nav-item {{ request()->routeIs('portals.admin.security*') ? 'active' : '' }}">
    <i data-lucide="shield"></i><span>Security Ops</span>
  </a>
  <a href="{{ route('portals.admin.legal') }}" class="nav-item {{ request()->routeIs('portals.admin.legal*') ? 'active' : '' }}">
    <i data-lucide="scale"></i><span>Legal</span>
  </a>
  <a href="{{ route('portals.admin.reports.minsante-monthly') }}" class="nav-item {{ request()->routeIs('portals.admin.reports.*') ? 'active' : '' }}">
    <i data-lucide="file-bar-chart"></i><span>Reports</span>
  </a>

  {{-- System --}}
  <div class="nav-section-label">System</div>
  <a href="{{ route('portals.admin.cc') }}" class="nav-item {{ request()->routeIs('portals.admin.cc*') ? 'active' : '' }}">
    <i data-lucide="server"></i><span>Control Center</span>
  </a>
  <a href="{{ route('portals.admin.support.index') }}" class="nav-item {{ request()->routeIs('portals.admin.support.*') ? 'active' : '' }}">
    <i data-lucide="life-buoy"></i><span>Support</span>
  </a>

</nav>
