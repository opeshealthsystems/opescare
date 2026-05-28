<?php

namespace App\Http\Controllers\Api\V1\Reports;

use App\Http\Controllers\Controller;
use App\Services\Reports\RevenueCycleService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RevenueCycleController extends Controller
{
    public function __construct(private readonly RevenueCycleService $service) {}

    public function summary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'facility_id' => ['required', 'uuid', 'exists:facilities,id'],
            'from'        => ['sometimes', 'date_format:Y-m-d'],
            'to'          => ['sometimes', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $from = isset($validated['from'])
            ? Carbon::parse($validated['from'])->startOfDay()
            : Carbon::now()->startOfMonth();
        $to = isset($validated['to'])
            ? Carbon::parse($validated['to'])->endOfDay()
            : Carbon::now()->endOfDay();

        return response()->json(['data' => $this->service->getSummary($validated['facility_id'], $from, $to)]);
    }

    public function aging(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'facility_id' => ['required', 'uuid', 'exists:facilities,id'],
        ]);

        return response()->json(['data' => $this->service->getAgingReport($validated['facility_id'])]);
    }

    public function denials(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'facility_id' => ['required', 'uuid', 'exists:facilities,id'],
            'from'        => ['sometimes', 'date_format:Y-m-d'],
            'to'          => ['sometimes', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $from = isset($validated['from'])
            ? Carbon::parse($validated['from'])->startOfDay()
            : Carbon::now()->subMonths(3)->startOfDay();
        $to = isset($validated['to'])
            ? Carbon::parse($validated['to'])->endOfDay()
            : Carbon::now()->endOfDay();

        return response()->json(['data' => $this->service->getDenialReasons($validated['facility_id'], $from, $to)]);
    }

    public function trend(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'facility_id' => ['required', 'uuid', 'exists:facilities,id'],
            'months'      => ['sometimes', 'integer', 'min:1', 'max:24'],
        ]);

        return response()->json([
            'data' => $this->service->getMonthlyTrend(
                $validated['facility_id'],
                (int) ($validated['months'] ?? 6)
            ),
        ]);
    }
}
