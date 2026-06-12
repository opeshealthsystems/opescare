"""
CSV folder-watcher connector.

Watches a directory for new or modified CSV files matching a glob pattern.
Each row is transformed to an OpesCare record and enqueued in the local queue.

Supported CSV record types (detected by filename convention):
  patients_*.csv      → patient records
  encounters_*.csv    → encounter records
  lab_results_*.csv   → lab result records
  prescriptions_*.csv → prescription records
"""
import csv
import hashlib
import logging
import os
import time
from pathlib import Path
from typing import Dict, List, Optional

from watchdog.events import FileSystemEventHandler, FileCreatedEvent, FileModifiedEvent
from watchdog.observers import Observer

from ..queue.local_queue import LocalQueue

logger = logging.getLogger(__name__)


def _detect_record_type(filename: str) -> Optional[str]:
    name = filename.lower()
    if "patient"      in name: return "patient"
    if "encounter"    in name: return "encounter"
    if "lab_result"   in name: return "lab_result"
    if "prescription" in name: return "prescription"
    return None


def _row_to_idempotency_key(record_type: str, row: Dict[str, str]) -> str:
    """Deterministic key based on record type + source row content."""
    content = record_type + str(sorted(row.items()))
    return hashlib.sha256(content.encode()).hexdigest()[:32]


def _transform_row(record_type: str, row: Dict[str, str], facility_id: str) -> dict:
    """Map CSV columns to OpesCare payload fields."""
    base = {
        "facility_id":   facility_id,
        "source_system": "bridge_agent_csv",
        "source_file":   row.get("_source_file", ""),
    }

    if record_type == "patient":
        return {**base,
            "first_name":    row.get("first_name", ""),
            "last_name":     row.get("last_name", ""),
            "date_of_birth": row.get("dob") or row.get("date_of_birth", ""),
            "sex":           row.get("sex") or row.get("gender", ""),
            "phone":         row.get("phone", ""),
        }

    if record_type == "encounter":
        return {**base,
            "health_id":      row.get("health_id", ""),
            "encounter_type": row.get("type") or row.get("encounter_type", "general"),
            "clinical_note":  row.get("notes") or row.get("clinical_note", ""),
            "severity":       row.get("severity", "low"),
            "occurred_at":    row.get("date") or row.get("occurred_at", ""),
        }

    if record_type == "lab_result":
        return {**base,
            "health_id":      row.get("health_id", ""),
            "test_name":      row.get("test") or row.get("test_name", ""),
            "result_value":   row.get("result") or row.get("result_value", ""),
            "flagged":        row.get("flagged", "false").lower() == "true",
            "occurred_at":    row.get("date") or row.get("occurred_at", ""),
        }

    if record_type == "prescription":
        return {**base,
            "health_id":       row.get("health_id", ""),
            "medication_name": row.get("medicine") or row.get("medication_name", ""),
            "dose":            row.get("dose", ""),
            "frequency":       row.get("frequency", ""),
        }

    return {**base, **row}  # pass-through for unknown types


class CsvFileHandler(FileSystemEventHandler):
    """Watchdog handler that processes CSV files as they appear or change."""

    def __init__(self, queue: LocalQueue, facility_id: str, pattern: str = "*.csv"):
        self.queue       = queue
        self.facility_id = facility_id
        self.pattern     = pattern
        self._processed: set = set()  # track file+mtime combos to avoid re-processing

    def on_created(self, event: FileCreatedEvent) -> None:
        if not event.is_directory and event.src_path.endswith(".csv"):
            self._process_file(event.src_path)

    def on_modified(self, event: FileModifiedEvent) -> None:
        if not event.is_directory and event.src_path.endswith(".csv"):
            self._process_file(event.src_path)

    def _process_file(self, path: str) -> None:
        try:
            mtime     = os.path.getmtime(path)
            cache_key = f"{path}:{mtime}"

            if cache_key in self._processed:
                return

            record_type = _detect_record_type(Path(path).name)
            if not record_type:
                logger.debug("Skipping file with unknown record type: %s", path)
                return

            rows_queued = self._ingest_csv(path, record_type)
            self._processed.add(cache_key)

            logger.info("Ingested %d rows from %s (type: %s)", rows_queued, path, record_type)
        except Exception as exc:
            logger.error("Failed to process CSV file %s: %s", path, exc)

    def _ingest_csv(self, path: str, record_type: str) -> int:
        count = 0
        filename = Path(path).name

        with open(path, newline="", encoding="utf-8-sig") as f:
            reader = csv.DictReader(f)
            for row in reader:
                row["_source_file"] = filename
                payload         = _transform_row(record_type, row, self.facility_id)
                idempotency_key = _row_to_idempotency_key(record_type, row)

                self.queue.enqueue(record_type, payload, idempotency_key)
                count += 1

        return count


class CsvConnector:
    """Manages the watchdog observer for a folder-watch CSV connector."""

    def __init__(self, watch_folder: str, queue: LocalQueue, facility_id: str, pattern: str = "*.csv"):
        self.watch_folder = watch_folder
        self.handler      = CsvFileHandler(queue, facility_id, pattern)
        self.observer     = Observer()
        self._started     = False

    def start(self) -> None:
        os.makedirs(self.watch_folder, exist_ok=True)
        self.observer.schedule(self.handler, self.watch_folder, recursive=False)
        self.observer.start()
        self._started = True
        logger.info("CsvConnector watching: %s", self.watch_folder)

    def stop(self) -> None:
        if self._started:
            self.observer.stop()
            self.observer.join()
            self._started = False
            logger.info("CsvConnector stopped.")

    def scan_existing(self) -> int:
        """Process any CSV files already in the folder at startup."""
        total = 0
        for path in Path(self.watch_folder).glob("*.csv"):
            record_type = _detect_record_type(path.name)
            if record_type:
                total += self.handler._ingest_csv(str(path), record_type)
        return total
