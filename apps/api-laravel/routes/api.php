<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\IntegrationController;
use App\Http\Middleware\VerifyIntegrationClient;

Route::prefix('v1')->middleware(VerifyIntegrationClient::class)->group(function () {
    Route::post('/records/encounters', [IntegrationController::class, 'pushEncounter']);
});
