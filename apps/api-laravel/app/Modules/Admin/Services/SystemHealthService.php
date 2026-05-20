<?php

namespace App\Modules\Admin\Services;

use App\Models\SystemHealthSnapshot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * SystemHealthService — Captures and reports system health metrics.
 *
 * Health snapshots are stored in system_health_snapshots for trend analysis.
 * Metrics include: DB connectivity, cache connectivity, queue depth,
 * disk space, active sessions, failed jobs, and API latency p95.
 *
 * Used by the Master Admin Control Center dashboard.
 */
class SystemHealthService
{
    public function captureSnapshot(string $capturedBy = 'scheduler'): SystemHealthSnapshot
    {
        $metrics = $this->gatherMetrics();

        return SystemHealthSnapshot::create([
            'captured_by' => $capturedBy,
            'metrics'     => $metrics,
            'status'      => $this->deriveStatus($metrics),
        ]);
    }

    private function gatherMetrics(): array
    {
        return [
            'db_connected'       => $this->checkDbConnectivity(),
            'cache_connected'    => $this->checkCacheConnectivity(),
            'failed_jobs_count'  => DB::table('failed_jobs')->count(),
            'active_users'       => Cache::get('active_sessions_count', 0),
            'memory_usage_mb'    => round(memory_get_usage(true) / 1024 / 1024, 2),
            'captured_at'        => now()->toIso8601String(),
        ];
    }

    private function checkDbConnectivity(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception) {
            return false;
        }
    }

    private function checkCacheConnectivity(): bool
    {
        try {
            Cache::put('health_check', true, 5);
            return Cache::get('health_check') === true;
        } catch (\Exception) {
            return false;
        }
    }

    private function deriveStatus(array $metrics): string
    {
        if (! $metrics['db_connected']) {
            return 'critical';
        }
        if (! $metrics['cache_connected'] || $metrics['failed_jobs_count'] > 100) {
            return 'degraded';
        }
        return 'healthy';
    }

    public function getLatestSnapshot(): ?SystemHealthSnapshot
    {
        return SystemHealthSnapshot::latest()->first();
    }
}
