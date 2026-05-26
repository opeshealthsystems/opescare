<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        // Horizon::routeSmsNotificationsTo('15556667777');
        // Horizon::routeMailNotificationsTo('example@example.com');
        // Horizon::routeSlackNotificationsTo('slack-webhook-url', '#channel');
    }

    /**
     * Register the Horizon gate.
     *
     * This gate determines who can access Horizon in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', function ($user) {
            $allowedEmails = array_filter(
                explode(',', env('HORIZON_ADMIN_EMAILS', '')),
                fn($e) => trim($e) !== ''
            );

            // Fallback: allow any user with admin/super-admin role if gate is not configured
            if (empty($allowedEmails)) {
                return $user?->hasRole(['admin', 'super-admin']) ?? false;
            }

            return in_array(trim($user->email), array_map('trim', $allowedEmails));
        });
    }
}
