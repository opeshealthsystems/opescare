# OpesCare — CloudPanel Production Deploy (clean payload)

**Target:** Hostinger VPS (Ubuntu 22.04) + CloudPanel · domain `opescare.com`
**Principle:** ship **only the Laravel runtime** (`apps/api-laravel`, minus dev files).
Nothing dev-only, nothing secret, and nothing outside `public/` is ever web-served.

> Run as a sudo user. Replace `<...>` placeholders. **Never paste secrets into chat or commits** — type them directly on the server.

---

## 0. SECURITY FIRST (do before anything else)

Your root + panel passwords were exposed in chat — rotate them now.

```bash
# New root password
passwd

# Add your SSH public key, then harden SSH
mkdir -p ~/.ssh && nano ~/.ssh/authorized_keys     # paste your PUBLIC key
chmod 700 ~/.ssh && chmod 600 ~/.ssh/authorized_keys
sed -i 's/^#\?PasswordAuthentication.*/PasswordAuthentication no/' /etc/ssh/sshd_config
sed -i 's/^#\?PermitRootLogin.*/PermitRootLogin prohibit-password/' /etc/ssh/sshd_config
systemctl restart ssh
```
- Change the **CloudPanel admin password** in the panel UI.
- Firewall: allow only 22 (or your SSH port), 80, 443, and the CloudPanel port (8443) — ideally restrict 8443 to your IP.

---

## 1. Server packages (PostgreSQL 15 + Redis 7)

CloudPanel already provides Nginx + PHP 8.3. Add the data stores:

```bash
apt update
apt install -y postgresql-15 postgresql-contrib redis-server unzip git
systemctl enable --now postgresql redis-server

# DB + user (set a strong password; you'll put it in .env later)
sudo -u postgres psql -c "CREATE DATABASE opescare;"
sudo -u postgres psql -c "CREATE USER opescare WITH ENCRYPTED PASSWORD '<DB_PASSWORD>';"
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE opescare TO opescare;"
sudo -u postgres psql -d opescare -c "GRANT ALL ON SCHEMA public TO opescare;"
```

Ensure these PHP 8.3 extensions are enabled (CloudPanel → site → PHP, or `apt`):
`pdo_pgsql pgsql redis mbstring bcmath gd intl zip curl openssl`.

---

## 2. Create the CloudPanel site

In CloudPanel → **Add Site → PHP site**:
- Domain: `app.opescare.com` (primary)
- PHP: **8.3**
- **Document root must be the `public/` subfolder** (see step 4) — CloudPanel sets `.../htdocs/<domain>`; we deploy the app so its `public/` is the web root.

---

## 3. Build a CLEAN deploy payload (only runtime files)

Do this on the server in a scratch build dir. `git archive` respects the
`.gitattributes export-ignore` rules, so tests, IDE files, `.env.example`,
README, `.git`, and the other monorepo apps are **never** included.

```bash
cd /tmp
rm -rf opes-build && git clone --depth 1 https://github.com/opeshealthsystems/opescare.git opes-build
cd opes-build

# Export ONLY apps/api-laravel runtime files (no .git, tests, dev configs, other apps)
mkdir -p /tmp/opes-release
git archive HEAD:apps/api-laravel | tar -x -C /tmp/opes-release
```
`/tmp/opes-release` now contains the Laravel app and nothing else dev-only.

> Private repo: use a **read-only deploy key** for the clone. Generate on the server
> (`ssh-keygen -t ed25519 -f ~/.ssh/opes_deploy -N ""`), add the **public** key as a
> Deploy Key in GitHub repo settings, and clone via `git@github.com:...` with that key.

---

## 4. Place the app + install runtime dependencies

```bash
SITE=/home/<cloudpanel-site-user>/htdocs/app.opescare.com   # adjust to your site path
# Move app into place (keep public/ as the web root)
rsync -a --delete /tmp/opes-release/ "$SITE/app/"
# Point the site's document root at the app's public/ folder (CloudPanel → site → Root: .../app/public)

cd "$SITE/app"
composer install --no-dev --optimize-autoloader --no-interaction

# Frontend assets (Vite) — build, then DROP node_modules (not needed at runtime)
npm ci
npm run build            # → public/build
rm -rf node_modules
```

---

## 5. Environment (.env) — secrets typed on the server only

```bash
cp .env.example .env
nano .env
```
Set at minimum:
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://app.opescare.com
LOG_LEVEL=warning
SESSION_SECURE_COOKIE=true

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=opescare
DB_USERNAME=opescare
DB_PASSWORD=<DB_PASSWORD>

REDIS_HOST=127.0.0.1
QUEUE_CONNECTION=redis
CACHE_STORE=database          # or redis
SESSION_DRIVER=database

# Demo OFF in production
OPESCARE_DEMO_MODE=false
OPESCARE_PUBLIC_DEMO_MODE=false
OPESCARE_INTERNAL_DEMO_MODE=false

# Cameroon services (fill real values)
MTN_MOMO_* / ORANGE_MONEY_* / WHATSAPP_* / DHIS2_*  ...
MOBILE_STORE_URL=https://play.google.com/store/apps/details?id=com.opescare.patient
AUDIT_ARCHIVE_DISK=s3         # WORM bucket in prod (see config/audit.php)
```

```bash
php artisan key:generate      # ⚠️ generate ONCE. Never change after PII is encrypted.
```

---

## 6. Migrate + seed ONLY production data

```bash
php artisan migrate --force

# Production seeders ONLY — never the Demo* seeders
php artisan db:seed --class=RolesSeeder --force
php artisan db:seed --class=ProductionAccountsSeeder --force
php artisan db:seed --class=CameroonFacilityRegistrySeeder --force
php artisan db:seed --class=CameroonInsurancePlansSeeder --force
```

---

## 7. Optimize + permissions + links

```bash
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Web user (CloudPanel runs PHP-FPM as the site user) must own writable dirs
chown -R <site-user>:<site-user> storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache
```

---

## 8. Queue worker + scheduler

Queue worker (Supervisor):
```ini
# /etc/supervisor/conf.d/opescare-worker.conf
[program:opescare-worker]
command=php /home/<site-user>/htdocs/app.opescare.com/app/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
user=<site-user>
autostart=true
autorestart=true
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/opescare-worker.log
```
```bash
supervisorctl reread && supervisorctl update && supervisorctl start opescare-worker:*
```

Scheduler (cron):
```cron
* * * * * cd /home/<site-user>/htdocs/app.opescare.com/app && php artisan schedule:run >> /dev/null 2>&1
```

---

## 9. Subdomains + wildcard SSL

- DNS: 14 records → the VPS IP (or a `*` wildcard A record + apex). Subdomains:
  `app api connect fhir mobile-api lite academy developer docs caremap bridge public-health status ussd` (all `.opescare.com`).
- In CloudPanel, add each subdomain (or one site with the Nginx blocks from
  `DEPLOYMENT.md §5`), all pointing document root at the same `app/public`.
- Wildcard cert (CloudPanel → SSL, DNS-01) or:
  ```bash
  certbot certonly --manual --preferred-challenges dns -d opescare.com -d "*.opescare.com"
  ```

---

## 10. "Nothing that shouldn't be online" — verify

- **Web root is `public/` only.** The app code, `.env`, `vendor/`, `storage/`,
  `config/` all sit ABOVE the web root and are never URL-accessible.
- Deny dotfiles in Nginx (CloudPanel usually does): `location ~ /\.(?!well-known) { deny all; }`
- Confirm dev files are absent (they were export-ignored): no `tests/`, no
  `.env.example`, no `README.md`, no `.git/` in the deployed `app/`.
- Smoke test:
  ```bash
  curl -I https://app.opescare.com/login            # 200, no Server/X-Powered-By
  curl -s https://app.opescare.com/api/mobile/app-config   # version-gate JSON
  curl -I https://app.opescare.com/.env             # MUST be 403/404
  ```

---

## What is deployed vs NOT

| Deployed (runtime) | NOT deployed |
|---|---|
| `app/ bootstrap/ config/ database/migrations+seeders/ lang/ public/ resources/views routes/ vendor/(--no-dev) public/build/` | `tests/ .git/ node_modules/ .env.example README.md phpunit.xml .editorconfig docs/ .github/ other monorepo apps (sdk, widget, bridge-agent, contracts, upgradeplans) the Flutter app` |

The Flutter mobile app and SDK/widget are **separate deliverables** — they are not
part of the API server deploy.
