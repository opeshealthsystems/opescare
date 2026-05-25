# OpesCare — Production Deployment Checklist

## MANDATORY — Must complete before every deployment to national environment

### 1. Environment Configuration
- [ ] `APP_KEY` is set (run `php artisan key:generate` and store in secrets manager)
- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] `APP_URL` is set to actual HTTPS domain
- [ ] `DB_CONNECTION=pgsql`
- [ ] `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` all set
- [ ] `SESSION_ENCRYPT=true`
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] `MAIL_MAILER=smtp`
- [ ] `QUEUE_CONNECTION=redis` or `database` (NOT sync)
- [ ] `CACHE_STORE=redis` (NOT file)
- [ ] `LOG_LEVEL=warning`
- [ ] `OPESCARE_DEMO_MODE=false`
- [ ] `DEMO_ALLOWED_IPS=` (empty)
- [ ] `OPESCARE_SYSTEM_PROVIDER_ID` set to actual system account UUID

### 2. Database
- [ ] All migrations run: `php artisan migrate --force`
- [ ] System account seeder run: `php artisan db:seed --class=SystemAccountSeeder`
- [ ] Facility role assignment seeder run: `php artisan db:seed --class=FacilityRoleAssignmentSeeder`
- [ ] PII encryption run: `php artisan opescare:encrypt-patient-pii`
- [ ] PostgreSQL connection tested and working
- [ ] Database backups configured and tested

### 3. Application
- [ ] `php artisan config:cache`
- [ ] `php artisan route:cache`
- [ ] `php artisan view:cache`
- [ ] `php artisan optimize`
- [ ] Storage symlink created: `php artisan storage:link`

### 4. Security Verification
- [ ] Run `php artisan test` — all tests pass
- [ ] Verify security headers: `curl -I https://yourdomain.com/login | grep -i "content-security\|x-frame\|x-content"`
- [ ] Verify emergency endpoint requires auth: `curl -X POST https://yourdomain.com/api/v1/connect/patients/emergency-profile` → 401/403
- [ ] Verify demo login blocked: `curl -X POST https://yourdomain.com/demo-access/login-as` → 403
- [ ] Verify HTTPS redirect: `curl -I http://yourdomain.com/login` → 301

### 5. Infrastructure
- [ ] Redis running and accessible
- [ ] Queue workers running: `php artisan queue:work redis --daemon`
- [ ] Scheduler: `* * * * * php artisan schedule:run >> /dev/null 2>&1`
- [ ] SSL certificate valid and not expiring within 30 days
- [ ] Port 5432 (PostgreSQL) NOT exposed to internet
- [ ] Port 6379 (Redis) NOT exposed to internet

### 6. Monitoring
- [ ] Log shipping configured
- [ ] Alert on LOG_LEVEL=critical (production safety check failures)
- [ ] Database backup notifications configured
- [ ] Uptime monitoring active

### 7. Data
- [ ] Patient PII encryption verified (spot-check DB raw values)
- [ ] Demo data NOT present in production database
- [ ] National facility registry seeded

## REMINDER: Changing APP_KEY after PII encryption destroys all encrypted patient data.
## Store APP_KEY in a secrets manager immediately after generation.
