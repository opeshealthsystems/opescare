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
        $middleware->prepend(\App\Http\Middleware\ForceHttps::class);
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\DemoSessionMiddleware::class,
            \App\Http\Middleware\DemoDataScope::class,
            \App\Http\Middleware\AddSecurityHeaders::class,
        ]);
        $middleware->api(append: [
            \App\Http\Middleware\DemoSessionMiddleware::class,
            \App\Http\Middleware\DemoDataScope::class,
            \App\Http\Middleware\AddSecurityHeaders::class,
            \App\Http\Middleware\LogApiUsage::class,
        ]);
        $middleware->append(\App\Http\Middleware\DatabaseHealthMiddleware::class);

        $middleware->alias([
            'sdk.token'        => \App\Http\Middleware\VerifySdkToken::class,
            'throttle.client'  => \App\Http\Middleware\ThrottleByClient::class,
            'bridge.agent'     => \App\Http\Middleware\VerifyBridgeAgent::class,
            'portal.access'    => \App\Http\Middleware\EnsurePortalAccess::class,
            'facility.context' => \App\Http\Middleware\RequireFacilityContext::class,
            'consent.grant'    => \App\Http\Middleware\RequireConsentGrant::class,
            'auth.mobile'      => \App\Http\Middleware\AuthenticateMobilePatient::class,
            'guardian.context' => \App\Http\Middleware\GuardianAccessMiddleware::class,
            'api.admin'        => \App\Http\Middleware\RequireApiAdminRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (\App\Exceptions\SlotFullException $e, $request) {
            return response()->json([
                'error_code' => 'SLOT_FULL',
                'message'    => $e->getMessage(),
            ], 409);
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
