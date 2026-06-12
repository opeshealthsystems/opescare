"""Tests for CSV folder-watcher connector — ingest and transform logic."""
import csv
import os
import tempfile

import pytest

from opescare_bridge.connectors.csv_connector import (
    CsvConnector, _detect_record_type, _row_to_idempotency_key, _transform_row
)
from opescare_bridge.queue.local_queue import LocalQueue


FACILITY_ID = "00000000-0000-0000-0000-100000000001"


@pytest.fixture
def queue(tmp_path):
    return LocalQueue(db_path=str(tmp_path / "queue.db"))


@pytest.fixture
def watch_folder(tmp_path):
    folder = tmp_path / "incoming"
    folder.mkdir()
    return str(folder)


def write_csv(path: str, headers: list, rows: list) -> None:
    with open(path, "w", newline="") as f:
        w = csv.DictWriter(f, fieldnames=headers)
        w.writeheader()
        w.writerows(rows)


# ── _detect_record_type ───────────────────────────────────────────────────────

def test_detect_encounter_from_filename():
    assert _detect_record_type("encounters_2026-06-01.csv") == "encounter"

def test_detect_lab_result_from_filename():
    assert _detect_record_type("lab_results_morning.csv") == "lab_result"

def test_detect_patient_from_filename():
    assert _detect_record_type("patients_imported.csv") == "patient"

def test_detect_prescription_from_filename():
    assert _detect_record_type("prescriptions_daily.csv") == "prescription"

def test_unknown_filename_returns_none():
    assert _detect_record_type("export_20260601.csv") is None


# ── _transform_row ────────────────────────────────────────────────────────────

def test_transform_encounter_row():
    row = {
        "health_id": "CM-HID-0001",
        "notes":     "Patient has fever",
        "severity":  "medium",
        "date":      "2026-06-01",
        "_source_file": "encounters.csv",
    }
    result = _transform_row("encounter", row, FACILITY_ID)
    assert result["health_id"] == "CM-HID-0001"
    assert result["clinical_note"] == "Patient has fever"
    assert result["facility_id"] == FACILITY_ID
    assert result["source_system"] == "bridge_agent_csv"

def test_transform_lab_result_row():
    row = {
        "health_id":    "CM-HID-0002",
        "test":         "HbA1c",
        "result":       "9.2%",
        "flagged":      "true",
        "_source_file": "lab_results.csv",
    }
    result = _transform_row("lab_result", row, FACILITY_ID)
    assert result["test_name"] == "HbA1c"
    assert result["result_value"] == "9.2%"
    assert result["flagged"] is True


# ── CsvConnector end-to-end ingestion ────────────────────────────────────────

def test_scan_existing_ingests_csv_files(watch_folder, queue):
    csv_path = os.path.join(watch_folder, "encounters_test.csv")
    write_csv(csv_path,
        ["health_id", "notes", "severity", "date"],
        [
            {"health_id": "CM-HID-A001", "notes": "Fever", "severity": "low", "date": "2026-06-01"},
            {"health_id": "CM-HID-A002", "notes": "Cough", "severity": "low", "date": "2026-06-01"},
        ]
    )

    connector = CsvConnector(watch_folder, queue, FACILITY_ID)
    count = connector.scan_existing()

    assert count == 2
    assert queue.pending_count() == 2


def test_idempotency_prevents_duplicate_ingestion(watch_folder, queue):
    csv_path = os.path.join(watch_folder, "encounters_dedup.csv")
    row_data = [{"health_id": "CM-HID-B001", "notes": "Test", "severity": "low", "date": "2026-06-01"}]
    write_csv(csv_path, ["health_id", "notes", "severity", "date"], row_data)

    connector = CsvConnector(watch_folder, queue, FACILITY_ID)
    connector.scan_existing()
    connector.scan_existing()  # scan again

    assert queue.pending_count() == 1  # NOT 2


def test_unknown_type_file_is_skipped(watch_folder, queue):
    csv_path = os.path.join(watch_folder, "export_unknown.csv")
    write_csv(csv_path, ["col1", "col2"], [{"col1": "a", "col2": "b"}])

    connector = CsvConnector(watch_folder, queue, FACILITY_ID)
    count = connector.scan_existing()

    assert count == 0
    assert queue.pending_count() == 0
