# OpesCare Secrets Rotation Runbook

**Version:** 1.0
**Owner:** Platform Engineering
**Review cadence:** Quarterly

## Overview

Step-by-step procedures for rotating OpesCare secrets. Rotation is triggered by:
- Routine schedule (see thresholds below)
- Suspected credential compromise
- Staff departure with secret access

**Never auto-rotate** without a change-control record.

## Rotation Thresholds

| Secret | Max Age | Check Command |
|--------|---------|---------------|
| APP_KEY | 90 days | `php artisan opescare:rotate-secrets --check` |
| DB password | 60 days | `php artisan opescare:rotate-secrets --check` |
| MTN MoMo API key | 180 days | `php artisan opescare:rotate-secrets --check` |
| Orange Money API key | 180 days | `php artisan opescare:rotate-secrets --check` |
| KMS data keys | 365 days | AWS Console → KMS → Enable automatic rotation |

## Procedure 1: Rotate APP_KEY

> Rotating APP_KEY invalidates all existing encrypted cookies and sessions.

1. Generate: `php artisan key:generate --show`
2. Update in AWS Secrets Manager: `aws secretsmanager update-secret --secret-id opescare/app_key --secret-string "base64:xxxx..."`
3. Deploy via rolling restart.
4. Verify: `curl https://your-domain.com/api/health | jq .`
5. Record: `php artisan tinker` → `Cache::put('secrets.last_rotated.app_key', now()->toIso8601String(), 400 * 24 * 60);`

## Procedure 2: Rotate DB Password

1. Generate: `openssl rand -base64 32`
2. Update PostgreSQL: `ALTER USER opescare_app PASSWORD 'new_password';`
3. Update Secrets Manager: `aws secretsmanager update-secret --secret-id opescare/db_password --secret-string "new_password"`
4. Rolling restart → verify health endpoint.
5. Record in Cache.

## Procedure 3: Rotate Mobile Money Keys

### MTN MoMo
1. Regenerate on MTN Developer Portal.
2. Update `MTN_MOMO_API_USER` and `MTN_MOMO_API_KEY` in Secrets Manager.
3. Deploy → smoke test payment flow.
4. Record: `Cache::put('secrets.last_rotated.api_key_mtn_momo', now()->toIso8601String(), 400 * 24 * 60);`

### Orange Money
1. Regenerate on Orange partner portal.
2. Update `ORANGE_MONEY_SECRET` in Secrets Manager.
3. Deploy → smoke test.
4. Record: `Cache::put('secrets.last_rotated.api_key_orange', now()->toIso8601String(), 400 * 24 * 60);`

## Procedure 4: AWS KMS Key Rotation

Enable automatic annual rotation:
```bash
aws kms enable-key-rotation --key-id alias/opescare
```
Record: `Cache::put('secrets.last_rotated.kms_data_keys', now()->toIso8601String(), 400 * 24 * 60);`

## Post-Rotation Checklist
- [ ] `GET /api/health` returns `{"status":"ok"}`
- [ ] Run `php artisan opescare:rotate-secrets --check` — all OK
- [ ] Monitor logs for auth failures for 10 minutes
