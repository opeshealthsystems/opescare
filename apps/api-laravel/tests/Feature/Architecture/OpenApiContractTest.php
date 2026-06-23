<?php

namespace Tests\Feature\Architecture;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Contract guardrail for public/openapi.yaml.
 *
 * Asserts every documented (method, path) resolves to a real registered route,
 * so the published spec can never advertise an endpoint that does not exist
 * ("no phantom endpoints"). Reports honest coverage vs the real API surface.
 *
 * The spec is parsed with a small dependency-free line extractor (the project
 * has no YAML library installed). It relies on the file's 2-space-per-level
 * indentation; a malformed block simply isn't extracted and the structure
 * assertions below catch gross breakage.
 */
class OpenApiContractTest extends TestCase
{
    private const HTTP_VERBS = ['get', 'post', 'put', 'patch', 'delete', 'options', 'head'];

    public function test_spec_has_required_top_level_structure(): void
    {
        $raw = file_get_contents(public_path('openapi.yaml'));
        $this->assertMatchesRegularExpression('/^openapi:\s*3\./m', $raw, 'openapi version must be 3.x');
        $this->assertStringContainsString("\npaths:", $raw, 'spec must declare a paths block');
        $this->assertStringContainsString("\ncomponents:", $raw, 'spec must declare a components block');
    }

    public function test_every_documented_path_is_a_real_route(): void
    {
        $documented = $this->extractDocumentedOperations();
        $this->assertNotEmpty($documented, 'No operations extracted from openapi.yaml — parser or file is broken.');

        $real = $this->realRouteMap();

        $phantom = [];
        foreach ($documented as [$method, $path]) {
            $norm = $this->normalize($path);
            if (! isset($real[$norm]) || ! in_array(strtoupper($method), $real[$norm], true)) {
                $phantom[] = strtoupper($method) . ' ' . $path;
            }
        }

        $this->assertSame(
            [],
            $phantom,
            "openapi.yaml documents endpoints that do not resolve to a real route:\n  "
            . implode("\n  ", $phantom)
            . "\nFix the path/method or remove the operation — the spec must never advertise a phantom endpoint."
        );

        // Honest coverage report (stderr, non-failing).
        $apiRouteCount = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($r) => str_starts_with($r->uri(), 'api/'))
            ->count();
        fwrite(STDERR, sprintf(
            "[openapi] documented operations: %d | total api routes: %d (~%.1f%% of surface)\n",
            count($documented),
            $apiRouteCount,
            100 * count($documented) / max(1, $apiRouteCount)
        ));
    }

    /** @return array<int, array{0:string,1:string}> list of [method, path]. */
    private function extractDocumentedOperations(): array
    {
        $lines = preg_split('/\R/', file_get_contents(public_path('openapi.yaml')));
        $ops = [];
        $current = null;
        $inPaths = false;

        foreach ($lines as $line) {
            if (preg_match('/^paths:\s*$/', $line)) {
                $inPaths = true;
                continue;
            }
            if ($inPaths && preg_match('/^\S/', $line)) {
                $inPaths = false; // a new top-level key ends the paths block
            }
            if (! $inPaths) {
                continue;
            }

            if (preg_match('#^  (/[^:]+):\s*$#', $line, $m)) {
                $current = $m[1];
            } elseif ($current !== null && preg_match('/^    ([a-z]+):\s*$/', $line, $m)) {
                if (in_array($m[1], self::HTTP_VERBS, true)) {
                    $ops[] = [$m[1], $current];
                }
            }
        }

        return $ops;
    }

    /** @return array<string, string[]> normalized-uri => [METHODS]. */
    private function realRouteMap(): array
    {
        $map = [];
        foreach (Route::getRoutes()->getRoutes() as $route) {
            $uri = $this->normalize('/' . ltrim($route->uri(), '/'));
            $map[$uri] = array_values(array_unique(array_merge($map[$uri] ?? [], $route->methods())));
        }

        return $map;
    }

    private function normalize(string $path): string
    {
        $path = '/' . ltrim($path, '/');

        return preg_replace('/\{[^}]+\}/', '{}', $path);
    }
}
