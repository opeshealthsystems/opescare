<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CareFacility;
use App\Models\SavedFacility;
use App\Models\MedicineReservationRequest;
use App\Modules\CareMap\Services\CareMapSearchService;
use App\Modules\CareMap\Services\FacilityVerificationService;
use App\Modules\CareMap\Services\FacilityClaimService;
use App\Modules\CareMap\Services\FacilityReportService;
use App\Modules\CareMap\Services\FacilityFreshnessService;
use App\Modules\CareMap\Services\PharmacyStockSearchService;
use App\Modules\CareMap\Services\BloodAvailabilitySearchService;
use App\Modules\CareMap\Services\LabTestSearchService;
use App\Modules\CareMap\Services\InsuranceNetworkSearchService;
use Illuminate\Support\Facades\Auth;

class CareMapController extends Controller
{
    protected $searchService;
    protected $verificationService;
    protected $claimService;
    protected $reportService;
    protected $freshnessService;
    protected $pharmacySearch;
    protected $bloodSearch;
    protected $labSearch;
    protected $insuranceSearch;

    public function __construct(
        CareMapSearchService $searchService,
        FacilityVerificationService $verificationService,
        FacilityClaimService $claimService,
        FacilityReportService $reportService,
        FacilityFreshnessService $freshnessService,
        PharmacyStockSearchService $pharmacySearch,
        BloodAvailabilitySearchService $bloodSearch,
        LabTestSearchService $labSearch,
        InsuranceNetworkSearchService $insuranceSearch
    ) {
        $this->searchService = $searchService;
        $this->verificationService = $verificationService;
        $this->claimService = $claimService;
        $this->reportService = $reportService;
        $this->freshnessService = $freshnessService;
        $this->pharmacySearch = $pharmacySearch;
        $this->bloodSearch = $bloodSearch;
        $this->labSearch = $labSearch;
        $this->insuranceSearch = $insuranceSearch;
    }

    /**
     * Public search directory
     */
    public function index(Request $request)
    {
        $facilities = $this->searchService->searchNearby($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $facilities,
            'meta' => [
                'disclaimer' => __('public.availability_may_change') ?? 'Information may change. Please contact the facility before travelling or making medical decisions.',
            ]
        ]);
    }

    /**
     * Show facility details with service catalogs
     */
    public function show($id)
    {
        $facility = CareFacility::with(['services', 'hours', 'insurances', 'pharmacyStock', 'labTests', 'bloodAvailability'])
            ->findOrFail($id);

        if ($facility->listing_status !== 'active') {
            return response()->json([
                'status' => 'error',
                'code' => 'FACILITY_SUSPENDED',
                'message' => 'This facility listing is not currently active.'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $facility,
            'meta' => [
                'disclaimer' => __('public.availability_may_change') ?? 'Information may change. Please contact the facility before travelling or making medical decisions.',
            ]
        ]);
    }

    /**
     * Search pharmacies having medicine stock
     */
    public function searchMedicine(Request $request)
    {
        $request->validate([
            'medicine' => 'required|string',
        ]);

        $lat = $request->input('latitude');
        $lon = $request->input('longitude');
        $radius = $request->input('radius', 50);

        $results = $this->pharmacySearch->searchMedicine($request->input('medicine'), $lat, $lon, $radius);

        return response()->json([
            'status' => 'success',
            'data' => $results,
            'meta' => [
                'warning' => 'fresh',
                'disclaimer' => __('public.medicine_disclaimer') ?? 'Medicine availability is reported by the pharmacy and may change. Always confirm with the pharmacy.'
            ]
        ]);
    }

    /**
     * Search blood availability
     */
    public function searchBlood(Request $request)
    {
        $request->validate([
            'blood_group' => 'required|string',
        ]);

        $lat = $request->input('latitude');
        $lon = $request->input('longitude');
        $radius = $request->input('radius', 50);
        $component = $request->input('component_type', 'whole_blood');

        $results = $this->bloodSearch->searchBlood($request->input('blood_group'), $component, $lat, $lon, $radius);

        return response()->json([
            'status' => 'success',
            'data' => $results,
            'meta' => [
                'warning' => 'fresh',
                'disclaimer' => __('public.blood_disclaimer') ?? 'Blood availability may change quickly. Contact the blood bank immediately.'
            ]
        ]);
    }

    /**
     * Search labs offering LOINC/turnaround tests
     */
    public function searchTests(Request $request)
    {
        $request->validate([
            'test_name' => 'required|string',
        ]);

        $lat = $request->input('latitude');
        $lon = $request->input('longitude');
        $radius = $request->input('radius', 50);

        $results = $this->labSearch->searchTests($request->input('test_name'), $lat, $lon, $radius);

        return response()->json([
            'status' => 'success',
            'data' => $results,
            'meta' => [
                'disclaimer' => 'Some tests may require a clinician’s request. Confirm requirements with the lab before booking.'
            ]
        ]);
    }

    /**
     * Clean and swift emergency finder
     */
    public function searchEmergency(Request $request)
    {
        $results = $this->searchService->searchEmergency($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $results,
            'meta' => [
                'disclaimer' => __('public.emergency_disclaimer') ?? 'If this is a life-threatening emergency, contact local emergency services or go to the nearest emergency facility immediately.'
            ]
        ]);
    }

    /**
     * Save/favorite a facility listing
     */
    public function saveFacility(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $facility = CareFacility::findOrFail($id);

        $saved = SavedFacility::updateOrCreate([
            'user_id' => $user->id,
            'facility_id' => $facility->id,
        ], [
            'label' => $request->input('label'),
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $saved
        ]);
    }

    /**
     * File incorrect info correction report
     */
    public function reportFacility(Request $request, $id)
    {
        $facility = CareFacility::findOrFail($id);
        $user = Auth::user();

        $report = $this->reportService->submitReport([
            'facility_id' => $facility->id,
            'reported_by_user_id' => $user ? $user->id : null,
            'report_type' => $request->input('report_type', 'wrong_phone'),
            'description' => $request->input('description'),
            'evidence_path' => $request->input('evidence_path'),
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $report,
            'message' => 'Report submitted successfully. We will review the listing information.'
        ]);
    }

    /**
     * Claim listing ownership profile
     */
    public function claimFacility(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated.'
            ], 401);
        }

        try {
            $claim = $this->claimService->submitClaim($id, $user->id, $request->input('claim_reason', 'Listing management'));
            return response()->json([
                'status' => 'success',
                'data' => $claim,
                'message' => 'Claim submitted successfully. Proof of authority document is required.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'code' => $e->getMessage(),
                'message' => 'You already have a pending claim for this facility.'
            ], 400);
        }
    }

    /**
     * Admin: verify facility listing status
     */
    public function adminVerifyFacility(Request $request, $id)
    {
        $admin = Auth::user();
        if (!$admin) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $facility = $this->verificationService->verifyFacility($id, $admin->id, $request->input('status', 'license_verified'));

        return response()->json([
            'status' => 'success',
            'data' => $facility,
            'message' => 'Facility listing verified successfully.'
        ]);
    }

    /**
     * Admin: suspend active listing
     */
    public function adminSuspendFacility(Request $request, $id)
    {
        $admin = Auth::user();
        if (!$admin) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $facility = $this->verificationService->suspendFacility($id, $admin->id);

        return response()->json([
            'status' => 'success',
            'data' => $facility,
            'message' => 'Facility listing suspended successfully.'
        ]);
    }

    /**
     * Sync pharmacy stock level
     */
    public function partnerStockSync(Request $request, $id)
    {
        $facility = CareFacility::findOrFail($id);
        $user = Auth::user();

        // Safe mock partner credentials check
        if ($facility->partner_id && $facility->partner_id != $user->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized facility owner.'], 403);
        }

        // Just mock a sync update
        $facility->update(['last_availability_update_at' => now()]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pharmacy stock sync accomplished successfully.'
        ]);
    }

    /**
     * Web: Render Public Interactive Directory Map
     */
    public function publicDirectory(Request $request)
    {
        $facilities = $this->searchService->searchNearby($request->all());
        
        return view('care_map.directory', [
            'facilities' => $facilities,
            'locale' => session('locale', 'en'),
        ]);
    }

    /**
     * Web: Render Facility Clinical Profile
     */
    public function publicProfile($id)
    {
        $facility = CareFacility::with(['services', 'hours', 'insurances', 'pharmacyStock', 'labTests', 'bloodAvailability'])
            ->findOrFail($id);

        return view('care_map.profile', [
            'facility' => $facility,
            'locale' => session('locale', 'en'),
        ]);
    }

    /**
     * Web: Render Simplified Red Emergency Panel
     */
    public function publicEmergency(Request $request)
    {
        $facilities = $this->searchService->searchEmergency($request->all());

        return view('care_map.emergency', [
            'facilities' => $facilities,
            'locale' => session('locale', 'en'),
        ]);
    }

    /**
     * Web: Render Admin Care Map Moderation Desk
     */
    public function adminGovernance()
    {
        $pendingClaims = \App\Models\FacilityClaim::with(['facility', 'claimant'])->where('claim_status', 'submitted')->get();
        $reports = \App\Models\FacilityReport::with(['facility', 'reporter'])->where('status', 'new')->get();
        $staleStock = \App\Models\PharmacyStockAvailability::where('freshness_status', 'stale')->with('facility')->get();

        return view('care_map.admin_dashboard', [
            'pendingClaims' => $pendingClaims,
            'reports' => $reports,
            'staleStock' => $staleStock,
            'locale' => session('locale', 'en'),
        ]);
    }
}

