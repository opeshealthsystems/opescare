<?php

namespace App\Http\Controllers\MedicalId;

use App\Models\BillingAccount;
use App\Models\Facility;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminFinancialController extends Controller
{
    private function actorId(): string
    {
        return Auth::id() ?? session('auth_email', 'system');
    }

    public function index(Request $request): View|RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $fromDate = $request->input('from_date')
            ? Carbon::parse($request->input('from_date'))->startOfDay()
            : Carbon::now()->startOfMonth();

        $toDate = $request->input('to_date')
            ? Carbon::parse($request->input('to_date'))->endOfDay()
            : Carbon::now()->endOfMonth();

        $invoiceQuery = Invoice::whereBetween('issued_at', [$fromDate, $toDate]);

        $totalInvoiced = (clone $invoiceQuery)->sum('subtotal_amount');

        $totalCollected = Payment::whereBetween('created_at', [$fromDate, $toDate])
            ->where('status', 'completed')
            ->sum('amount');

        $totalOutstanding = BillingAccount::sum('outstanding_balance_amount');

        $overdueCount = Invoice::where('status', 'unpaid')
            ->where('issued_at', '<', Carbon::now()->subDays(30))
            ->count();

        $recentInvoices = Invoice::with(['patient', 'facility'])
            ->latest()
            ->limit(20)
            ->get();

        $revenueByFacility = (clone $invoiceQuery)
            ->selectRaw('facility_id, SUM(subtotal_amount) as total_amount, COUNT(*) as invoice_count')
            ->groupBy('facility_id')
            ->with('facility')
            ->get();

        $facilities = Facility::orderBy('name')->get();

        return view('portals.admin.financial.index', compact(
            'fromDate',
            'toDate',
            'totalInvoiced',
            'totalCollected',
            'totalOutstanding',
            'overdueCount',
            'recentInvoices',
            'revenueByFacility',
            'facilities'
        ));
    }

    public function invoices(Request $request): View|RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $query = Invoice::with(['patient', 'facility']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('facility_id')) {
            $query->where('facility_id', $request->input('facility_id'));
        }

        if ($request->filled('from_date')) {
            $query->where('issued_at', '>=', Carbon::parse($request->input('from_date'))->startOfDay());
        }

        if ($request->filled('to_date')) {
            $query->where('issued_at', '<=', Carbon::parse($request->input('to_date'))->endOfDay());
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $invoices = $query->latest()->paginate(25)->withQueryString();

        $facilities = Facility::orderBy('name')->get();

        $statuses = ['draft', 'unpaid', 'paid', 'partial', 'cancelled', 'overdue'];

        return view('portals.admin.financial.invoices', compact(
            'invoices',
            'facilities',
            'statuses'
        ));
    }

    public function payments(Request $request): View|RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $query = Payment::with(['invoice', 'invoice.patient', 'invoice.facility']);

        if ($request->filled('method')) {
            $query->where('method', $request->input('method'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('facility_id')) {
            $query->where('facility_id', $request->input('facility_id'));
        }

        if ($request->filled('from_date')) {
            $query->where('created_at', '>=', Carbon::parse($request->input('from_date'))->startOfDay());
        }

        if ($request->filled('to_date')) {
            $query->where('created_at', '<=', Carbon::parse($request->input('to_date'))->endOfDay());
        }

        $payments = $query->latest()->paginate(25)->withQueryString();

        $facilities = Facility::orderBy('name')->get();

        $methods = ['cash', 'wallet', 'insurance', 'mobile_money', 'bank_transfer'];
        $statuses = ['pending', 'completed', 'failed', 'refunded'];

        return view('portals.admin.financial.payments', compact(
            'payments',
            'facilities',
            'methods',
            'statuses'
        ));
    }

    public function voidInvoice(string $id): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $invoice = Invoice::findOrFail($id);

        if (!in_array($invoice->status, ['draft', 'unpaid'])) {
            return redirect()->back()->with('error', 'Only draft or unpaid invoices can be voided.');
        }

        $invoice->update(['status' => 'cancelled']);

        return redirect()->back()->with('success', 'Invoice #' . $invoice->invoice_number . ' has been voided.');
    }

    public function markPaid(Request $request, string $id): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $request->validate([
            'amount' => 'required|numeric|min:0',
            'method' => 'required|in:cash,wallet,insurance,mobile_money,bank_transfer',
        ]);

        $invoice = Invoice::findOrFail($id);

        Payment::create([
            'invoice_id'         => $invoice->id,
            'patient_id'         => $invoice->patient_id,
            'facility_id'        => $invoice->facility_id,
            'cashier_id'         => $this->actorId(),
            'payment_reference'  => 'MAN-' . strtoupper(uniqid()),
            'method'             => $request->input('method'),
            'status'             => 'completed',
            'amount'             => $request->input('amount'),
            'refunded_amount'    => 0,
        ]);

        $invoice->update([
            'status'  => 'paid',
            'paid_at' => Carbon::now(),
        ]);

        return redirect()->back()->with('success', 'Invoice #' . $invoice->invoice_number . ' marked as paid.');
    }
}
