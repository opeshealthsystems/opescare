# OpesCare — Laragon Local Development Setup

Quick-start guide for running the OpesCare platform locally with Laragon on Windows.

---

## Prerequisites

| Tool | Version | Notes |
|------|---------|-------|
| Laragon | 6.x | Full edition (includes Apache/Nginx, MySQL/Postgres, PHP) |
| PHP | 8.3.x | `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe` |
| PostgreSQL | 15+ | Preferred over MySQL; set in Laragon preferences |
| Node.js | 20+ | For frontend asset compilation |
| Composer | 2.x | Bundled or standalone |
| Python | 3.11+ | Required for Bridge Agent only |

---

## Repository Layout

```
opescare/                        ← repo root (one git repo)
├── VERSION                      ← platform version (single source of truth)
├── CHANGELOG.md                 ← platform-level changelog
├── LARAGON_SETUP.md             ← this file
├── apps/
│   ├── api-laravel/             ← Laravel 13 API + web portals
│   └── mobile-expo/             ← Expo / React Native patient app
├── sdk/
│   ├── php/                     ← Connect Suite PHP SDK
│   ├── python/                  ← Connect Suite Python SDK
│   └── typescript/              ← Connect Suite TypeScript SDK
├── bridge-agent/                ← Python bridge service
├── docs/                        ← platform documentation
├── contracts/                   ← API contracts / OpenAPI specs
├── upgradeplans/                ← migration and upgrade plans
└── widget/                      ← embeddable JS widget
```

### What is NOT tracked in git

The following artifact directories are excluded by `.gitignore` and should
never be committed:

| Path | Why excluded |
|------|-------------|
| `apps/api-laravel/vendor/` | Composer dependencies — run `composer install` |
| `apps/api-laravel/node_modules/` | npm dependencies — run `npm install` |
| `apps/api-laravel/public/build/` | Vite build output — run `npm run build` |
| `apps/api-laravel/storage/logs/` | Runtime logs |
| `apps/api-laravel/storage/framework/cache/` | Framework cache |
| `apps/api-laravel/storage/framework/sessions/` | Session files |
| `apps/api-laravel/.env` | Environment secrets — copy from `.env.example` |
| `apps/mobile-expo/node_modules/` | npm dependencies — run `npm install` |
| `apps/mobile-expo/.expo/` | Expo local build/dev cache |
| `bridge-agent/venv/` | Python virtualenv |

---

## First-Time Setup

### 1. Clone and position

```
C:\laragon\www\opescare\   ← Laragon auto-discovers this as opescare.test
```

Laragon's virtual host will serve `apps/api-laravel/public/` — set the
document root in Laragon's Apache/Nginx config or `laragon.conf`.

### 2. Laravel API

```powershell
cd apps\api-laravel
composer install
cp .env.example .env
# Edit .env — set DB_CONNECTION=pgsql and your Postgres credentials
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
```

### 3. Environment (.env) essentials

```env
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=opescare
DB_USERNAME=postgres
DB_PASSWORD=your_password

# Payments (MTN MoMo + Orange Money)
MOMO_API_KEY=
ORANGE_MONEY_KEY=

# FHIR endpoint (optional for local)
FHIR_BASE_URL=
```

### 4. Bridge Agent (optional)

```powershell
cd bridge-agent
python -m venv venv
venv\Scripts\activate
pip install -e .
cp bridge_config.example.json config.json
# Edit config.json with your local API URL and credentials
python -m opescare_bridge
```

### 5. Expo patient app (optional)

```powershell
cd apps\mobile-expo
npm install
npx expo start
```

Device builds go through EAS — see `apps/mobile-expo/eas.json` for the
`local-api` / `preview` / `production` profiles.

---

## PHP Binary

Always use the exact Laragon PHP binary for CLI commands:

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe
```

Add it to your PATH or prefix artisan calls:

```powershell
& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" artisan migrate
```

---

## Test Database

Tests use a dedicated Postgres test database (not SQLite) to maintain
FK constraint parity with production:

```env
# In .env.testing
DB_DATABASE=opescare_test
```

Run tests:
```powershell
cd apps\api-laravel
php artisan test
```

---

## Platform Version

The current platform version is in `VERSION` at the repo root.
When releasing, update `VERSION` and add an entry to `CHANGELOG.md`.
