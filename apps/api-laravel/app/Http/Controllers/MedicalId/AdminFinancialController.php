<?php

namespace App\Http\Controllers\MedicalId;

use App\Models\Facility;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\PaymentReversal;
use App\Models\Receipt;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminFinancialController extends Controller
{
    private function actorId(): string
    {
        return Auth::id() ?? session('auth_email', 'system');
    }

    // ── Dashboard ─────────────────────────────────────────────────

    public function index(Request $request): View|RedirectResponse
    {
        if (!Auth::check()) return redirect()->route('login');

        $from = $request->input('from_date')
            ? Carbon::parse($request->input('from_date'))->startOfDay()
            : Carbon::now()->startOfMonth();
        $to = $request->input('to_date')
            ? Carbon::parse($request->input('to_date'))->endOfDay()
            : Carbon::now()->endOfDay();

        // Summary KPIs
        $totalCollected = Payment::whereBetween('created_at', [$from, $to])
            ->whereIn('status', ['successful', 'completed'])->sum('amount');

        $totalPending = Payment::whereBetween('created_at', [$from, $to])
            ->where('status', 'pending')->sum('amount');

        $totalFailed = Payment::whereBetween('created_at', [$from, $to])
            ->where('status', 'failed')->count();

        $totalRefunded = Payment::whereBetween('created_at', [$from, $to])
            ->sum('refunded_amount');

        // Revenue by gateway
        $byGateway = Payment::whereBetween('created_at', [$from, $to])
            ->whereIn('status', ['successful', 'completed'])
            ->selectRaw("COALESCE(gateway, method, 'unknown') as gw, COUNT(*) as txn_count, SUM(amount) as total")
            ->groupBy('gw')
            ->orderByDesc('total')
            ->get();

        // Revenue by service type
        $byService = Payment::whereBetween('created_at', [$from, $to])
            ->whereIn('status', ['successful', 'completed'])
            ->selectRaw("COALESCE(service_type, 'unspecified') as svc, COUNT(*) as txn_count, SUM(amount) as total")
            ->groupBy('svc')
            ->orderByDesc('total')
            ->get();

        // Revenue by facility
        $byFacility = Payment::whereBetween('created_at', [$from, $to])
            ->whereIn('status', ['successful', 'completed'])
            ->with('facility:id,name')
            ->selectRaw("facility_id, COUNT(*) as txn_count, SUM(amount) as total")
            ->groupBy('facility_id')
            ->orderByDesc('total')
            ->get();

        // Daily trend (last 30 days or date range)
        $dailyTrend = Payment::whereBetween('created_at', [$from, $to])
            ->whereIn('status', ['successful', 'completed'])
            ->selectRaw("DATE(created_at) as day, SUM(amount) as total, COUNT(*) as txn_count")
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // Recent payments
        $recentPayments = Payment::with(['patient:id,first_name,last_name,health_id', 'facility:id,name', 'cashier:id,name'])
            ->latest()->limit(15)->get();

        $facilities = Facility::orderBy('name')->get(['id', 'name']);

        return view('portals.admin.financial.index', compact(
            'from', 'to',
            'totalCollected', 'totalPending', 'totalFailed', 'totalRefunded',
            'byGateway', 'byService', 'byFacility', 'dailyTrend',
            'recentPayments', 'facilities'
        ));
    }

    // ── All Payments — full transparency table ─────────────────────

    public function payments(Request $request): View|RedirectResponse
    {
        if (!Auth::check()) return redirect()->route('login');

        $query = Payment::with([
            'patient:id,first_name,last_name,health_id',
            'facility:id,name',
            'cashier:id,name',
            'invoice:id,invoice_number,status',
            'receipts:id,payment_id,receipt_number,issued_at',
        ]);

        // Filters
        if ($v = $request->input('gateway'))      $query->where(DB::raw("COALESCE(gateway, method)"), $v);
        if ($v = $request->input('status'))       $query->where('status', $v);
        if ($v = $request->input('service_type')) $query->where('service_type', $v);
        if ($v = $request->input('facility_id'))  $query->where('facility_id', $v);
        if ($v = $request->input('device_type'))  $query->where('device_type', $v);
        if ($v = $request->input('payer_phone'))  $query->where('payer_phone', 'like', "%{$v}%");
        if ($v = $request->input('search')) {
            $query->where(function ($q) use ($v) {
                $q->where('payment_reference', 'like', "%{$v}%")
                  ->orWhere('gateway_transaction_id', 'like', "%{$v}%")
                  ->orWhere('payer_phone', 'like', "%{$v}%")
                  ->orWhere('payer_name', 'like', "%{$v}%");
            });
        }
        if ($v = $request->input('from_date')) $query->where('created_at', '>=', Carbon::parse($v)->startOfDay());
        if ($v = $request->input('to_date'))   $query->where('created_at', '<=', Carbon::parse($v)->endOfDay());

        $payments  = $query->latest()->paginate(50)->withQueryString();
        $facilities = Facility::orderBy('name')->get(['id', 'name']);

        $summaryTotal   = (clone $query->getQuery())->whereIn('status', ['successful', 'completed'])->sum('amount');
        $summaryCount   = $payments->total();

        return view('portals.admin.financial.payments', compact(
            'payments', 'facilities', 'summaryTotal', 'summaryCount'
        ));
    }

    // ── Single Payment Detail ─────────────────────────────────────

    public function paymentDetail(string $id): View|RedirectResponse
    {
        if (!Auth::check()) return redirect()->route('login');

        $payment = Payment::with([
            'patient:id,first_name,last_name,health_id,phone_number,sex,date_of_birth',
            'facility:id,name,type,license_number',
            'cashier:id,name,email',
            'invoice.items',
            'invoice.patient:id,first_name,last_name,health_id',
            'receipts',
            'reversals.actor:id,name',
        ])->findOrFail($id);

        return view('portals.admin.financial.payment_detail', compact('payment'));
    }

    // ── Invoices ─────────────────────────────────────────────────

    public function invoices(Request $request): View|RedirectResponse
    {
        if (!Auth::check()) return redirect()->route('login');

        $query = Invoice::with(['patient:id,first_name,last_name,health_id', 'facility:id,name', 'items']);

        if ($v = $request->input('status'))      $query->where('status', $v);
        if ($v = $request->input('facility_id')) $query->where('facility_id', $v);
        if ($v = $request->input('from_date'))   $query->where('issued_at', '>=', Carbon::parse($v)->startOfDay());
        if ($v = $request->input('to_date'))     $query->where('issued_at', '<=', Carbon::parse($v)->endOfDay());
        if ($v = $request->input('search')) {
            $query->where(function ($q) use ($v) {
                $q->where('invoice_number', 'like', "%{$v}%");
            });
        }

        $invoices   = $query->latest('issued_at')->paginate(25)->withQueryString();
        $facilities = Facility::orderBy('name')->get(['id', 'name']);

        return view('portals.admin.financial.invoices', compact('invoices', 'facilities'));
    }

    // ── Reports by service type ────────────────────────────────────

    public function reportByService(Request $request): View|RedirectResponse
    {
        if (!Auth::check()) return redirect()->route('login');

        $from = $request->input('from_date') ? Carbon::parse($request->input('from_date'))->startOfDay() : Carbon::now()->startOfMonth();
        $to   = $request->input('to_date')   ? Carbon::parse($request->input('to_date'))->endOfDay()   : Carbon::now()->endOfDay();

        $report = Payment::whereBetween('created_at', [$from, $to])
            ->whereIn('status', ['successful', 'completed'])
            ->selectRaw("COALESCE(service_type,'unspecified') as service_type, COALESCE(gateway,method,'unknown') as gateway, COUNT(*) as txn_count, SUM(amount) as total_collected, AVG(amount) as avg_amount, MIN(amount) as min_amount, MAX(amount) as max_amount")
            ->groupByRaw("COALESCE(service_type,'unspecified'), COALESCE(gateway,method,'unknown')")
            ->orderByDesc('total_collected')
            ->get();

        return view('portals.admin.financial.report_by_service', compact('report', 'from', 'to'));
    }

    // ── Actions ────────────────────────────────────────────────────

    public function voidInvoice(string $id): RedirectResponse
    {
        if (!Auth::check()) return redirect()->route('login');

        $invoice = Invoice::findOrFail($id);
        if (!in_array($invoice->status, ['draft', 'unpaid'])) {
            return redirect()->back()->with('error', 'Only draft or unpaid invoices can be voided.');
        }
        $invoice->update(['status' => 'cancelled']);
        return redirect()->back()->with('success', "Invoice #{$invoice->invoice_number} voided.");
    }

    public function markPaid(Request $request, string $id): RedirectResponse
    {
        if (!Auth::check()) return redirect()->route('login');

        $data = $request->validate([
            'amount'      => 'required|numeric|min:0.01',
            'method'      => 'required|string',
            'payer_phone' => 'nullable|string|max:30',
            'payer_name'  => 'nullable|string|max:160',
            'notes'       => 'nullable|string|max:500',
        ]);

        $invoice = Invoice::findOrFail($id);

        Payment::create([
            'invoice_id'        => $invoice->id,
            'patient_id'        => $invoice->patient_id,
            'facility_id'       => $invoice->facility_id,
            'cashier_id'        => $this->actorId(),
            'payment_reference' => 'MAN-' . strtoupper(uniqid()),
            'method'            => $data['method'],
            'gateway'           => $data['method'],
            'service_type'      => 'manual_override',
            'status'            => 'successful',
            'amount'            => $data['amount'],
            'currency'          => 'XAF',
            'refunded_amount'   => 0,
            'payer_phone'       => $data['payer_phone'] ?? null,
            'payer_name'        => $data['payer_name'] ?? null,
            'device_type'       => 'web',
            'ip_address'        => request()->ip(),
            'confirmed_at'      => now(),
            'gateway_metadata'  => ['manual' => true, 'notes' => $data['notes'] ?? null, 'cashier' => $this->actorId()],
        ]);

        $invoice->update(['status' => 'paid', 'paid_at' => now()]);
        return redirect()->back()->with('success', "Invoice #{$invoice->invoice_number} marked as paid.");
    }
}
