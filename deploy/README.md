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
systemctl is-active opescare-horizon.service     # -> active
php artisan horizon:status                        # -> "Horizon is running."
```

If `horizon:status` does not print running, queued work (webhooks, FHIR
notifications, exports/imports, reminders) is silently stalled — treat it as a
SEV2. Re-check with `journalctl -u opescare-horizon -n 50`.

The deploy workflow already calls `php artisan horizon:terminate` on each release;
systemd's `Restart=always` brings Horizon back with the new code. No cron entry is
needed — the `.timer` replaces the traditional `* * * * * schedule:run` line.

**Alternative (cron instead of the timer):**
```cron
* * * * * www-data cd /var/www/opescare/apps/api-laravel && /usr/bin/php8.3 artisan schedule:run >> /dev/null 2>&1
```

## 2. Error tracking (Sentry) — DSN-only, ~2 minutes

**Already wired in code** (`sentry/sentry-laravel`): the SDK is installed,
exceptions are reported to Sentry in production/staging (`bootstrap/app.php`),
and `config/sentry.php` ships a PHI-safe `before_send` that strips request
bodies, cookies and query strings, with `send_default_pii=false` and SQL
bindings off. It is a **no-op until the DSN is set**, so it's safe pre-launch.

Just set in the production `.env` (NOT committed):
```env
SENTRY_LARAVEL_DSN=https://<key>@<org>.ingest.sentry.io/<project>
SENTRY_TRACES_SAMPLE_RATE=0.1          # 10% performance traces; tune for cost
SENTRY_SEND_DEFAULT_PII=false          # IMPORTANT: never ship patient PII to Sentry
```
`SENTRY_RELEASE` is set automatically by `deploy.yml` to the deployed commit SHA.

Verify after setting the DSN:
```bash
php artisan sentry:test          # sends a test event; confirm it lands in Sentry
```

PHI/PII guard: keep `SENTRY_SEND_DEFAULT_PII=false`. The `before_send` scrubber is
already implemented in `config/sentry.php`. This is a national patient-data program —
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

Automated gates (run by `deploy.yml`, also runnable by hand):
```bash
php artisan config:assert-production   # fails the deploy if prod config is unsafe/missing
php artisan payments:smoke             # verifies configured MoMo/Orange creds can authenticate
```
Run `payments:smoke` once the live MTN MoMo Collections key is in `.env`; it must
print `✓ MTN MoMo token` before paid subscription/billing is enabled.

See `apps/api-laravel/docs/RUNBOOK-PRODUCTION.md` for the full go-live checklist.
