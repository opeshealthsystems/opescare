<?php

namespace App\Http\Controllers\Api\V1\Reports;

use App\Http\Controllers\Controller;
use App\Services\Patient\SurveyService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SurveyReportController extends Controller
{
    public function __construct(private readonly SurveyService $service)
    {
    }

    /** GET /api/v1/reports/surveys/satisfaction?facility_id=&from=&to= */
    public function satisfaction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'facility_id' => 'required|uuid|exists:facilities,id',
            'from'        => 'required|date',
            'to'          => 'required|date|after_or_equal:from',
        ]);

        $scores = $this->service->getSatisfactionScore(
            $validated['facility_id'],
            Carbon::parse($validated['from']),
            Carbon::parse($validated['to']),
        );

        return response()->json(['data' => $scores]);
    }
}
