# OpesCare — Deployment Documentation

> Version: 1.0 | Platform: Laravel 11 / PHP 8.3 / PostgreSQL
> This document covers single-server deployment, subdomain routing, SSL, and the
> path to microservices. Read it before provisioning any production server.

---

## Table of Contents

1. [Architecture Overview](#1-architecture-overview)
2. [File Structure — One Codebase, One Folder](#2-file-structure)
3. [Subdomain Map](#3-subdomain-map)
4. [Server Requirements](#4-server-requirements)
5. [Nginx Configuration — All Subdomains](#5-nginx-configuration)
6. [SSL Certificates (Let's Encrypt / Wildcard)](#6-ssl-certificates)
7. [Environment Variables](#7-environment-variables)
8. [Database Setup](#8-database-setup)
9. [First Deploy Checklist](#9-first-deploy-checklist)
10. [Enabling Subdomain Routing](#10-enabling-subdomain-routing)
11. [Scaling Path — Monolith to Microservices](#11-scaling-path)
12. [OpesCare Lite — Standalone Deployment Options](#12-opescare-lite-standalone)

---

## 1. Architecture Overview

OpesCare is currently a **Laravel monolith** — one codebase serving all services.
All subdomains point to the **same public/ folder** on the same server.
Nginx handles subdomain routing; Laravel handles application routing.

```
                         ┌─────────────────────────────────┐
  app.opescare.com       │                                 │
  api.opescare.com       │         NGINX (reverse proxy)   │
  connect.opescare.com   │         + SSL termination        │
  fhir.opescare.com   ──▶│         + rate limiting          │
  mobile-api.opescare.com│         + subdomain routing      │
  lite.opescare.com      │                                 │
  academy.opescare.com   └──────────────┬──────────────────┘
  developer.opescare.com                │
  docs.opescare.com                     ▼
  caremap.opescare.com   ┌─────────────────────────────────┐
  bridge.opescare.com    │  PHP-FPM / Laravel Application  │
  public-health.opescare │  /var/www/opescare/public/      │
  ussd.opescare.com      │  (ONE codebase, ONE folder)     │
  status.opescare.com    └──────────────┬──────────────────┘
                                        │
                         ┌──────────────▼──────────────────┐
                         │   PostgreSQL Database           │
                         │   Redis (cache + queues)        │
                         └─────────────────────────────────┘
```

**Key principle:** Subdomains are a routing/security concern, not a file structure concern.
You do NOT create separate folders per subdomain. All files live in one place.

---

## 2. File Structure

```
/var/www/opescare/                  ← Application root (not web-accessible)
    public/                         ← Web root — this is what Nginx points to
        index.php                   ← Laravel entry point (ALL subdomains)
        favicon.svg
        css/, js/
    app/                            ← Application code
    routes/
        web.php                     ← Web portal routes
        api.php                     ← All API routes
        academy.php                 ← Academy API routes
        communications.php          ← Notification/messaging routes
        partners.php                ← Partner governance routes
    storage/                        ← Logs, file uploads, cache
    bootstrap/
    config/
    database/
    .env                            ← Production environment variables
```

**On cPanel / Shared Hosting:**
```
public_html/                        ← cPanel web root
    index.php                       ← Symlink or copy from /var/www/opescare/public/
    (or configure document root in cPanel to point to /var/www/opescare/public)
```

---

## 3. Subdomain Map

Every subdomain below points to the **same document root**: `/var/www/opescare/public`

| Subdomain | Purpose | Restricted to routes |
|---|---|---|
| `app.opescare.com` | Main web portal — all staff portals | `/portals/*`, `/login`, `/signup`, `/docs` |
| `api.opescare.com` | Core REST API for internal use | `/v1/*` (excl. connect, fhir) |
| `connect.opescare.com` | B2B interoperability — HIS integrations | `/v1/connect/*` |
| `fhir.opescare.com` | FHIR R4 healthcare standard | `/fhir/R4/*` |
| `mobile-api.opescare.com` | Flutter patient + provider mobile apps | `/mobile/*`, `/provider-mobile/*` |
| `lite.opescare.com` | OpesCare Lite portal + sync API | `/portals/lite/*`, `/api/v1/lite/*` |
| `academy.opescare.com` | Learning management system | `/v1/academy/*`, `/academy/*`, `/verify/certificate/*` |
| `developer.opescare.com` | External developer self-service | `/portals/developer/*`, `/signup/developer` |
| `docs.opescare.com` | Public API documentation | `/docs/*` |
| `caremap.opescare.com` | Public facility directory | `/care-map/*`, `/v1/care-map/*` |
| `bridge.opescare.com` | Bridge Agent device sync | `/v1/bridge/*` |
| `public-health.opescare.com` | Disease surveillance + reporting | `/v1/public-health/*` |
| `ussd.opescare.com` | Africa's Talking USSD callback | `/ussd/callback` |
| `status.opescare.com` | System status page | `/status` (or external: Instatus) |

---

## 4. Server Requirements

### Minimum (Single Server — up to ~50 facilities)
| Component | Spec |
|---|---|
| CPU | 4 vCPU |
| RAM | 8 GB |
| Storage | 100 GB SSD |
| OS | Ubuntu 22.04 LTS |
| PHP | 8.3 (php-fpm) |
| Database | PostgreSQL 15+ |
| Cache | Redis 7+ |
| Web Server | Nginx 1.24+ |
| Queue Worker | Supervisor + Laravel Queue |

### Recommended (Production — up to ~500 facilities)
| Component | Spec |
|---|---|
| CPU | 8 vCPU |
| RAM | 32 GB |
| Storage | 500 GB SSD + object storage (S3/Cloudflare R2) for uploads |
| Database | PostgreSQL 15+ (separate server or managed: AWS RDS, Supabase) |
| Cache | Redis (separate server or managed: Upstash, AWS ElastiCache) |
| CDN | Cloudflare (free tier covers most needs) |

---

## 5. Nginx Configuration — All Subdomains

All subdomain blocks share the same `root` directive pointing to one folder.
Create `/etc/nginx/sites-available/opescare` and symlink to `sites-enabled`.

```nginx
# ── Shared PHP-FPM upstream ─────────────────────────────────────────────────
upstream opescare_php {
    server unix:/var/run/php/php8.3-fpm.sock;
}

# ── Shared location block (included by every server block) ──────────────────
# Save as: /etc/nginx/snippets/opescare-php.conf
# location / {
#     try_files $uri $uri/ /index.php?$query_string;
# }
# location ~ \.php$ {
#     fastcgi_pass opescare_php;
#     fastcgi_index index.php;
#     fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
#     include fastcgi_params;
# }
# location ~ /\.(?!well-known).* { deny all; }

# ── app.opescare.com — Main Web Portal ──────────────────────────────────────
server {
    listen 443 ssl http2;
    server_name app.opescare.com;

    root /var/www/opescare/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/opescare.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/opescare.com/privkey.pem;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header Referrer-Policy "strict-origin-when-cross-origin";
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains";

    client_max_body_size 50M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        fastcgi_pass opescare_php;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    location ~ /\.(?!well-known).* { deny all; }
}

# ── api.opescare.com — Core REST API ────────────────────────────────────────
server {
    listen 443 ssl http2;
    server_name api.opescare.com;

    root /var/www/opescare/public;   # ← SAME folder
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/opescare.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/opescare.com/privkey.pem;

    # CORS for API consumers
    add_header Access-Control-Allow-Origin  "*";
    add_header Access-Control-Allow-Methods "GET, POST, PUT, PATCH, DELETE, OPTIONS";
    add_header Access-Control-Allow-Headers "Authorization, Content-Type, X-Requested-With";

    # Rate limiting — 120 requests/minute per IP
    limit_req_zone $binary_remote_addr zone=api_limit:10m rate=120r/m;
    limit_req zone=api_limit burst=30 nodelay;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        fastcgi_pass opescare_php;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    location ~ /\.(?!well-known).* { deny all; }
}

# ── connect.opescare.com — B2B Interoperability ─────────────────────────────
server {
    listen 443 ssl http2;
    server_name connect.opescare.com;

    root /var/www/opescare/public;   # ← SAME folder
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/opescare.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/opescare.com/privkey.pem;

    # Tighter rate limit for B2B — 200 req/min (matches VerifyIntegrationClient)
    limit_req_zone $binary_remote_addr zone=connect_limit:10m rate=200r/m;
    limit_req zone=connect_limit burst=50 nodelay;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        fastcgi_pass opescare_php;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    location ~ /\.(?!well-known).* { deny all; }
}

# ── fhir.opescare.com — FHIR R4 ─────────────────────────────────────────────
server {
    listen 443 ssl http2;
    server_name fhir.opescare.com;

    root /var/www/opescare/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/opescare.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/opescare.com/privkey.pem;

    # FHIR requires specific CORS headers
    add_header Access-Control-Allow-Origin  "*";
    add_header Access-Control-Expose-Headers "Location, Content-Location";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        fastcgi_pass opescare_php;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}

# ── mobile-api.opescare.com — Flutter Mobile Apps ───────────────────────────
server {
    listen 443 ssl http2;
    server_name mobile-api.opescare.com;

    root /var/www/opescare/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/opescare.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/opescare.com/privkey.pem;

    # Strict rate limit on auth endpoints (brute-force protection)
    limit_req_zone $binary_remote_addr zone=mobile_auth:10m rate=5r/m;
    location ~ ^/api/mobile/auth { limit_req zone=mobile_auth burst=3 nodelay; }
    location ~ ^/api/provider-mobile/auth { limit_req zone=mobile_auth burst=3 nodelay; }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        fastcgi_pass opescare_php;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}

# ── lite.opescare.com — OpesCare Lite ───────────────────────────────────────
server {
    listen 443 ssl http2;
    server_name lite.opescare.com;

    root /var/www/opescare/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/opescare.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/opescare.com/privkey.pem;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        fastcgi_pass opescare_php;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}

# ── academy.opescare.com — OpesCare Academy ─────────────────────────────────
server {
    listen 443 ssl http2;
    server_name academy.opescare.com;

    root /var/www/opescare/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/opescare.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/opescare.com/privkey.pem;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        fastcgi_pass opescare_php;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}

# ── developer.opescare.com — Developer Portal ───────────────────────────────
server {
    listen 443 ssl http2;
    server_name developer.opescare.com;

    root /var/www/opescare/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/opescare.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/opescare.com/privkey.pem;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        fastcgi_pass opescare_php;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}

# ── docs.opescare.com — Public API Documentation ────────────────────────────
server {
    listen 443 ssl http2;
    server_name docs.opescare.com;

    root /var/www/opescare/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/opescare.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/opescare.com/privkey.pem;

    # Docs are mostly static — aggressive caching
    location ~* \.(css|js|png|svg|woff2)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        fastcgi_pass opescare_php;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}

# ── caremap.opescare.com — Public Care Map ──────────────────────────────────
server {
    listen 443 ssl http2;
    server_name caremap.opescare.com;

    root /var/www/opescare/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/opescare.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/opescare.com/privkey.pem;

    # Cache public facility listings
    location ~* ^/care-map {
        add_header Cache-Control "public, max-age=300";
        try_files $uri $uri/ /index.php?$query_string;
    }
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        fastcgi_pass opescare_php;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}

# ── bridge.opescare.com — Bridge Agent Sync ─────────────────────────────────
server {
    listen 443 ssl http2;
    server_name bridge.opescare.com;

    root /var/www/opescare/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/opescare.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/opescare.com/privkey.pem;

    # Bridge agents are high-frequency — generous rate limit
    limit_req_zone $binary_remote_addr zone=bridge_limit:10m rate=300r/m;
    limit_req zone=bridge_limit burst=100 nodelay;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        fastcgi_pass opescare_php;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}

# ── public-health.opescare.com — Disease Surveillance ───────────────────────
server {
    listen 443 ssl http2;
    server_name public-health.opescare.com;

    root /var/www/opescare/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/opescare.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/opescare.com/privkey.pem;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        fastcgi_pass opescare_php;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}

# ── ussd.opescare.com — Africa's Talking USSD ───────────────────────────────
server {
    listen 443 ssl http2;
    server_name ussd.opescare.com;

    root /var/www/opescare/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/opescare.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/opescare.com/privkey.pem;

    # Only allow POST to callback; reject everything else
    location = /ussd/callback {
        limit_except POST { deny all; }
        try_files $uri /index.php?$query_string;
    }
    location ~ \.php$ {
        fastcgi_pass opescare_php;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    location / { return 404; }
}

# ── Redirect bare HTTP to HTTPS for all subdomains ──────────────────────────
server {
    listen 80;
    server_name *.opescare.com opescare.com;
    return 301 https://$host$request_uri;
}
```

---

## 6. SSL Certificates

Use a **wildcard certificate** to cover all subdomains with one cert:

```bash
# Install Certbot
apt install certbot python3-certbot-nginx

# Issue wildcard cert (requires DNS challenge — add TXT record in your DNS panel)
certbot certonly \
  --manual \
  --preferred-challenges dns \
  -d opescare.com \
  -d "*.opescare.com"

# Certificate locations (referenced in Nginx above):
# /etc/letsencrypt/live/opescare.com/fullchain.pem
# /etc/letsencrypt/live/opescare.com/privkey.pem

# Auto-renew
certbot renew --dry-run
```

---

## 7. Environment Variables

Add these to your production `.env`:

```env
# ── Application ────────────────────────────────────────────────────────────
APP_NAME="OpesCare"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://app.opescare.com

# ── Subdomain Routing ───────────────────────────────────────────────────────
# Set to true once all Nginx subdomain blocks are active and DNS is configured.
# When true, each subdomain only serves its designated routes (others return 404).
SUBDOMAIN_ROUTING=false

# ── Service URLs (used for cross-service links in views and emails) ─────────
OPESCARE_URL_APP=https://app.opescare.com
OPESCARE_URL_API=https://api.opescare.com
OPESCARE_URL_CONNECT=https://connect.opescare.com
OPESCARE_URL_FHIR=https://fhir.opescare.com
OPESCARE_URL_MOBILE_API=https://mobile-api.opescare.com
OPESCARE_URL_LITE=https://lite.opescare.com
OPESCARE_URL_ACADEMY=https://academy.opescare.com
OPESCARE_URL_DEVELOPER=https://developer.opescare.com
OPESCARE_URL_DOCS=https://docs.opescare.com
OPESCARE_URL_CAREMAP=https://caremap.opescare.com
OPESCARE_URL_BRIDGE=https://bridge.opescare.com
OPESCARE_URL_PUBLIC_HEALTH=https://public-health.opescare.com
OPESCARE_URL_USSD=https://ussd.opescare.com

# ── Database ────────────────────────────────────────────────────────────────
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=opescare_production
DB_USERNAME=opescare
DB_PASSWORD=your-strong-db-password

# ── Cache & Queues ──────────────────────────────────────────────────────────
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# ── Mail ────────────────────────────────────────────────────────────────────
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_FROM_ADDRESS=no-reply@opescare.com
MAIL_FROM_NAME="OpesCare"

# ── Africa's Talking (USSD + SMS) ───────────────────────────────────────────
AT_API_KEY=your-africastalking-api-key
AT_USERNAME=opescare
AT_SHORTCODE=*384*123#

# ── Storage ─────────────────────────────────────────────────────────────────
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=eu-west-1
AWS_BUCKET=opescare-production
```

---

## 8. Database Setup

```bash
# Create PostgreSQL database and user
sudo -u postgres psql
CREATE DATABASE opescare_production;
CREATE USER opescare WITH ENCRYPTED PASSWORD 'your-strong-db-password';
GRANT ALL PRIVILEGES ON DATABASE opescare_production TO opescare;
\q

# Run migrations
cd /var/www/opescare
php artisan migrate --force

# Seed core data (categories, roles, dashboard profiles, notification templates)
php artisan db:seed --force

# Seed production accounts
php artisan db:seed --class=ProductionAccountsSeeder --force
php artisan db:seed --class=StaffAccountsSeeder --force
```

---

## 9. First Deploy Checklist

```
DNS Setup
  □ A record: opescare.com          → server IP
  □ A record: *.opescare.com        → server IP (wildcard)
  □ MX records for no-reply@opescare.com

SSL
  □ Wildcard cert issued for *.opescare.com
  □ Auto-renewal configured (certbot renew cron)

Server
  □ PHP 8.3-fpm installed and running
  □ PostgreSQL 15+ installed and running
  □ Redis 7+ installed and running
  □ Nginx installed, opescare site config linked and tested
  □ Supervisor installed for queue workers

Application
  □ .env configured for production (APP_DEBUG=false)
  □ php artisan config:cache
  □ php artisan route:cache
  □ php artisan view:cache
  □ php artisan migrate --force
  □ php artisan db:seed --force
  □ php artisan db:seed --class=ProductionAccountsSeeder --force
  □ php artisan storage:link
  □ Correct permissions: storage/ and bootstrap/cache/ writable by www-data

Queue Workers (Supervisor config: /etc/supervisor/conf.d/opescare.conf)
  □ Default queue worker running
  □ Lite sync worker running (if applicable)

After Go-Live
  □ Set SUBDOMAIN_ROUTING=true in .env
  □ php artisan config:cache
  □ Test each subdomain with curl
  □ Register ussd.opescare.com with Africa's Talking dashboard
  □ Register mobile-api.opescare.com in Flutter app .env
```

---

## 10. Enabling Subdomain Routing

Once all Nginx blocks are active and DNS has propagated:

```bash
# 1. Update .env
SUBDOMAIN_ROUTING=true

# 2. Clear config cache
php artisan config:cache

# 3. Test each subdomain
curl -I https://api.opescare.com/health
curl -I https://connect.opescare.com/v1/connect/auth/token
curl -I https://fhir.opescare.com/fhir/R4/metadata
curl -I https://lite.opescare.com/portals/lite
curl -I https://caremap.opescare.com/care-map
curl -I https://ussd.opescare.com/ussd/callback

# Each should return 200 or 405 (method not allowed for GET on POST-only).
# Accessing a wrong route on a restricted subdomain should return 404.
```

---

## 11. Scaling Path — Monolith to Microservices

When traffic grows beyond a single server, extract services in this order:

| Phase | Extract | Trigger |
|---|---|---|
| 1 | Move DB to dedicated server | > 50 active facilities |
| 2 | Move Redis to dedicated server | Queue lag > 5 seconds |
| 3 | Put Cloudflare in front (CDN + WAF) | Any production launch |
| 4 | Separate `mobile-api` server | > 10 000 mobile users |
| 5 | Separate `connect` server | > 20 integrated hospitals |
| 6 | Separate `fhir` server | Government FHIR mandate |
| 7 | Separate `academy` as standalone Laravel | Academy sold as product |
| 8 | Extract `lite` as standalone package | Lite licensed to hospitals |
| 9 | Full microservices (Kubernetes) | > 1 000 facilities |

---

## 12. OpesCare Lite — Standalone Deployment Options

See [Lite Hosting Architecture](#) for full details. Summary:

| Mode | How | When to use |
|---|---|---|
| **Hosted SaaS** | `lite.opescare.com` subdomain (this document) | Default. No hospital server needed |
| **Hospital-hosted** | Extracted thin Laravel + same DB connection | Hospital has a server and IT team |
| **Electron Desktop** | PHP + Laravel + SQLite bundled in Electron | No internet, Windows PC at facility |
| **Flutter Mobile** | Consumes `/api/v1/lite/*` directly | Android tablet, offline-capable |

---

*Document maintained by the OpesCare platform team. Update this file whenever a new service or subdomain is added.*
