# OpesCare deployment — supervision & observability

Closes the operational gaps flagged in the 2026-06-17 readiness review: the queue
worker (Horizon) and the scheduler (cron) were **not encoded anywhere**, so if they
weren't running, async work and scheduled jobs would silently stop. There was also
**no error-tracking backend** (logs only, over UDP to Datadog).

> Adjust `User`, `WorkingDirectory`, and the PHP path (`/usr/bin/php8.3`) in the unit
> files to match your host and `DEPLOY_PATH` (see `.github/workflows/deploy.yml`).

## 1. Supervise Horizon + the scheduler (systemd) — no external account needed

```bash
# From repo root on the production host:
sudo cp deploy/systemd/opescare-horizon.service    /etc/systemd/system/
sudo cp deploy/systemd/opescare-scheduler.service  /etc/systemd/system/
sudo cp deploy/systemd/opescare-scheduler.timer    /etc/systemd/system/

sudo systemctl daemon-reload
sudo systemctl enable --now opescare-horizon.service
sudo systemctl enable --now opescare-scheduler.timer

# Verify
systemctl status opescare-horizon.service
systemctl list-timers | grep opescare
```

The deploy workflow already calls `php artisan horizon:terminate` on each release;
systemd's `Restart=always` brings Horizon back with the new code. No cron entry is
needed — the `.timer` replaces the traditional `* * * * * schedule:run` line.

**Alternative (cron instead of the timer):**
```cron
* * * * * www-data cd /var/www/opescare/apps/api-laravel && /usr/bin/php8.3 artisan schedule:run >> /dev/null 2>&1
```

## 2. Error tracking (Sentry) — needs a DSN, then ~5 minutes

Currently exceptions are only written to logs (Datadog over UDP — lossy, no grouping
or alerting). To add real error tracking:

```bash
cd apps/api-laravel
composer require sentry/sentry-laravel
php artisan sentry:publish --dsn=PASTE_YOUR_DSN_HERE   # writes config/sentry.php + .env
```

Then set in the production `.env` (NOT committed):
```env
SENTRY_LARAVEL_DSN=https://<key>@<org>.ingest.sentry.io/<project>
SENTRY_TRACES_SAMPLE_RATE=0.2          # 20% performance traces; tune for cost
SENTRY_SEND_DEFAULT_PII=false          # IMPORTANT: never ship patient PII to Sentry
```

PHI/PII guard: keep `SENTRY_SEND_DEFAULT_PII=false`, and add a `before_send` scrubber
in `config/sentry.php` to strip health IDs, phone numbers, names, and tokens from
event payloads before they leave the server. This is a national patient-data program —
treat the error pipeline as a PHI egress point.

Sanity check after enabling:
```bash
php artisan sentry:test
```

## 3. Production config reminders (from the readiness review)

- `CACHE_STORE=redis` (the committed default is `database` — Redis is already
  provisioned and is both faster and required for the idempotency lock in the
  pending Blocker #4 patch).
- `QUEUE_CONNECTION=redis`, `SESSION_DRIVER` per your security posture.
- Confirm `APP_DEBUG=false` and demo mode off — `ProductionSafetyServiceProvider`
  throws at boot if either is wrong, which is the desired guard.
- Wire the deploy failure alert: `.github/workflows/deploy.yml` has a `# Add
  Slack/PagerDuty webhook here` TODO in the "Notify on failure" step.
