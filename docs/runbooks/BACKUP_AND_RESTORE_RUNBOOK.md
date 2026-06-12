# OpesCare Backup & Restore Runbook

**Version:** 1.0  
**Last Updated:** 2026-05  
**Owner:** Infrastructure Lead  
**RPO (Recovery Point Objective):** ≤ 1 hour  
**RTO (Recovery Time Objective):** ≤ 4 hours  

---

## 1. Backup Strategy

| Data Type | Method | Frequency | Retention |
|-----------|--------|-----------|-----------|
| PostgreSQL database | Automated snapshots + WAL | Every 1 hour | 30 days |
| PostgreSQL database | Daily pg_dump export | Daily 02:00 WAT | 90 days |
| S3 file storage | S3 versioning + replication | Continuous | 365 days |
| Redis cache/session | Not backed up (recoverable) | — | — |
| Application code | Git repository | On every commit | Permanent |
| `.env` secrets | Vault / AWS Secrets Manager | On change | Permanent |

---

## 2. Database Backup

### 2.1 Automated PostgreSQL Backup (pg_dump)

```bash
#!/bin/bash
# /etc/cron.d/opescare-backup
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR=/var/backups/opescare/postgres
S3_BUCKET=s3://opescare-backups/postgres/

mkdir -p $BACKUP_DIR

# Dump
PGPASSWORD="$DB_PASSWORD" pg_dump \
  -h $DB_HOST \
  -U $DB_USERNAME \
  -d opescare_prod \
  --format=custom \
  --file=$BACKUP_DIR/opescare_$TIMESTAMP.pgdump

# Compress
gzip $BACKUP_DIR/opescare_$TIMESTAMP.pgdump

# Upload to S3
aws s3 cp $BACKUP_DIR/opescare_$TIMESTAMP.pgdump.gz $S3_BUCKET

# Rotate local files (keep last 7 days)
find $BACKUP_DIR -name "*.pgdump.gz" -mtime +7 -delete

echo "Backup completed: opescare_$TIMESTAMP.pgdump.gz"
```

### 2.2 RDS / Cloud Managed Database

If using AWS RDS:
- Enable automated backups: 7-day retention
- Enable point-in-time recovery
- Maintenance window: Sunday 03:00–05:00 WAT
- Multi-AZ enabled for production

---

## 3. File Storage Backup

### 3.1 S3 Bucket Versioning

```bash
# Enable versioning on production bucket
aws s3api put-bucket-versioning \
  --bucket opescare-prod-files \
  --versioning-configuration Status=Enabled

# Enable cross-region replication (Lagos → Cape Town)
# See: AWS S3 Replication Configuration
```

### 3.2 Lifecycle Policy

```json
{
  "Rules": [
    {
      "ID": "move-to-ia-after-90-days",
      "Status": "Enabled",
      "Filter": {},
      "Transitions": [
        {"Days": 90,  "StorageClass": "STANDARD_IA"},
        {"Days": 365, "StorageClass": "GLACIER"}
      ],
      "NoncurrentVersionExpiration": {"NoncurrentDays": 365}
    }
  ]
}
```

---

## 4. Restore Procedures

### 4.1 Restore PostgreSQL from pg_dump

```bash
# 1. Download backup from S3
aws s3 cp s3://opescare-backups/postgres/opescare_20260101_020000.pgdump.gz ./
gunzip opescare_20260101_020000.pgdump.gz

# 2. Create a blank target database
createdb -h $DB_HOST -U $DB_USERNAME opescare_restore

# 3. Restore
PGPASSWORD="$DB_PASSWORD" pg_restore \
  -h $DB_HOST \
  -U $DB_USERNAME \
  -d opescare_restore \
  --no-owner \
  --role=$DB_USERNAME \
  opescare_20260101_020000.pgdump

# 4. Verify row counts
psql -h $DB_HOST -U $DB_USERNAME -d opescare_restore \
  -c "SELECT tablename, n_live_tup FROM pg_stat_user_tables ORDER BY n_live_tup DESC LIMIT 20;"

# 5. Promote to production (after verification)
# Rename databases in PostgreSQL:
# ALTER DATABASE opescare_prod RENAME TO opescare_prod_old;
# ALTER DATABASE opescare_restore RENAME TO opescare_prod;
```

### 4.2 Point-in-Time Recovery (RDS)

```bash
# Restore to specific point-in-time via AWS CLI
aws rds restore-db-instance-to-point-in-time \
  --source-db-instance-identifier opescare-prod \
  --target-db-instance-identifier opescare-prod-restored \
  --restore-time 2026-01-01T02:00:00Z

# Wait for restoration to complete
aws rds wait db-instance-available \
  --db-instance-identifier opescare-prod-restored
```

---

## 5. Backup Verification (Monthly Test)

Run this checklist every month:

- [ ] Download last nightly backup from S3
- [ ] Restore to isolated test RDS instance
- [ ] Run `php artisan test --no-coverage` against restored DB
- [ ] Verify patient count matches expected
- [ ] Verify recent data integrity (last 7 days)
- [ ] Document restore time — compare to RTO
- [ ] Delete test instance after verification

---

## 6. Disaster Recovery

See `DISASTER_RECOVERY_RUNBOOK.md` for full DR playbook.

Brief summary:
1. Primary region fails → DNS failover to standby region
2. Standby region has warm replica (15-minute lag max)
3. Promote read replica to primary
4. Update application `.env` to point to new DB host
5. Notify users of incident via status page

---

## 7. Backup Monitoring Alerts

Configure CloudWatch / PagerDuty alerts for:

| Alert | Threshold |
|-------|-----------|
| Backup job failed | Any failure |
| Backup older than 26 hours | Age > 26h |
| S3 bucket size drop >10% | Unusual decrease |
| RDS free storage < 20 GB | Low disk |
| RDS backup window missed | Window elapsed without backup |
