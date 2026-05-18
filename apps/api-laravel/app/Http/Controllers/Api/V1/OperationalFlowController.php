<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\OperationalFlow\Services\PatientJourneyService;
use Illuminate\Http\Request;

class OperationalFlowController extends Controller
{
    public function patientJourney(Request $request, PatientJourneyService $service)
    {
        $result = $service->runPilotJourney($request->validate([
            'patient_id' => ['required', 'uuid'],
            'facility_id' => ['required', 'uuid'],
            'provider_id' => ['required', 'uuid'],
            'appointment_slot_id' => ['required', 'uuid'],
            'appointment_type' => ['required', 'string'],
            'queue_name' => ['required', 'string'],
            'consultation' => ['required', 'array'],
            'consultation.history_of_present_illness' => ['nullable', 'string'],
            'consultation.examination_findings' => ['nullable', 'string'],
            'consultation.treatment_plan' => ['nullable', 'string'],
            'lab_result_summary' => ['nullable', 'string'],
            'invoice_items' => ['required', 'array', 'min:1'],
            'invoice_items.*.description' => ['required', 'string'],
            'invoice_items.*.service_code' => ['nullable', 'string'],
            'invoice_items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'invoice_items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'invoice_items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'payment' => ['required', 'array'],
            'payment.amount' => ['required', 'numeric', 'min:0.01'],
            'payment.method' => ['required', 'string'],
            'actor_id' => ['nullable', 'uuid'],
        ]));

        return response()->json(['data' => $this->serialize($result)], 201);
    }

    private function serialize(array $result): array
    {
        return [
            'appointment' => [
                'id' => $result['appointment']->id,
                'status' => $result['appointment']->status,
                'visit_id' => $result['appointment']->visit_id,
            ],
            'queue_ticket' => [
                'id' => $result['queue_ticket']->id,
                'queue_number' => $result['queue_ticket']->queue_number,
                'status' => $result['queue_ticket']->status,
            ],
            'invoice' => [
                'id' => $result['invoice']->id,
                'status' => $result['invoice']->status,
                'balance_amount' => (float) $result['invoice']->balance_amount,
            ],
            'payment' => [
                'id' => $result['payment']->id,
                'status' => $result['payment']->status,
            ],
            'receipt' => [
                'id' => $result['receipt']->id,
                'receipt_number' => $result['receipt']->receipt_number,
            ],
            'document' => [
                'id' => $result['document']->id,
                'document_number' => $result['document']->document_number,
                'verification_code' => $result['document']->verification_code,
            ],
        ];
    }
}
