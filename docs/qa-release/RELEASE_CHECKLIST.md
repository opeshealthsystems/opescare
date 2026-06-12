# OpesCare Release Checklist

**Version:** 1.0 | **Last Updated:** 2026-05

---

## Pre-Release (T-48h)

- [ ] All planned features merged to `main`
- [ ] All tests passing: `php artisan test --no-coverage` → 0 failures
- [ ] No critical open bugs
- [ ] Migrations tested on staging (up + rollback verified)
- [ ] Changelog drafted
- [ ] Release branch / tag created: `v{x.y.z}`
- [ ] Staging deployment successful
- [ ] Smoke tests passed on staging
- [ ] QA sign-off on new features
- [ ] Security review completed for new endpoints
- [ ] Dependencies updated and scanned for CVEs
- [ ] Performance: no regression in API response times
- [ ] Database backup taken before release

## Pre-Release (T-2h)

- [ ] Maintenance window communicated via status page
- [ ] On-call engineer confirmed and briefed
- [ ] Rollback plan documented and understood
- [ ] Database backup verified (< 2 hours old)

## During Release

- [ ] Maintenance mode activated: `php artisan down`
- [ ] Code deployed per DEPLOYMENT_RUNBOOK.md
- [ ] Migrations run: `php artisan migrate --force`
- [ ] Config/route/view caches cleared and rebuilt
- [ ] Queue workers restarted
- [ ] Health check passes: `GET /health → {"status":"ok"}`
- [ ] Maintenance mode deactivated: `php artisan up`

## Post-Release (0–30 min)

- [ ] Status page updated to operational
- [ ] Error rate monitored (< 0.1% threshold)
- [ ] API response times normal
- [ ] Queue processing normally
- [ ] Critical user journeys verified manually:
  - [ ] Patient registration
  - [ ] Health ID lookup
  - [ ] Queue check-in
  - [ ] Consultation note save
  - [ ] Billing invoice
- [ ] Release notes published
- [ ] Facility admins notified if breaking changes

## Rollback Trigger Conditions

Roll back immediately if within 30 minutes of deploy:
- Error rate > 5%
- Health check fails
- Database migration errors affecting data
- Core user journey broken (registration, queue, billing)
