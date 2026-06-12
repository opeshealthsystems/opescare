"""Tests for the sync engine — mock HTTP server responses."""
import pytest
import responses as responses_lib

from opescare_bridge.config import BridgeConfig, ConnectorConfig
from opescare_bridge.queue.local_queue import LocalQueue, RecordStatus
from opescare_bridge.sync.syncer import Syncer


FACILITY_ID = "00000000-0000-0000-0000-100000000001"
SERVER_URL  = "http://opescare-test.local"
SYNC_URL    = f"{SERVER_URL}/api/v1/bridge/sync"


@pytest.fixture
def config():
    return BridgeConfig(
        server_url  = SERVER_URL,
        agent_id    = "test-agent-001",
        agent_key   = "test-agent-key",
        facility_id = FACILITY_ID,
        connector   = ConnectorConfig(type="csv_folder"),
        environment = "sandbox",
    )


@pytest.fixture
def queue(tmp_path):
    return LocalQueue(db_path=str(tmp_path / "queue.db"))


@pytest.fixture
def syncer(config, queue):
    return Syncer(config, queue)


@responses_lib.activate
def test_successful_sync_marks_delivered(syncer, queue):
    queue.enqueue("encounter", {"health_id": "CM-HID-0001", "notes": "Test"}, "idem-ok-1")

    responses_lib.add(responses_lib.POST, SYNC_URL, json={"status": "ok"}, status=200)

    result = syncer.run_once()
    assert result["delivered"] == 1
    assert result["failed"]    == 0


@responses_lib.activate
def test_idempotency_conflict_counts_as_delivered(syncer, queue):
    queue.enqueue("encounter", {"health_id": "CM-HID-0001"}, "idem-409")
    responses_lib.add(responses_lib.POST, SYNC_URL, json={"message": "Already processed."}, status=409)

    result = syncer.run_once()
    assert result["delivered"] == 1


@responses_lib.activate
def test_server_error_marks_failed(syncer, queue):
    queue.enqueue("lab_result", {"health_id": "CM-HID-0002", "test_name": "CBC"}, "idem-500")
    responses_lib.add(responses_lib.POST, SYNC_URL, json={"message": "Internal error"}, status=500)

    result = syncer.run_once()
    assert result["failed"] == 1


@responses_lib.activate
def test_empty_queue_returns_zero_counts(syncer, queue):
    result = syncer.run_once()
    assert result == {"delivered": 0, "failed": 0, "pending": 0}


@responses_lib.activate
def test_connection_error_marks_failed(syncer, queue):
    queue.enqueue("encounter", {"health_id": "CM-HID-0003"}, "idem-conn-err")
    responses_lib.add(responses_lib.POST, SYNC_URL, body=ConnectionError("No route to host"))

    result = syncer.run_once()
    assert result["failed"] == 1
