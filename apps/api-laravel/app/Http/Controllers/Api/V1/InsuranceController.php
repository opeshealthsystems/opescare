<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Insurance\Services\ClaimService;
use App\Modules\Insurance\Services\PreauthorizationService;
use App\Modules\Insurance\Services\InsuranceEligibilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * InsuranceController — Insurance Claims & Preauthorization API.
 *
 * SECURITY: Insurance users must not access full EMR.
 * Every claim response returns only minimum necessary data.
 */
class InsuranceController extends Controller
{
    public function __construct(
        private ClaimService               $claims,
        private PreauthorizationService    $preauth,
        private InsuranceEligibilityService $eligibility
    ) {}

    // ── Eligibility ─────────────────────────────────────────────────────────

    public function checkEligibility(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_id'       => ['required', 'uuid'],
            'insurance_plan_id' => ['required', 'uuid'],
        ]);

        return response()->json(
            $this->eligibility->check($validated['patient_id'], $validated['insurance_plan_id'])
        );
    }

    // ── Preauthorization ────────────────────────────────────────────────────

    public function requestPreauth(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_id'          => ['required', 'uuid'],
            'insurance_plan_id'   => ['required', 'uuid'],
            'facility_id'         => ['required', 'uuid'],
            'service_description' => ['required', 'string', 'max:500'],
            'estimated_cost'      => ['nullable', 'numeric', 'min:0'],
            'urgency'             => ['nullable', 'in:routine,urgent,emergency'],
            'clinical_notes'      => ['nullable', 'string'],
        ]);

        $preauth = $this->preauth->submit($validated, $request->user()->id);

        return response()->json($preauth, 201);
    }

    public function decidePreauth(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:approved,rejected,partial'],
            'reason'   => ['required', 'string'],
            'approved_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        return response()->json(
            $this->preauth->decide($id, $validated, $request->user()->id)
        );
    }

    // ── Claims ──────────────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $claims = $this->claims->listForUser($request->user(), $request->all());
        return response()->json($claims);
    }

    public function show(string $id, Request $request): JsonResponse
    {
        return response()->json(
            $this->claims->getMinimumNecessaryView($id, $request->user())
        );
    }

    public function submit(Request $request, string $id): JsonResponse
    {
        return response()->json(
            $this->claims->submit($id, $request->user()->id)
        );
    }

    public function decide(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:approved,rejected,partial,information_requested'],
            'reason'   => ['required', 'string'],
            'approved_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        return response()->json(
            $this->claims->decide($id, $validated, $request->user()->id)
        );
    }

    public function postPayment(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'amount'         => ['required', 'numeric', 'min:0.01'],
            'payment_date'   => ['required', 'date'],
            'reference'      => ['nullable', 'string'],
        ]);

        return response()->json(
            $this->claims->postPayment($id, $validated, $request->user()->id)
        );
    }
}
