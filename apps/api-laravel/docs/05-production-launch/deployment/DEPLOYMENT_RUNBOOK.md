# OpesCare Deployment Runbook

**Version:** 1.0  
**Last Updated:** 2026-05  
**Owner:** Infrastructure / DevOps Lead  

---

## 1. Pre-Deployment Checklist

Before any production deployment:

- [ ] All tests passing (`php artisan test --no-coverage` → 0 failures)
- [ ] Migrations reviewed and rolled back tested
- [ ] `.env.production` variables audited (no dev keys, no test credentials)
- [ ] Feature flags set appropriately
- [ ] Database backup confirmed (snapshot taken)
- [ ] CDN / static assets pre-warmed
- [ ] Third-party API keys (SMS, email, payment) confirmed active
- [ ] Maintenance window communicated to stakeholders
- [ ] Rollback plan documented and understood
- [ ] On-call engineer identified

---

## 2. Environment Variables — Required for Production

```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=<generated via php artisan key:generate>
APP_URL=https://app.opescare.com

DB_CONNECTION=pgsql
DB_HOST=<rds-or-private-host>
DB_PORT=5432
DB_DATABASE=opescare_prod
DB_USERNAME=opescare
DB_PASSWORD=<strong-password>

REDIS_HOST=<elasticache-or-redis-host>
REDIS_PASSWORD=<strong-password>
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

MAIL_MAILER=ses
MAIL_FROM_ADDRESS=noreply@opescare.com
MAIL_FROM_NAME=OpesCare

AWS_ACCESS_KEY_ID=<key>
AWS_SECRET_ACCESS_KEY=<secret>
AWS_DEFAULT_REGION=af-south-1
AWS_BUCKET=opescare-prod-files

FILESYSTEM_DISK=s3

LOG_CHANNEL=stack
LOG_LEVEL=warning

BROADCAST_DRIVER=pusher
# or BROADCAST_DRIVER=reverb for self-hosted

SMS_PROVIDER=termii
SMS_API_KEY=<key>

PAYMENT_GATEWAY=paystack
PAYSTACK_SECRET_KEY=<live-key>
```

---

## 3. Server Requirements

| Component | Minimum | Recommended |
|-----------|---------|-------------|
| PHP | 8.3 | 8.3+ |
| Web server | Nginx 1.24 | Nginx latest |
| Database | PostgreSQL 15 | PostgreSQL 16 |
| Cache / Queue | Redis 7 | Redis 7+ |
| File storage | Local / S3-compatible | AWS S3 af-south-1 |
| Memory (app server) | 2 GB RAM | 4–8 GB |
| CPU (app server) | 2 vCPU | 4 vCPU |
| Disk | 50 GB SSD | 200 GB SSD |

---

## 4. Deployment Steps

### 4.1 Zero-Downtime Deploy (Blue-Green or Rolling)

```bash
# 1. Pull latest release tag
git fetch --tags
git checkout v<x.y.z>

# 2. Install dependencies (no dev)
composer install --no-dev --optimize-autoloader

# 3. Run migrations (always test on staging first)
php artisan migrate --force

# 4. Cache config, routes, views
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 5. Restart queue workers
php artisan queue:restart

# 6. Reload PHP-FPM
sudo systemctl reload php8.3-fpm

# 7. Clear CDN cache if applicable
# aws cloudfront create-invalidation --distribution-id <id> --paths "/*"
```

### 4.2 First-Time Deployment

```bash
# 1. Clone repo and install
git clone git@github.com:opescare/opescare.git
cd opescare/apps/api-laravel
composer install --no-dev --optimize-autoloader

# 2. Configure environment
cp .env.example .env.production
# Edit .env.production with production values
ln -sf .env.production .env

# 3. Generate app key
php artisan key:generate

# 4. Run migrations and seed
php artisan migrate --force
php artisan db:seed --class=ProductionSeeder --force

# 5. Set storage permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# 6. Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Set up supervisor for queue workers
# See /docs/05-production-launch/deployment/SUPERVISOR_CONFIG.md
```

---

## 5. Nginx Configuration

```nginx
server {
    listen 80;
    server_name app.opescare.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name app.opescare.com;
    root /var/www/opescare/apps/api-laravel/public;

    ssl_certificate     /etc/letsencrypt/live/app.opescare.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/app.opescare.com/privkey.pem;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains";
    add_header Referrer-Policy "strict-origin-when-cross-origin";

    index index.php;
    charset utf-8;
    client_max_body_size 50M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 6. Queue Worker Setup (Supervisor)

```ini
[program:opescare-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/opescare/apps/api-laravel/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/log/opescare/worker.log
stopwaitsecs=3600
```

---

## 7. Post-Deployment Verification

```bash
# Check application response
curl -I https://app.opescare.com/health

# Check queue worker status
php artisan queue:monitor redis:default

# Check scheduled tasks
php artisan schedule:list

# Check error log (last 50 lines)
tail -50 storage/logs/laravel.log | grep ERROR

# Check DB connection
php artisan tinker --execute="DB::connection()->getPdo(); echo 'DB OK';"
```

---

## 8. Rollback Procedure

```bash
# 1. Switch to previous release tag
git checkout v<previous-tag>

# 2. Reinstall dependencies for that version
composer install --no-dev --optimize-autoloader

# 3. Rollback migrations if needed (CAUTION — data loss risk)
# php artisan migrate:rollback --step=1

# 4. Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 5. Recache for rolled-back version
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Restart queue workers
php artisan queue:restart
sudo systemctl reload php8.3-fpm
```

---

## 9. Release Tags

Format: `v{MAJOR}.{MINOR}.{PATCH}`  
Example: `v1.2.0`

| Part | When to increment |
|------|-------------------|
| MAJOR | Breaking API change, major re-architecture |
| MINOR | New feature, non-breaking change |
| PATCH | Bug fix, security patch, hot fix |

Tag releases:
```bash
git tag -a v1.0.0 -m "Production release 1.0.0"
git push origin v1.0.0
```
