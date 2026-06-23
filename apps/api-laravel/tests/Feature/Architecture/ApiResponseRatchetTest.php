<?php

namespace Tests\Feature\Architecture;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

/**
 * Ratchet guardrail for the API DTO/Resource migration (docs/API-RESOURCES.md).
 *
 * OpesCare is migrating every API endpoint off raw Eloquent serialization
 * (response()->json($model) / ['data' => $model]) and onto explicit
 * App\Http\Resources\* resources, so the wire contract is decoupled from the
 * database schema (see docs/API-VERSIONING.md §4).
 *
 * This test counts the remaining raw-variable serialization sites and asserts
 * the total never EXCEEDS the recorded baseline. The ratchet only turns one
 * way:
 *   • Add a new raw-model response  → count goes UP  → this test FAILS.
 *   • Convert an endpoint to a Resource → count goes DOWN → LOWER the baseline.
 *
 * A Resource return (e.g. ['data' => FooResource::make($m)]) does not match the
 * pattern, so converting an endpoint correctly drops it out of the count.
 *
 * When you legitimately reduce the count, set BASELINE to the new (lower)
 * number printed in the failure message. Never raise it.
 */
class ApiResponseRatchetTest extends TestCase
{
    /**
     * Measured 2026-06-23 after converting EncounterController to Resources.
     * This number must only ever go DOWN.
     */
    private const BASELINE = 220;

    public function test_raw_model_api_responses_do_not_exceed_baseline(): void
    {
        $dir = app_path('Http/Controllers/Api');
        $this->assertDirectoryExists($dir);

        $total = 0;
        $hotspots = [];

        $rii = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($rii as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') {
                continue;
            }

            $src = file_get_contents($file->getPathname());

            // 'data' => $var   (var is the whole value: followed by , ] or ) )
            $n  = preg_match_all('/\'data\'\s*=>\s*\$[a-zA-Z_][a-zA-Z0-9_]*\s*[,\]\)]/', $src);
            // ->json($var ...) / ->json($var)
            $n += preg_match_all('/->json\(\s*\$[a-zA-Z_][a-zA-Z0-9_]*\s*[,\)]/', $src);

            if ($n > 0) {
                $total += $n;
                $hotspots[$file->getFilename()] = $n;
            }
        }

        arsort($hotspots);
        $top = array_slice($hotspots, 0, 10, true);

        $this->assertLessThanOrEqual(
            self::BASELINE,
            $total,
            "Raw-model API responses ($total) exceeded the ratchet baseline (" . self::BASELINE . ").\n"
            . "A new endpoint is returning a raw Eloquent model instead of an "
            . "App\\Http\\Resources\\* resource. Wrap it in a resource — see docs/API-RESOURCES.md.\n"
            . "Current hotspots: " . json_encode($top)
        );

        // If you converted endpoints and the count dropped, tighten the ratchet:
        // lower BASELINE to $total. This keeps the guardrail honest.
        $this->assertGreaterThanOrEqual(
            $total,
            self::BASELINE,
            "Raw-model API responses dropped to $total (baseline " . self::BASELINE . "). "
            . "Lower ApiResponseRatchetTest::BASELINE to $total to lock in the progress."
        );
    }
}
