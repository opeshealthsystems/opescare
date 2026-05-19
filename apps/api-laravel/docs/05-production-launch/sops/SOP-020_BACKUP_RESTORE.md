# SOP-020 — Backup & Restore Operations

**SOP Number:** SOP-020 | **Version:** 1.0 | **Owner:** Infrastructure Lead

---

## 1. Backup Schedule

| Data | Frequency | Retention |
|------|-----------|-----------|
| Database | Hourly automated | 30 days |
| Database | Daily full export | 90 days |
| Files (S3) | Continuous versioning | 365 days |

## 2. Daily Backup Verification Checklist

- [ ] Last backup is < 26 hours old.
- [ ] S3 upload confirmed successful.
- [ ] No overnight alerts triggered.
- [ ] Disk usage on backup storage reviewed.

## 3. Monthly Full Restore Test
See BACKUP_AND_RESTORE_RUNBOOK.md for full procedure.
1. Download nightly backup. Restore to isolated instance.
2. Run data integrity checks. Measure restore time vs RTO.
3. Delete test instance. Document results.

## 4. Restore Escalation

| Scenario | Action |
|----------|--------|
| Backup corrupt | Restore from previous day |
| All recent backups failed | Escalate to Infrastructure Lead + CTO |
| Production data loss | Initiate disaster recovery plan |

## 5. Related Documents
BACKUP_AND_RESTORE_RUNBOOK.md | DISASTER_RECOVERY_RUNBOOK.md (MONITORING_AND_ALERTS.md)
