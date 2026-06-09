<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
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
        // Clinical module routes (Group 3 + wired modules) — loaded here to
        // avoid touching the sealed routes/api.php file.
        Route::middleware('api')
            ->group(base_path('routes/clinical.php'));

        RateLimiter::for('verify', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        // Patient portal rate limit — prevents QR spam, profile-scrape, and enumeration.
        // 120 requests per minute per authenticated user is generous for normal use
        // while blocking automated abuse (a QR-flood attack would hit this instantly).
        RateLimiter::for('portal', function (Request $request) {
            $user = $request->user();
            return $user
                ? Limit::perMinute(120)->by('portal|' . $user->id)
                : Limit::perMinute(20)->by('portal|' . $request->ip());
        });

        // Tighter limit specifically for QR generation — 10 per minute per user.
        // A patient generating more than 10 QRs per minute is an abuse signal.
        RateLimiter::for('portal.qr', function (Request $request) {
            $user = $request->user();
            return $user
                ? Limit::perMinute(10)->by('qr|' . $user->id)
                : Limit::perMinute(3)->by('qr|' . $request->ip());
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

        // FHIR Subscriptions: dispatch async notifications when clinical resources change.
        // Uses Laravel's Eloquent closure-based observers to avoid a proliferation of
        // observer class registrations. The job is queued (fhir-notifications queue)
        // so delivery never blocks the originating request.
        $fhirObserver = new \App\Observers\FhirSubscriptionObserver(
            app(\App\Modules\Fhir\Services\FhirService::class)
        );
        \App\Models\Patient::created([$fhirObserver, 'created']);
        \App\Models\Patient::updated([$fhirObserver, 'updated']);
        \App\Models\Visit::created([$fhirObserver, 'created']);
        \App\Models\Visit::updated([$fhirObserver, 'updated']);
        \App\Models\LabOrder::created([$fhirObserver, 'created']);
        \App\Models\LabOrder::updated([$fhirObserver, 'updated']);
        \App\Models\Prescription::created([$fhirObserver, 'created']);
        \App\Models\Prescription::updated([$fhirObserver, 'updated']);
        \App\Models\ImmunizationRecord::created([$fhirObserver, 'created']);
        \App\Models\ImmunizationRecord::updated([$fhirObserver, 'updated']);
        \App\Models\AllergyRecord::created([$fhirObserver, 'created']);
        \App\Models\AllergyRecord::updated([$fhirObserver, 'updated']);
        \App\Models\Diagnosis::created([$fhirObserver, 'created']);
        \App\Models\Diagnosis::updated([$fhirObserver, 'updated']);
    }
}
