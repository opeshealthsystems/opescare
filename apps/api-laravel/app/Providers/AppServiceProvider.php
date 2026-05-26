<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('verify', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        // Per-tenant / per-API-key rate limiting:
        // - Unauthenticated: 60 req/min by IP
        // - Authenticated users: 600 req/min by user ID
        // - Integration partners (X-Integration-Client-Id header): 1200 req/min by user ID
        RateLimiter::for('api', function (Request $request) {
            $user = $request->user();

            if (! $user) {
                return Limit::perMinute(60)->by($request->ip());
            }

            $isPartner = $request->header('X-Integration-Client-Id');
            $rate      = $isPartner ? 1200 : 600;

            return Limit::perMinute($rate)->by($user->id);
        });

        // Family: notify guardians when patient events occur
        $familyListener = new \App\Listeners\NotifyGuardiansOfPatientEvent();
        \App\Models\LabResult::created(fn($m) => $familyListener->handleLabResult($m));
        \App\Models\Appointment::created(fn($m) => $familyListener->handleAppointment($m));
        \App\Models\Appointment::updated(fn($m) => $familyListener->handleAppointmentUpdated($m));
        \App\Models\ConsentRequest::created(fn($m) => $familyListener->handleConsentRequest($m));
    }
}
