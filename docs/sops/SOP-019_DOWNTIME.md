# SOP-019 — Planned & Unplanned Downtime

**SOP Number:** SOP-019 | **Version:** 1.0 | **Owner:** Infrastructure Lead

---

## 1. Planned Downtime
- Announce min 48 hours in advance via status page + email + in-app banner.
- Schedule Sunday 02:00–06:00 WAT.
- Define rollback plan before starting.
- Execute per deployment runbook. Verify health checks. Update status page.
- Monitor error rates 30 minutes post-restart.

## 2. Unplanned Outage

**0–15 min:** Identify and confirm outage. Page on-call engineer. Update status page: "Investigating".

**15–60 min:** Identify root cause. Apply fix or failover. Update status page with ETA.

**Resolution:** Confirm full service restored. Update status page: "Resolved". Write incident report within 24 hours.

## 3. Related SOPs
SOP-018 Incident Reporting
