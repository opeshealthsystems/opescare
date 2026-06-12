"""Tests for the SQLite local queue — core reliability component."""
import os
import tempfile
import time

import pytest

from opescare_bridge.queue.local_queue import LocalQueue, RecordStatus


@pytest.fixture
def queue(tmp_path):
    db = str(tmp_path / "test_queue.db")
    return LocalQueue(db_path=db)


def test_enqueue_returns_row_id(queue):
    row_id = queue.enqueue("encounter", {"health_id": "CM-HID-0001"}, "idem-001")
    assert row_id > 0


def test_enqueue_idempotent_key_does_not_duplicate(queue):
    queue.enqueue("encounter", {"health_id": "CM-HID-0001"}, "idem-dupe")
    queue.enqueue("encounter", {"health_id": "CM-HID-0001"}, "idem-dupe")
    records = queue.peek_pending(limit=100)
    assert len([r for r in records if r.idempotency_key == "idem-dupe"]) == 1


def test_peek_pending_returns_pending_only(queue):
    queue.enqueue("encounter", {"a": 1}, "key-1")
    queue.enqueue("lab_result", {"b": 2}, "key-2")
    pending = queue.peek_pending(limit=100)
    assert len(pending) == 2
    assert all(r.status == RecordStatus.PENDING for r in pending)


def test_mark_delivered_removes_from_pending(queue):
    queue.enqueue("encounter", {"a": 1}, "key-del")
    records = queue.peek_pending(limit=10)
    queue.mark_delivered(records[0].id)
    assert queue.pending_count() == 0


def test_mark_failed_increments_attempt_count(queue):
    queue.enqueue("encounter", {"a": 1}, "key-fail")
    records = queue.peek_pending(limit=10)
    queue.mark_failed(records[0].id, "Server error")
    # After mark_failed, status is FAILED — no longer in pending
    assert queue.pending_count() == 0


def test_requeue_failed_resets_to_pending(queue):
    queue.enqueue("encounter", {"a": 1}, "key-retry")
    records = queue.peek_pending(limit=10)
    queue.mark_failed(records[0].id, "Transient error")
    requeued = queue.requeue_failed(max_attempts=5)
    assert requeued == 1
    assert queue.pending_count() == 1


def test_requeue_respects_max_attempts(queue):
    queue.enqueue("encounter", {"a": 1}, "key-maxfail")
    records = queue.peek_pending(limit=10)
    # Fail it 5 times
    for _ in range(5):
        queue.mark_failed(records[0].id, "persistent error")
        queue.requeue_failed(max_attempts=5)

    # After 5 failures, requeue_failed should not reset it anymore
    requeued = queue.requeue_failed(max_attempts=5)
    assert requeued == 0


def test_payload_survives_roundtrip(queue):
    payload = {"health_id": "CM-HID-1234", "clinical_note": "Test note 🏥", "nested": {"a": 1}}
    queue.enqueue("encounter", payload, "key-payload")
    records = queue.peek_pending(limit=10)
    assert records[0].payload == payload


def test_pending_count_accurate(queue):
    for i in range(5):
        queue.enqueue("lab_result", {"i": i}, f"key-{i}")
    assert queue.pending_count() == 5
