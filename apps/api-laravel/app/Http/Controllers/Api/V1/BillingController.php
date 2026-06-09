<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CashierSession;
use App\Models\ConsentGrant;
use App\Models\Invoice;
use App\Models\Payment;
use App\Modules\Billing\Services\BillingService;
use App\Modules\Billing\Services\PaymentReconciliationService;
use App\Modules\Billing\Services\PaymentService;
use App\Services\Documents\DocumentIssuanceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function invoices(Request $request)
    {
        $query = Invoice::query()->orderByDesc('issued_at');

        // Security: verify the calling integration client holds a consent grant
        // for the requested patient before filtering — prevents IDOR enumeration.
        // Accepts the grant either from request attributes (set by RequireConsentGrant middleware
        // if applied) or directly from the X-Consent-Grant-Id header for this unauthenticated route.
        if ($request->filled('patient_id')) {
            $patientId    = $request->query('patient_id');

            // Prefer middleware-resolved grant; fall back to header-based lookup
            $consentGrant = $request->attributes->get('consent_grant');

            if (!$consentGrant && $request->header('X-Consent-Grant-Id')) {
                $consentGrant = ConsentGrant::withoutGlobalScopes()
                    ->where('id', $request->header('X-Consent-Grant-Id'))
                    ->where('status', 'active')
                    ->where('expires_at', '>=', Carbon::now())
                    ->first();
            }

            if (!$consentGrant || $consentGrant->patient_id !== $patientId) {
                return response()->json([
                    'error'   => 'forbidden',
                    'message' => 'You do not have a consent grant to access billing records for this patient.',
                ], 403);
            }
        }

        if ($request->query('scope') === 'patient') {
            abort_unless($request->filled('patient_id'), 403, 'PATIENT_SCOPE_REQUIRES_PATIENT_ID');
            $query->where('patient_id', $request->query('patient_id'));
        } elseif ($request->filled('patient_id')) {
            $query->where('patient_id', $request->query('patient_id'));
        }

        $facilityId = $request->attributes->get('facility_id');
        if (!$facilityId) {
            return response()->json(['error' => 'forbidden', 'message' => 'facility_id could not be resolved from authentication context.'], 403);
        }
        $query->where('facility_id', $facilityId);

        return response()->json(['data' => $query->get()->map(fn (Invoice $invoice) => $this->serializeInvoice($invoice))->values()]);
    }

    public function createInvoice(Request $request, BillingService $service, DocumentIssuanceService $issuance)
    {
        $facilityId = $request->attributes->get('facility_id');
        if (!$facilityId) {
            return response()->json(['error' => 'forbidden', 'message' => 'facility_id could not be resolved from authentication context.'], 403);
        }

        $validated = $request->validate([
            'patient_id' => ['required', 'uuid'],
            'visit_id' => ['nullable', 'uuid'],
            'insurance_covered_amount' => ['nullable', 'numeric', 'min:0'],
            'actor_id' => ['nullable', 'uuid'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.service_code' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
        ]);
        $validated['facility_id'] = $facilityId;

        $invoice = $service->createInvoice($validated);

        try {
            $issuance->issueFromModel(
                'INV',
                'Invoice #' . ($invoice->invoice_number ?? $invoice->id),
                ['invoice_id' => $invoice->id, 'patient_id' => $invoice->patient_id, 'total_amount' => $invoice->total_amount, 'items' => $validated['items']],
                $facilityId,
                $invoice->patient_id,
                null,
                $validated['actor_id'] ?? null
            );
        } catch (\Throwable) {}

        return response()->json(['data' => $this->serializeInvoice($invoice)], 201);
    }

    public function recordPayment(Request $request, Invoice $invoice, PaymentService $service, DocumentIssuanceService $issuance)
    {
        $facilityId = $request->attributes->get('facility_id');

        $payment = $service->recordPayment($invoice, $request->validate([
            'amount' => ['required', 'numeric'],
            'method' => ['required', 'string'],
            'cashier_id' => ['nullable', 'uuid'],
            'cashier_session_id' => ['nullable', 'uuid'],
            'wallet_id' => ['nullable', 'uuid'],
        ]));

        if ($facilityId) {
            try {
                $issuance->issueFromModel(
                    'REC',
                    'Receipt #' . ($payment->receipt_number ?? $payment->id),
                    ['payment_id' => $payment->id, 'invoice_id' => $invoice->id, 'patient_id' => $invoice->patient_id, 'amount' => $payment->amount, 'method' => $payment->method],
                    $facilityId,
                    $invoice->patient_id
                );
            } catch (\Throwable) {}
        }

        return response()->json(['data' => $this->serializePayment($payment)], 201);
    }

    public function refund(Request $request, Payment $payment, PaymentService $service)
    {
        $refund = $service->refund($payment, $request->validate([
            'amount' => ['required', 'numeric'],
            'reason' => ['required', 'string'],
            'actor_id' => ['nullable', 'uuid'],
        ]));

        return response()->json(['data' => $refund]);
    }

    public function depositWallet(Request $request, PaymentService $service)
    {
        $facilityId = $request->attributes->get('facility_id');
        if (!$facilityId) {
            return response()->json(['error' => 'forbidden', 'message' => 'facility_id could not be resolved from authentication context.'], 403);
        }

        $validated = $request->validate([
            'patient_id' => ['required', 'uuid'],
            'amount' => ['required', 'numeric'],
            'reason' => ['required', 'string'],
            'actor_id' => ['nullable', 'uuid'],
        ]);

        $wallet = $service->depositToWallet($validated['patient_id'], $facilityId, (float) $validated['amount'], $validated['reason'], $validated['actor_id'] ?? null);

        return response()->json(['data' => $wallet], 201);
    }

    public function openSession(Request $request, PaymentService $service)
    {
        $facilityId = $request->attributes->get('facility_id');
        if (!$facilityId) {
            return response()->json(['error' => 'forbidden', 'message' => 'facility_id could not be resolved from authentication context.'], 403);
        }

        $validated = $request->validate([
            'cashier_id' => ['required', 'uuid'],
        ]);

        return response()->json(['data' => $service->openCashierSession($facilityId, $validated['cashier_id'])], 201);
    }

    public function closeSession(CashierSession $session, Request $request, PaymentService $service)
    {
        $validated = $request->validate(['actor_id' => ['required', 'uuid']]);

        return response()->json(['data' => $service->closeCashierSession($session, $validated['actor_id'])]);
    }

    /**
     * Reconcile a cashier session at end of shift.
     * Compares physical cash + electronic total against system expected total.
     * Discrepancies are flagged for finance review.
     *
     * Body: { physical_cash_amount, electronic_total, closed_by, notes? }
     */
    public function reconcileSession(CashierSession $session, Request $request, PaymentReconciliationService $service): JsonResponse
    {
        $validated = $request->validate([
            'physical_cash_amount' => ['required', 'numeric', 'min:0'],
            'electronic_total'     => ['required', 'numeric', 'min:0'],
            'closed_by'            => ['required', 'uuid'],
            'notes'                => ['nullable', 'string', 'max:2000'],
        ]);

        // Prevent reconciliation of already-closed session
        if ($session->status === 'closed') {
            return response()->json(['message' => 'This cashier session is already closed and reconciled.'], 422);
        }

        $reconciliation = $service->reconcile(
            $session->id,
            (float) $validated['physical_cash_amount'],
            (float) $validated['electronic_total'],
            $validated['closed_by'],
            $validated['notes'] ?? null
        );

        return response()->json([
            'message' => $reconciliation->status === 'balanced'
                ? 'Session reconciled — balanced.'
                : 'Session reconciled — discrepancy flagged for finance review.',
            'data' => [
                'id'              => $reconciliation->id,
                'session_id'      => $reconciliation->cashier_session_id,
                'expected_total'  => (float) $reconciliation->expected_total,
                'physical_cash'   => (float) $reconciliation->physical_cash,
                'electronic_total' => (float) $reconciliation->electronic_total,
                'actual_total'    => (float) $reconciliation->actual_total,
                'discrepancy'     => (float) $reconciliation->discrepancy,
                'status'          => $reconciliation->status,
                'reconciled_by'   => $reconciliation->reconciled_by,
                'reconciled_at'   => $reconciliation->reconciled_at?->toISOString(),
                'notes'           => $reconciliation->notes,
            ],
        ]);
    }

    private function serializeInvoice(Invoice $invoice): array
    {
        return [
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'patient_id' => $invoice->patient_id,
            'facility_id' => $invoice->facility_id,
            'visit_id' => $invoice->visit_id,
            'status' => $invoice->status,
            'subtotal_amount' => (float) $invoice->subtotal_amount,
            'discount_amount' => (float) $invoice->discount_amount,
            'insurance_covered_amount' => (float) $invoice->insurance_covered_amount,
            'patient_responsibility_amount' => (float) $invoice->patient_responsibility_amount,
            'paid_amount' => (float) $invoice->paid_amount,
            'balance_amount' => (float) $invoice->balance_amount,
        ];
    }

    private function serializePayment(Payment $payment): array
    {
        return [
            'id' => $payment->id,
            'invoice_id' => $payment->invoice_id,
            'patient_id' => $payment->patient_id,
            'facility_id' => $payment->facility_id,
            'method' => $payment->method,
            'status' => $payment->status,
            'amount' => (float) $payment->amount,
            'refunded_amount' => (float) $payment->refunded_amount,
        ];
    }
}
