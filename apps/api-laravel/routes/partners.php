<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Partner Governance API Routes
|--------------------------------------------------------------------------
|
| The routes for the Partner Contribution & Governance Module.
| Loaded within the v1/connect prefix.
|
*/

Route::prefix('partner-governance')->group(function () {
    // Test Route for Middleware
    Route::get('/test-access', function(\Illuminate\Http\Request $request) {
        return response()->json(['status' => 'success', 'partner_id' => $request->attributes->get('partner')->uuid]);
    })->middleware(\App\Http\Middleware\VerifyPartnerTrustLevel::class);

    // Partner Applications
    Route::get('/partners', [\App\Http\Controllers\Api\V1\Admin\PartnerGovernanceController::class, 'listPartners']);
    Route::post('/partners', function() { return response()->json(['message' => 'Stub: Create partner']); });
    Route::post('/partners/{id}/approve', [\App\Http\Controllers\Api\V1\Admin\PartnerGovernanceController::class, 'approvePartner']);
    Route::post('/partners/{id}/suspend', [\App\Http\Controllers\Api\V1\Admin\PartnerGovernanceController::class, 'suspendPartner']);
    
    // Partner Documents
    Route::get('/partners/{id}/documents', function() { return response()->json(['message' => 'Stub: Get documents']); });
    Route::post('/partners/{id}/documents/{documentId}/verify', [\App\Http\Controllers\Api\V1\Admin\PartnerGovernanceController::class, 'verifyDocument']);
    
    // Partner Agreements
    Route::get('/partners/{id}/agreements', function() { return response()->json(['message' => 'Stub: Get agreements']); });
    Route::post('/partners/{id}/agreements/{agreementId}/mark-signed', [\App\Http\Controllers\Api\V1\Admin\PartnerGovernanceController::class, 'markAgreementSigned']);
    
    // Integrations
    Route::post('/partners/{id}/integrations/{integrationId}/certify', [\App\Http\Controllers\Api\V1\Admin\PartnerGovernanceController::class, 'certifyIntegration']);
    Route::post('/partners/{id}/integrations/{integrationId}/enable-production', [\App\Http\Controllers\Api\V1\Admin\PartnerGovernanceController::class, 'enableProduction']);
    
    // Permissions
    Route::get('/partners/{id}/contribution-permissions', function() { return response()->json(['message' => 'Stub: Get permissions']); });
    Route::get('/partners/{id}/access-permissions', function() { return response()->json(['message' => 'Stub: Get access permissions']); });
    
    // Integrations
    Route::get('/partners/{id}/integrations', function() { return response()->json(['message' => 'Stub: Get integrations']); });
    
    // Governance Cases
    Route::get('/cases', function() { return response()->json(['message' => 'Stub: Get governance cases']); });
    
    // Dashboards
    Route::get('/dashboard', function() { return response()->json(['message' => 'Stub: Dashboard']); });
});
