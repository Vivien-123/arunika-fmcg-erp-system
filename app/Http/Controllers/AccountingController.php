<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\ChartOfAccount;
use App\Models\AuditLog;
use App\Services\AccountingService;

/**
 * AccountingController — M4 HTTP Layer
 * Screens: S-10 Customer Invoice, S-11 Vendor Bill,
 *          S-12 Register Payment, S-13 General Ledger, S-16 Return & Credit Note
 */
class AccountingController extends Controller
{
    public function __construct(protected AccountingService $svc) {}

    // ── S-10: Customer Invoice ─────────────────────────────────────────────
    public function invoiceIndex(Request $request)
    {
        $query = Invoice::where('invoice_type', 'customer_invoice')->with('items')->orderByDesc('id');
        if ($request->filled('status'))  $query->where('status', $request->status);
        if ($request->filled('partner')) $query->where('partner_id', $request->partner);
        if ($request->filled('search'))  $query->where('document_number', 'like', '%'.$request->search.'%');
        $invoices = $query->paginate(20)->withQueryString();
        return view('accounting.invoices.index', compact('invoices'));
    }

    public function invoiceShow(int $id)
    {
        $invoice   = Invoice::with(['items', 'journalEntries', 'creditNotes'])->findOrFail($id);
        $auditLogs = AuditLog::forDocument($invoice->invoice_type, $invoice->document_number);
        $related   = AuditLog::relatedDocuments($invoice->invoice_type, $invoice->document_number);
        return view('accounting.invoices.show', compact('invoice', 'auditLogs', 'related'));
    }

    public function invoiceCreate() { return view('accounting.invoices.create'); }

    public function invoiceStore(Request $request)
    {
        $request->validate([
            'partner_id' => 'required|string', 'partner_name' => 'required|string',
            'source_so' => 'nullable|string', 'payment_terms' => 'nullable|in:CASH,NET14,NET30',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|string', 'lines.*.variant_sku' => 'required|string',
            'lines.*.product_name' => 'required|string', 'lines.*.unit' => 'required|string',
            'lines.*.unit_price' => 'required|integer|min:1',
            'lines.*.delivered_qty' => 'required|integer|min:0',
            'lines.*.invoiced_qty' => 'required|integer|min:0',
        ]);
        try {
            $invoice = $this->svc->createCustomerInvoice(array_merge($request->all(), ['created_by' => auth()->user()->username]));
            return redirect()->route('accounting.invoices.show', $invoice->id)->with('success', "Customer Invoice {$invoice->document_number} berhasil dibuat.");
        } catch (\RuntimeException $e) {
            return back()->withErrors(['lines' => $e->getMessage()])->withInput();
        }
    }

    public function invoicePost(int $id)
    {
        $invoice = Invoice::findOrFail($id);
        try {
            $this->svc->postInvoice($invoice, auth()->user()->username);
            return redirect()->route('accounting.invoices.show', $id)->with('success', "Invoice {$invoice->document_number} berhasil diposting.");
        } catch (\RuntimeException $e) {
            return back()->withErrors(['post' => $e->getMessage()]);
        }
    }

    // ── S-11: Vendor Bill ──────────────────────────────────────────────────
    public function billIndex(Request $request)
    {
        $query = Invoice::where('invoice_type', 'vendor_bill')->with('items')->orderByDesc('id');
        if ($request->filled('status'))  $query->where('status', $request->status);
        if ($request->filled('partner')) $query->where('partner_id', $request->partner);
        $invoices = $query->paginate(20)->withQueryString();
        return view('accounting.bills.index', compact('invoices'));
    }

    public function billShow(int $id)
    {
        $invoice   = Invoice::with(['items', 'journalEntries'])->findOrFail($id);
        $auditLogs = AuditLog::forDocument($invoice->invoice_type, $invoice->document_number);
        return view('accounting.bills.show', compact('invoice', 'auditLogs'));
    }

    public function billStore(Request $request)
    {
        $request->validate([
            'partner_id' => 'required|string', 'partner_name' => 'required|string',
            'source_po' => 'nullable|string', 'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|string', 'lines.*.variant_sku' => 'required|string',
            'lines.*.product_name' => 'required|string', 'lines.*.unit' => 'required|string',
            'lines.*.unit_price' => 'required|integer|min:1',
            'lines.*.accepted_qty' => 'required|integer|min:0',
            'lines.*.already_billed' => 'required|integer|min:0',
        ]);
        try {
            $invoice = $this->svc->createVendorBill(array_merge($request->all(), ['created_by' => auth()->user()->username]));
            return redirect()->route('accounting.bills.show', $invoice->id)->with('success', "Vendor Bill {$invoice->document_number} berhasil dibuat.");
        } catch (\RuntimeException $e) {
            return back()->withErrors(['lines' => $e->getMessage()])->withInput();
        }
    }

    public function billPost(int $id)
    {
        $invoice = Invoice::findOrFail($id);
        try {
            $this->svc->postInvoice($invoice, auth()->user()->username);
            return redirect()->route('accounting.bills.show', $id)->with('success', "Vendor Bill {$invoice->document_number} berhasil diposting.");
        } catch (\RuntimeException $e) {
            return back()->withErrors(['post' => $e->getMessage()]);
        }
    }

    // ── S-12: Register Payment ─────────────────────────────────────────────
    public function paymentCreate(int $invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        return view('accounting.payments.create', compact('invoice'));
    }

    public function paymentStore(Request $request, int $invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        $request->validate(['amount' => 'required|integer|min:1', 'payment_ref' => 'required|string']);
        try {
            $this->svc->registerPayment($invoice, (int)$request->amount, $request->payment_ref, auth()->user()->username);
            $routeName = $invoice->invoice_type === 'vendor_bill' ? 'accounting.bills.show' : 'accounting.invoices.show';
            return redirect()->route($routeName, $invoiceId)->with('success', "Pembayaran Rp{$request->amount} berhasil dicatat.");
        } catch (\RuntimeException $e) {
            return back()->withErrors(['amount' => $e->getMessage()]);
        }
    }

    // ── S-13: General Ledger ───────────────────────────────────────────────
    public function ledgerIndex(Request $request)
    {
        $query = JournalEntry::with(['invoice'])->orderByDesc('posted_at');
        if ($request->filled('journal_type')) $query->where('journal_type', $request->journal_type);
        if ($request->filled('account'))      $query->where(fn($q) => $q->where('debit_account', $request->account)->orWhere('credit_account', $request->account));
        if ($request->filled('reference'))    $query->where('reference', 'like', '%'.$request->reference.'%');
        if ($request->filled('date_from'))    $query->where('posted_at', '>=', $request->date_from.' 00:00:00');
        if ($request->filled('date_to'))      $query->where('posted_at', '<=', $request->date_to.' 23:59:59');
        $entries  = $query->paginate(50)->withQueryString();
        $accounts = ChartOfAccount::orderBy('code')->get();
        $types    = JournalEntry::select('journal_type')->distinct()->pluck('journal_type');
        return view('accounting.ledger.index', compact('entries', 'accounts', 'types'));
    }

    // ── S-16: Credit Note ─────────────────────────────────────────────────
    public function creditNoteCreate(int $invoiceId)
    {
        $invoice = Invoice::with('items')->findOrFail($invoiceId);
        $maxQty  = $invoice->maxCreditableQty();
        return view('accounting.credit-notes.create', compact('invoice', 'maxQty'));
    }

    public function creditNoteStore(Request $request, int $invoiceId)
    {
        $invoice = Invoice::with('items')->findOrFail($invoiceId);
        $request->validate([
            'return_lines' => 'required|array|min:1',
            'return_lines.*.variant_sku' => 'required|string',
            'return_lines.*.qty' => 'required|integer|min:1',
        ]);
        try {
            $cn = $this->svc->createCreditNote($invoice, $request->return_lines, auth()->user()->username);
            $this->svc->postInvoice($cn, auth()->user()->username);
            return redirect()->route('accounting.invoices.show', $invoiceId)->with('success', "Credit Note {$cn->document_number} berhasil dibuat dan diposting.");
        } catch (\RuntimeException $e) {
            return back()->withErrors(['return_lines' => $e->getMessage()])->withInput();
        }
    }

    // ── COA Overview ───────────────────────────────────────────────────────
    public function coaIndex()
    {
        $accounts = ChartOfAccount::where('is_active', true)->orderBy('code')->get();
        return view('accounting.coa.index', compact('accounts'));
    }
}
