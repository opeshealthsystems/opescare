"""
Local SQLite queue for offline-safe record staging.

Records are inserted when the connector detects new data.
They are deleted only after the server confirms receipt.
The queue survives agent restarts and network outages.
"""
import json
import sqlite3
import time
from dataclasses import dataclass
from enum import Enum
from pathlib import Path
from typing import List, Optional


class RecordStatus(str, Enum):
    PENDING   = "pending"
    SENDING   = "sending"
    DELIVERED = "delivered"
    FAILED    = "failed"


@dataclass
class QueuedRecord:
    id: int
    record_type: str      # "encounter" | "lab_result" | "prescription" | "patient"
    payload: dict
    idempotency_key: str
    status: RecordStatus
    attempt_count: int
    created_at: float
    last_attempt_at: Optional[float]
    error_message: Optional[str]


class LocalQueue:
    """
    SQLite-backed queue for Bridge Agent outbound records.

    Thread-safety: SQLite WAL mode provides safe concurrent reads.
    For single-process agents (typical), this is sufficient.
    """

    SCHEMA = """
    CREATE TABLE IF NOT EXISTS queued_records (
        id              INTEGER PRIMARY KEY AUTOINCREMENT,
        record_type     TEXT    NOT NULL,
        payload         TEXT    NOT NULL,
        idempotency_key TEXT    NOT NULL UNIQUE,
        status          TEXT    NOT NULL DEFAULT 'pending',
        attempt_count   INTEGER NOT NULL DEFAULT 0,
        created_at      REAL    NOT NULL,
        last_attempt_at REAL,
        error_message   TEXT
    );
    CREATE INDEX IF NOT EXISTS idx_status ON queued_records(status);
    """

    def __init__(self, db_path: str = "bridge_queue.db"):
        self.db_path = db_path
        self._init_db()

    def _init_db(self) -> None:
        with self._conn() as conn:
            conn.executescript(self.SCHEMA)

    def _conn(self) -> sqlite3.Connection:
        conn = sqlite3.connect(self.db_path, timeout=10)
        conn.row_factory = sqlite3.Row
        conn.execute("PRAGMA journal_mode=WAL")
        return conn

    def enqueue(self, record_type: str, payload: dict, idempotency_key: str) -> int:
        """Add a record to the queue. Returns the new row id."""
        with self._conn() as conn:
            cursor = conn.execute(
                """INSERT OR IGNORE INTO queued_records
                   (record_type, payload, idempotency_key, status, created_at)
                   VALUES (?, ?, ?, ?, ?)""",
                (record_type, json.dumps(payload), idempotency_key, RecordStatus.PENDING, time.time()),
            )
            return cursor.lastrowid or 0

    def peek_pending(self, limit: int = 50) -> List[QueuedRecord]:
        """Return up to `limit` pending records ordered by creation time."""
        with self._conn() as conn:
            rows = conn.execute(
                "SELECT * FROM queued_records WHERE status = ? ORDER BY created_at LIMIT ?",
                (RecordStatus.PENDING, limit),
            ).fetchall()
        return [self._row_to_record(r) for r in rows]

    def mark_sending(self, record_id: int) -> None:
        with self._conn() as conn:
            conn.execute(
                "UPDATE queued_records SET status = ?, last_attempt_at = ? WHERE id = ?",
                (RecordStatus.SENDING, time.time(), record_id),
            )

    def mark_delivered(self, record_id: int) -> None:
        with self._conn() as conn:
            conn.execute(
                "UPDATE queued_records SET status = ? WHERE id = ?",
                (RecordStatus.DELIVERED, record_id),
            )

    def mark_failed(self, record_id: int, error: str) -> None:
        with self._conn() as conn:
            conn.execute(
                """UPDATE queued_records
                   SET status = ?, attempt_count = attempt_count + 1,
                       last_attempt_at = ?, error_message = ?
                   WHERE id = ?""",
                (RecordStatus.FAILED, time.time(), error[:1000], record_id),
            )

    def requeue_failed(self, max_attempts: int = 5) -> int:
        """Reset failed records back to pending for retry (up to max_attempts)."""
        with self._conn() as conn:
            cursor = conn.execute(
                """UPDATE queued_records SET status = ?
                   WHERE status = ? AND attempt_count < ?""",
                (RecordStatus.PENDING, RecordStatus.FAILED, max_attempts),
            )
            return cursor.rowcount

    def pending_count(self) -> int:
        with self._conn() as conn:
            return conn.execute(
                "SELECT COUNT(*) FROM queued_records WHERE status = ?",
                (RecordStatus.PENDING,),
            ).fetchone()[0]

    def _row_to_record(self, row: sqlite3.Row) -> QueuedRecord:
        return QueuedRecord(
            id              = row["id"],
            record_type     = row["record_type"],
            payload         = json.loads(row["payload"]),
            idempotency_key = row["idempotency_key"],
            status          = RecordStatus(row["status"]),
            attempt_count   = row["attempt_count"],
            created_at      = row["created_at"],
            last_attempt_at = row["last_attempt_at"],
            error_message   = row["error_message"],
        )
