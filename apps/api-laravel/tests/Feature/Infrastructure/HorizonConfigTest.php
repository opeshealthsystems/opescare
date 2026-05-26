<?php
namespace Tests\Feature\Infrastructure;

use Tests\TestCase;

class HorizonConfigTest extends TestCase
{
    public function test_horizon_config_exists(): void
    {
        $this->assertFileExists(config_path('horizon.php'));
    }

    public function test_queue_connection_is_redis_in_config(): void
    {
        $horizonConfig = config('horizon');
        $this->assertIsArray($horizonConfig);
        $this->assertArrayHasKey('environments', $horizonConfig);
    }

    public function test_horizon_has_production_environment(): void
    {
        $envs = config('horizon.environments');
        $this->assertArrayHasKey('production', $envs);
        $this->assertArrayHasKey('supervisor-1', $envs['production']);
    }
}
