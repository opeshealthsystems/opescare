<?php

namespace App\Providers;

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
        \Illuminate\Support\Facades\RateLimiter::for('verify', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(30)->by($request->ip());
        });

        // Family: notify guardians when patient events occur
        $familyListener = new \App\Listeners\NotifyGuardiansOfPatientEvent();
        \App\Models\LabResult::created(fn($m) => $familyListener->handleLabResult($m));
        \App\Models\Appointment::created(fn($m) => $familyListener->handleAppointment($m));
        \App\Models\Appointment::updated(fn($m) => $familyListener->handleAppointmentUpdated($m));
        \App\Models\ConsentRequest::created(fn($m) => $familyListener->handleConsentRequest($m));
    }
}
