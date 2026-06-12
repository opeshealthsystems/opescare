"""
OpesCare Bridge Agent daemon.

Entry point: `opescare-bridge [--config bridge_config.json]`

The daemon:
  1. Loads config
  2. Initialises the local SQLite queue
  3. Starts the folder-watcher connector
  4. Runs a heartbeat loop (every heartbeat_interval_seconds)
  5. Runs a sync loop (every sync_interval_seconds)
  6. On SIGTERM / KeyboardInterrupt: graceful shutdown
"""
import argparse
import logging
import signal
import sys
import time

from .config import load_config
from .connectors.csv_connector import CsvConnector
from .queue.local_queue import LocalQueue
from .sync.heartbeat import Heartbeat
from .sync.syncer import Syncer


def _configure_logging(level: str) -> None:
    logging.basicConfig(
        level=getattr(logging, level.upper(), logging.INFO),
        format="%(asctime)s [%(levelname)s] %(name)s — %(message)s",
        datefmt="%Y-%m-%dT%H:%M:%S",
        stream=sys.stdout,
    )


class BridgeDaemon:
    def __init__(self, config_path: str = "bridge_config.json"):
        self.config    = load_config(config_path)
        self._running  = False
        self._queue    = None
        self._connector = None
        self._syncer   = None
        self._heartbeat = None

    def run(self) -> None:
        _configure_logging(self.config.log_level)
        logger = logging.getLogger(__name__)
        logger.info("OpesCare Bridge Agent starting (agent_id=%s)", self.config.agent_id)

        # Graceful shutdown on SIGTERM
        signal.signal(signal.SIGTERM, self._handle_signal)

        self._queue     = LocalQueue(self.config.queue_db_path)
        self._syncer    = Syncer(self.config, self._queue)
        self._heartbeat = Heartbeat(self.config)

        # Start CSV folder watcher if configured
        if self.config.connector.type == "csv_folder" and self.config.connector.watch_folder:
            self._connector = CsvConnector(
                self.config.connector.watch_folder,
                self._queue,
                self.config.facility_id,
                self.config.connector.file_pattern,
            )
            self._connector.start()

            # Process existing files at startup
            existing = self._connector.scan_existing()
            if existing:
                logger.info("Ingested %d existing rows at startup", existing)

        self._running = True

        last_heartbeat = 0.0
        last_sync      = 0.0

        logger.info("Bridge Agent running. Press Ctrl+C to stop.")

        try:
            while self._running:
                now = time.time()

                if now - last_heartbeat >= self.config.heartbeat_interval_seconds:
                    self._heartbeat.ping()
                    last_heartbeat = now

                if now - last_sync >= self.config.sync_interval_seconds:
                    summary = self._syncer.run_once()
                    # Retry failed records from previous cycles
                    retried = self._queue.requeue_failed(max_attempts=5)
                    if retried:
                        logger.info("Requeued %d failed records for retry", retried)
                    last_sync = now

                time.sleep(1)  # 1s tick — low CPU overhead

        except KeyboardInterrupt:
            logger.info("Keyboard interrupt received.")

        finally:
            self._shutdown()

    def _handle_signal(self, signum: int, frame) -> None:  # noqa: ANN001
        logging.getLogger(__name__).info("SIGTERM received — shutting down gracefully.")
        self._running = False

    def _shutdown(self) -> None:
        logger = logging.getLogger(__name__)
        logger.info("Shutting down Bridge Agent...")

        if self._connector:
            self._connector.stop()

        logger.info("Bridge Agent stopped cleanly.")


def main() -> None:
    parser = argparse.ArgumentParser(description="OpesCare Bridge Agent")
    parser.add_argument(
        "--config",
        default="bridge_config.json",
        help="Path to bridge_config.json (default: bridge_config.json)",
    )
    args = parser.parse_args()
    BridgeDaemon(args.config).run()


if __name__ == "__main__":
    main()
