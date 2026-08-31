<?php

use App\Http\Controllers\Api\Mobile\MobileMessagingController;
use App\Http\Controllers\Api\Mobile\MobileTelemedicineController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile Patient Routes — Messaging + Telemedicine
|
| Loaded from AppServiceProvider::boot() to avoid touching the sealed
| routes/api.php file (same pattern as routes/clinical.php). All routes
| use the auth.mobile middleware (AuthenticateMobilePatient) — patient_id
| always comes from the bearer token via $request->attributes, never from
| request input.
|--------------------------------------------------------------------------
*/

Route::prefix('api/mobile')->middleware('auth.mobile')->group(function () {
    // Messaging — patient-facing entry point onto the Messaging module.
    Route::get('messages/threads', [MobileMessagingController::class, 'index']);
    Route::post('messages/threads', [MobileMessagingController::class, 'start']);
    Route::get('messages/threads/{id}', [MobileMessagingController::class, 'show']);
    Route::post('messages/threads/{id}/messages', [MobileMessagingController::class, 'sendMessage']);

    // Telemedicine — patient-facing entry point onto the Telemedicine module.
    Route::get('telemedicine/consultations', [MobileTelemedicineController::class, 'index']);
    Route::get('telemedicine/consultations/{id}', [MobileTelemedicineController::class, 'show']);
    Route::post('telemedicine/consultations/{id}/consent', [MobileTelemedicineController::class, 'consent']);
    Route::post('telemedicine/consultations/{id}/join', [MobileTelemedicineController::class, 'join']);
    Route::post('telemedicine/consultations/{id}/end', [MobileTelemedicineController::class, 'end']);
});
