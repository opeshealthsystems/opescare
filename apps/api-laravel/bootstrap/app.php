<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Maintenance mode — checked on every request before routing.
        // Health endpoints (/up, /api/health) and bypass-token holders are exempt.
        // Must come AFTER ForceHttps so bypass tokens are sent over HTTPS.
        $middleware->prepend(\App\Http\Middleware\CheckMaintenanceMode::class);

        $middleware->prepend(\App\Http\Middleware\ForceHttps::class);
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\DemoSessionMiddleware::class,
            \App\Http\Middleware\DemoDataScope::class,
            \App\Http\Middleware\AddSecurityHeaders::class,
        ]);
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
        $middleware->api(append: [
            \App\Http\Middleware\DemoSessionMiddleware::class,
            \App\Http\Middleware\DemoDataScope::class,
            \App\Http\Middleware\AddSecurityHeaders::class,
            \App\Http\Middleware\LogApiUsage::class,
        ]);
        $middleware->append(\App\Http\Middleware\DatabaseHealthMiddleware::class);

        // Subdomain scope enforcement (enabled via SUBDOMAIN_ROUTING=true in .env)
        $middleware->prepend(\App\Http\Middleware\EnforceSubdomainScope::class);

        $middleware->alias([
            'auth.bearer'      => \App\Http\Middleware\VerifyBearerToken::class,
            'sdk.token'        => \App\Http\Middleware\VerifySdkToken::class,
            'throttle.client'  => \App\Http\Middleware\ThrottleByClient::class,
            'bridge.agent'     => \App\Http\Middleware\VerifyBridgeAgent::class,
            'portal.access'    => \App\Http\Middleware\EnsurePortalAccess::class,
            'facility.context' => \App\Http\Middleware\RequireFacilityContext::class,
            'consent.grant'    => \App\Http\Middleware\RequireConsentGrant::class,
            'auth.mobile'      => \App\Http\Middleware\AuthenticateMobilePatient::class,
            'guardian.context' => \App\Http\Middleware\GuardianAccessMiddleware::class,
            'api.admin'        => \App\Http\Middleware\RequireApiAdminRole::class,
            'verify.integration.client' => \App\Http\Middleware\VerifyIntegrationClient::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // ── Domain exceptions ───────────────────────────────────────────────
        $exceptions->renderable(function (\App\Exceptions\SlotFullException $e, $request) {
            return response()->json([
                'error_code' => 'SLOT_FULL',
                'message'    => $e->getMessage(),
            ], 409);
        });

        // ── Global API JSON error handler ───────────────────────────────────
        // All requests to /api/* or /fhir/* or Accept: application/json
        // return structured JSON — no HTML stack traces ever leak.
        $exceptions->renderable(function (\Throwable $e, \Illuminate\Http\Request $request) {
            $isApi = $request->is('api/*') || $request->is('fhir/*') || $request->expectsJson();
            if (! $isApi) {
                return null; // let Laravel handle web routes normally
            }

            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'status'     => 'error',
                    'error_code' => 'VALIDATION_FAILED',
                    'message'    => 'The request data failed validation.',
                    'errors'     => $e->errors(),
                ], 422);
            }

            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return response()->json([
                    'status'     => 'error',
                    'error_code' => 'NOT_FOUND',
                    'message'    => 'The requested resource was not found.',
                ], 404);
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                return response()->json([
                    'status'     => 'error',
                    'error_code' => 'ENDPOINT_NOT_FOUND',
                    'message'    => 'The API endpoint does not exist.',
                ], 404);
            }

            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return response()->json([
                    'status'     => 'error',
                    'error_code' => 'UNAUTHENTICATED',
                    'message'    => 'Authentication is required.',
                ], 401);
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                return response()->json([
                    'status'     => 'error',
                    'error_code' => 'HTTP_ERROR',
                    'message'    => $e->getMessage() ?: 'An HTTP error occurred.',
                ], $e->getStatusCode());
            }

            // Unexpected server error — never leak stack traces
            $message = config('app.debug')
                ? $e->getMessage()
                : 'An unexpected server error occurred. Reference your request ID for support.';

            return response()->json([
                'status'     => 'error',
                'error_code' => 'INTERNAL_SERVER_ERROR',
                'message'    => $message,
                'request_id' => $request->header('X-Request-Id', bin2hex(random_bytes(8))),
            ], 500);
        });
    })
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule) {
        $schedule->command('family:check-age-transitions')->daily();

        // DHIS2 monthly push — 1st of each month at 04:00 (Item 16)
        $schedule->command('opescare:push-dhis2 --month=' . now()->subMonth()->format('Y-m'))
                 ->monthlyOn(1, '04:00')
                 ->withoutOverlapping()
                 ->onSuccess(function () { \Log::info('DHIS2 monthly push completed'); })
                 ->onFailure(function () { \Log::error('DHIS2 monthly push failed'); });

        // Data retention purge — daily at 03:00 (Item 53)
        $schedule->command('opescare:purge-expired-data')
                 ->dailyAt('03:00')
                 ->withoutOverlapping()
                 ->runInBackground();

        // Notify expiring provider credentials — every Monday at 08:00 (Item 45)
        $schedule->command('opescare:notify-expiring-credentials --days=30')
                 ->weeklyOn(1, '08:00')
                 ->withoutOverlapping();
    })
    ->create();
