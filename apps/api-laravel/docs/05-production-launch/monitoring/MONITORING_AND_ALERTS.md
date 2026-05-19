# OpesCare Monitoring & Observability

**Version:** 1.0  
**Last Updated:** 2026-05  
**Owner:** Infrastructure Lead  

---

## 1. Monitoring Stack

| Layer | Tool | Purpose |
|-------|------|---------|
| APM | Laravel Telescope (dev) / Sentry (prod) | Error tracking, slow queries |
| Infrastructure | AWS CloudWatch / Grafana | CPU, memory, disk, network |
| Uptime | UptimeRobot / Pingdom | External health checks |
| Logs | CloudWatch Logs / ELK Stack | Centralised log search |
| Alerting | PagerDuty / Slack | On-call escalation |
| Database | pg_stat_statements + slow query log | Query performance |

---

## 2. Key Health Check Endpoints

```
GET /health
→ Returns: { "status": "ok", "db": "ok", "redis": "ok", "queue": "ok" }

GET /api/v1/health
→ Returns: { "status": "ok", "version": "1.0.0" }
```

Implement in `routes/web.php`:
```php
Route::get('/health', function () {
    $db    = DB::connection()->getPdo() ? 'ok' : 'error';
    $redis = Cache::store('redis')->get('health_check') !== null || Cache::store('redis')->put('health_check', 1, 60) ? 'ok' : 'error';
    $queue = Queue::size('default') !== null ? 'ok' : 'error';
    return response()->json(['status' => 'ok', 'db' => $db, 'redis' => $redis, 'queue' => $queue]);
});
```

---

## 3. SLA Targets

| Metric | Target |
|--------|--------|
| Uptime | 99.9% monthly |
| API response time (P95) | < 500ms |
| API response time (P99) | < 1500ms |
| Queue processing lag | < 2 minutes |
| DB query P95 | < 100ms |
| Error rate | < 0.1% of requests |

---

## 4. Alert Definitions

### 4.1 Critical Alerts (P1 — Wake someone up)

| Alert | Condition | Action |
|-------|-----------|--------|
| Site down | Health check fails 3 consecutive times (3 min) | Page on-call engineer immediately |
| DB connection failure | Can't connect to primary DB | Page + begin failover |
| Error rate spike | >5% error rate for 5 minutes | Page + investigate |
| Data breach detected | Unauthorised bulk data export or access | IMMEDIATE — security incident protocol |
| SSL certificate expiry | <7 days to expiry | Urgent renewal |

### 4.2 High Alerts (P2 — Respond within 1 hour)

| Alert | Condition | Action |
|-------|-----------|--------|
| Slow API | P95 > 2000ms for 10 minutes | Investigate, scale if needed |
| Queue backlog | >1000 jobs pending for >5 minutes | Scale workers |
| Disk usage | >85% | Clean logs/tmp, add capacity |
| Memory usage | >90% | Investigate, restart if needed |
| Backup failure | Nightly backup job failed | Investigate and re-run |

### 4.3 Warning Alerts (P3 — Respond same business day)

| Alert | Condition | Action |
|-------|-----------|--------|
| Slow queries | P95 query >500ms for 30 minutes | Query optimisation |
| Redis eviction | Eviction rate >0 | Increase Redis memory |
| Failed jobs | >10 failed queue jobs | Review and re-queue |
| Inactive cron | Scheduled task not run in 25 hours | Check cron/scheduler |

---

## 5. Logging

### 5.1 Log Levels in Production

```env
LOG_CHANNEL=stack
LOG_LEVEL=warning
```

### 5.2 Log Categories

| Category | Channel | Retention |
|----------|---------|-----------|
| Application errors | `laravel` | 30 days |
| Audit events | `audit` | 7 years (NDPR) |
| Access logs | Nginx access log | 90 days |
| Security events | `security` | 1 year |
| Queue failures | `failed_jobs` | 90 days |

### 5.3 Sentry Integration

```php
// config/logging.php
'sentry' => [
    'driver' => 'sentry',
    'level'  => 'error',
],
```

```env
SENTRY_LARAVEL_DSN=https://key@sentry.io/project-id
SENTRY_TRACES_SAMPLE_RATE=0.1
```

---

## 6. Performance Monitoring

### 6.1 Slow Query Log (PostgreSQL)

```sql
-- Enable in postgresql.conf
log_min_duration_statement = 1000  -- log queries > 1 second
log_statement = 'none'
shared_preload_libraries = 'pg_stat_statements'
```

### 6.2 Application Performance

Instrument critical paths with Telescope in staging:

```php
// AppServiceProvider::boot()
if (app()->environment('staging')) {
    Telescope::night();
}
```

---

## 7. On-Call Rotation

- Primary on-call: rotates weekly among backend engineers
- Secondary on-call: infrastructure lead always secondary for P1
- PagerDuty escalation: 5 min unacknowledged → escalate to secondary
- After-hours P1 only — P2/P3 next business day

---

## 8. Status Page

Maintain a public status page at `status.opescare.com` showing:

- API availability
- Portal availability
- Database availability
- SMS/notification service
- Payment processing
- Planned maintenance windows

Update the status page immediately when any P1 or P2 incident is confirmed.
