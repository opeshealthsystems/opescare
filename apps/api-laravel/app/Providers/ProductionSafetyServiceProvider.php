<?php
namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class ProductionSafetyServiceProvider extends ServiceProvider
{
    /**
     * Boot the production safety service provider.
     * Logs critical warnings when dangerous configuration is detected in production.
     * Does NOT throw exceptions — only logs so the app can still start.
     */
    public function boot(): void
    {
        if (!$this->app->isProduction()) {
            return;
        }

        $this->checkAppDebug();
        $this->checkDemoMode();
        $this->checkMailer();
        $this->checkQueueDriver();
        $this->checkLogLevel();
    }

    private function checkAppDebug(): void
    {
        if (config('app.debug')) {
            Log::critical('production_safety_check_failed', [
                'check'   => 'APP_DEBUG',
                'message' => 'APP_DEBUG is true in production. Stack traces will be exposed to users.',
                'action'  => 'Set APP_DEBUG=false immediately.',
            ]);
        }
    }

    private function checkDemoMode(): void
    {
        if (config('demo.enabled', false)) {
            Log::critical('production_safety_check_failed', [
                'check'   => 'OPESCARE_DEMO_MODE',
                'message' => 'Demo mode is enabled in production. This allows unauthenticated demo access.',
                'action'  => 'Set OPESCARE_DEMO_MODE=false immediately.',
            ]);
        }
    }

    private function checkMailer(): void
    {
        $mailer = config('mail.default');
        if (in_array($mailer, ['log', 'null', 'array'], true)) {
            Log::critical('production_safety_check_failed', [
                'check'   => 'MAIL_MAILER',
                'message' => "Mail driver '{$mailer}' silently discards emails in production. Users will not receive notifications.",
                'action'  => 'Set MAIL_MAILER=smtp and configure SMTP credentials.',
            ]);
        }
    }

    private function checkQueueDriver(): void
    {
        if (config('queue.default') === 'sync') {
            Log::critical('production_safety_check_failed', [
                'check'   => 'QUEUE_CONNECTION',
                'message' => 'Queue connection is sync in production. Heavy jobs will block HTTP request processing.',
                'action'  => 'Set QUEUE_CONNECTION=redis or database and run queue workers.',
            ]);
        }
    }

    private function checkLogLevel(): void
    {
        if (config('logging.default') === 'daily' && config('logging.channels.daily.level') === 'debug') {
            Log::warning('production_safety_check_failed', [
                'check'   => 'LOG_LEVEL',
                'message' => 'Log level is debug in production. This may expose sensitive data in log files.',
                'action'  => 'Set LOG_LEVEL=warning.',
            ]);
        }
    }

    public function register(): void
    {
        // No bindings needed
    }
}
