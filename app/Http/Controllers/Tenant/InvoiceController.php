<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LegalCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function index(Request $request): Response
    {
        $tenantId = auth()->user()->currentTenant()->id;

        $query = Invoice::where('tenant_id', $tenantId)
            ->with(['client:id,name', 'case:id,title,case_number']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('invoice_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('client', function ($cq) use ($request) {
                      $cq->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $invoices = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total_invoiced' => Invoice::where('tenant_id', $tenantId)->sum('total_amount'),
            'total_paid' => Invoice::where('tenant_id', $tenantId)->sum('paid_amount'),
            'total_unpaid' => Invoice::where('tenant_id', $tenantId)->whereIn('status', ['draft', 'posted', 'partially_paid'])->sum(DB::raw('total_amount - paid_amount')),
            'invoices_count' => Invoice::where('tenant_id', $tenantId)->count(),
        ];

        return Inertia::render('Tenant/Invoices/Index', [
            'invoices' => $invoices,
            'stats' => $stats,
            'filters' => $request->only(['status', 'client_id', 'search']),
        ]);
    }

    public function create(): Response
    {
        $tenantId = auth()->user()->currentTenant()->id;

        $clients = Client::where('tenant_id', $tenantId)->select('id', 'name')->get();
        $cases = LegalCase::where('tenant_id', $tenantId)->select('id', 'title', 'case_number', 'client_id')->get();

        // Auto-generate invoice number
        $nextNum = Invoice::where('tenant_id', $tenantId)->count() + 1;
        $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        return Inertia::render('Tenant/Invoices/Create', [
            'clients' => $clients,
            'cases' => $cases,
            'defaultInvoiceNumber' => $invoiceNumber,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = auth()->user()->currentTenant()->id;

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'case_id' => 'nullable|exists:legal_cases,id',
            'invoice_number' => 'required|string|max:50',
            'due_date' => 'nullable|date',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($tenantId, $validated) {
            $subtotal = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $totalPrice = $item['quantity'] * $item['unit_price'];
                $subtotal += $totalPrice;
                $itemsData[] = [
                    'tenant_id' => $tenantId,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $totalPrice,
                ];
            }

            $discount = $validated['discount_amount'] ?? 0;
            $tax = $validated['tax_amount'] ?? 0;
            $totalAmount = max(0, $subtotal - $discount + $tax);

            $invoice = Invoice::create([
                'tenant_id' => $tenantId,
                'client_id' => $validated['client_id'],
                'case_id' => $validated['case_id'] ?? null,
                'invoice_number' => $validated['invoice_number'],
                'status' => 'posted',
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'total_amount' => $totalAmount,
                'paid_amount' => 0.00,
                'due_date' => $validated['due_date'] ?? null,
            ]);

            foreach ($itemsData as $item) {
                $invoice->items()->create($item);
            }
        });

        return redirect()->route('invoices.index')->with('success', 'تم إنشاء الفاتورة بنجاح');
    }

    public function show(Invoice $invoice): Response
    {
        $tenantId = auth()->user()->currentTenant()->id;
        abort_unless($invoice->tenant_id === $tenantId, 403);

        $invoice->load(['client', 'case', 'items', 'payments']);

        return Inertia::render('Tenant/Invoices/Show', [
            'invoice' => $invoice,
            'tenant' => auth()->user()->currentTenant(),
        ]);
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $tenantId = auth()->user()->currentTenant()->id;
        abort_unless($invoice->tenant_id === $tenantId, 403);

        if ($invoice->payments()->exists()) {
            return redirect()->back()->with('error', 'لا يمكن حذف فاتورة تم تسجيل مدفوعات عليها');
        }

        $invoice->items()->delete();
        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'تم حذف الفاتورة بنجاح');
    }
}
