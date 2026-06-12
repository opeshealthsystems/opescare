<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Guard: tests must never touch a non-test database. A stale
        // bootstrap/cache/config.php can override phpunit.xml env vars and
        // point RefreshDatabase at the live database, destroying it.
        $database = (string) config('database.connections.' . config('database.default') . '.database');

        if ($database !== ':memory:' && !str_ends_with($database, '_test') && !str_contains($database, '_test_')) {
            $this->fail(sprintf(
                'Refusing to run tests against database "%s". Test databases must be ":memory:", end in "_test", or contain "_test_". ' .
                'Run "php artisan config:clear" if a cached config is overriding phpunit.xml.',
                $database
            ));
        }
    }
}
