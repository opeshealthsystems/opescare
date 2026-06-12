"""
Bridge Agent sync loop — pushes queued records to OpesCare.

Pulls pending records from the local queue, posts them to the server's
/api/v1/bridge/sync endpoint, and marks each record as delivered or failed.
"""
import json
import logging
import time
from typing import List

import requests

from ..auth import build_auth_headers
from ..config import BridgeConfig
from ..queue.local_queue import LocalQueue, QueuedRecord

logger = logging.getLogger(__name__)

SYNC_ENDPOINT   = "/api/v1/bridge/sync"
BATCH_SIZE      = 50
REQUEST_TIMEOUT = 30


class Syncer:
    def __init__(self, config: BridgeConfig, queue: LocalQueue):
        self.config  = config
        self.queue   = queue
        self.session = requests.Session()
        self.session.headers.update({
            "Content-Type": "application/json",
            "User-Agent":   "opescare-bridge-agent/1.0",
        })

    def run_once(self) -> dict:
        """
        Process one sync batch. Returns a summary dict with counts.
        Safe to call repeatedly on a timer.
        """
        pending = self.queue.peek_pending(limit=BATCH_SIZE)

        if not pending:
            return {"delivered": 0, "failed": 0, "pending": 0}

        logger.info("Syncing %d records to OpesCare...", len(pending))

        delivered = 0
        failed    = 0

        for record in pending:
            self.queue.mark_sending(record.id)
            success, error = self._push_record(record)

            if success:
                self.queue.mark_delivered(record.id)
                delivered += 1
            else:
                self.queue.mark_failed(record.id, error or "Unknown error")
                failed += 1

        remaining = self.queue.pending_count()

        logger.info("Sync complete: delivered=%d failed=%d remaining=%d",
                    delivered, failed, remaining)

        return {"delivered": delivered, "failed": failed, "pending": remaining}

    def _push_record(self, record: QueuedRecord) -> tuple[bool, str | None]:
        """Push a single record. Returns (success, error_message)."""
        endpoint = self._endpoint_for_type(record.record_type)
        url      = self.config.server_url.rstrip("/") + endpoint

        headers = build_auth_headers(self.config.agent_id, self.config.agent_key)
        headers["Idempotency-Key"] = record.idempotency_key

        payload = {**record.payload, "record_type": record.record_type}

        try:
            response = self.session.post(
                url,
                json=payload,
                headers=headers,
                timeout=REQUEST_TIMEOUT,
            )

            if response.status_code in (200, 201):
                return True, None

            if response.status_code == 409:
                # Idempotency conflict — record already delivered, mark as done
                logger.debug("Idempotency conflict for record %d — already delivered", record.id)
                return True, None

            body = response.json() if response.content else {}
            error_msg = body.get("message", f"HTTP {response.status_code}")
            logger.warning("Failed to push record %d: %s", record.id, error_msg)
            return False, error_msg

        except requests.exceptions.ConnectionError as exc:
            logger.warning("Connection error pushing record %d: %s", record.id, exc)
            return False, f"ConnectionError: {exc}"
        except requests.exceptions.Timeout:
            logger.warning("Timeout pushing record %d", record.id)
            return False, "Request timed out"
        except Exception as exc:
            logger.error("Unexpected error pushing record %d: %s", record.id, exc)
            return False, str(exc)

    @staticmethod
    def _endpoint_for_type(record_type: str) -> str:
        return SYNC_ENDPOINT
