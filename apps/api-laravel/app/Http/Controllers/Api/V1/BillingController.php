<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CashierSession;
use App\Models\Invoice;
use App\Models\Payment;
use App\Modules\Billing\Services\BillingService;
use App\Modules\Billing\Services\PaymentService;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function invoices(Request $request)
    {
        $query = Invoice::query()->orderByDesc('issued_at');

        if ($request->query('scope') === 'patient') {
            abort_unless($request->filled('patient_id'), 403, 'PATIENT_SCOPE_REQUIRES_PATIENT_ID');
            $query->where('patient_id', $request->query('patient_id'));
        } elseif ($request->filled('patient_id')) {
            $query->where('patient_id', $request->query('patient_id'));
        }

        if ($request->filled('facility_id')) {
            $query->where('facility_id', $request->query('facility_id'));
        }

        return response()->json(['data' => $query->get()->map(fn (Invoice $invoice) => $this->serializeInvoice($invoice))->values()]);
    }

    public function createInvoice(Request $request, BillingService $service)
    {
        $invoice = $service->createInvoice($request->validate([
            'patient_id' => ['required', 'uuid'],
            'facility_id' => ['required', 'uuid'],
            'visit_id' => ['nullable', 'uuid'],
            'insurance_covered_amount' => ['nullable', 'numeric', 'min:0'],
            'actor_id' => ['nullable', 'uuid'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.service_code' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
        ]));

        return response()->json(['data' => $this->serializeInvoice($invoice)], 201);
    }

    public function recordPayment(Request $request, Invoice $invoice, PaymentService $service)
    {
        $payment = $service->recordPayment($invoice, $request->validate([
            'amount' => ['required', 'numeric'],
            'method' => ['required', 'string'],
            'cashier_id' => ['nullable', 'uuid'],
            'cashier_session_id' => ['nullable', 'uuid'],
            'wallet_id' => ['nullable', 'uuid'],
        ]));

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
        $validated = $request->validate([
            'patient_id' => ['required', 'uuid'],
            'facility_id' => ['required', 'uuid'],
            'amount' => ['required', 'numeric'],
            'reason' => ['required', 'string'],
            'actor_id' => ['nullable', 'uuid'],
        ]);

        $wallet = $service->depositToWallet($validated['patient_id'], $validated['facility_id'], (float) $validated['amount'], $validated['reason'], $validated['actor_id'] ?? null);

        return response()->json(['data' => $wallet], 201);
    }

    public function openSession(Request $request, PaymentService $service)
    {
        $validated = $request->validate([
            'facility_id' => ['required', 'uuid'],
            'cashier_id' => ['required', 'uuid'],
        ]);

        return response()->json(['data' => $service->openCashierSession($validated['facility_id'], $validated['cashier_id'])], 201);
    }

    public function closeSession(CashierSession $session, Request $request, PaymentService $service)
    {
        $validated = $request->validate(['actor_id' => ['required', 'uuid']]);

        return response()->json(['data' => $service->closeCashierSession($session, $validated['actor_id'])]);
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
