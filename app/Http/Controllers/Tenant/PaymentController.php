<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function index(Request $request): Response
    {
        $tenantId = auth()->user()->currentTenant()->id;

        $sortable = ['payment_number', 'amount', 'payment_date', 'payment_method', 'created_at'];
        $sort = in_array($request->input('sort'), $sortable, true)
            ? $request->input('sort')
            : 'created_at';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';

        $query = Payment::where('tenant_id', $tenantId)
            ->with(['client:id,name', 'invoice:id,invoice_number,total_amount,paid_amount']);

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('payment_number', 'like', '%' . $request->search . '%')
                  ->orWhere('reference_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('client', function ($cq) use ($request) {
                      $cq->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $payments = $query->orderBy($sort, $direction)->paginate(15)->withQueryString();

        $stats = [
            'total_collected' => Payment::where('tenant_id', $tenantId)->sum('amount'),
            'payments_count' => Payment::where('tenant_id', $tenantId)->count(),
        ];

        $clients = Client::where('tenant_id', $tenantId)->select('id', 'name')->orderBy('name')->get();
        $invoices = Invoice::where('tenant_id', $tenantId)
            ->whereIn('status', ['posted', 'partially_paid'])
            ->select('id', 'invoice_number', 'total_amount', 'paid_amount', 'client_id')
            ->get();

        $nextNum = Payment::where('tenant_id', $tenantId)->count() + 1;
        $defaultPaymentNumber = 'REC-' . date('Y') . '-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        return Inertia::render('Tenant/Payments/Index', [
            'payments' => $payments,
            'stats' => $stats,
            'clients' => $clients,
            'invoices' => $invoices,
            'defaultPaymentNumber' => $defaultPaymentNumber,
            'filters' => $request->only(['payment_method', 'client_id', 'search', 'sort', 'direction']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = auth()->user()->currentTenant()->id;

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'payment_number' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer,check,card',
            'reference_number' => 'nullable|string|max:100',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($tenantId, $validated) {
            $payment = Payment::create([
                'tenant_id' => $tenantId,
                'client_id' => $validated['client_id'],
                'invoice_id' => $validated['invoice_id'] ?? null,
                'payment_number' => $validated['payment_number'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'] ?? null,
                'payment_date' => $validated['payment_date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            if (!empty($validated['invoice_id'])) {
                $invoice = Invoice::find($validated['invoice_id']);
                if ($invoice && $invoice->tenant_id === $tenantId) {
                    $newPaidAmount = $invoice->paid_amount + $validated['amount'];
                    $newStatus = $newPaidAmount >= $invoice->total_amount ? 'paid' : 'partially_paid';
                    $invoice->update([
                        'paid_amount' => $newPaidAmount,
                        'status' => $newStatus,
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', 'تم تسجيل سند القبض بنجاح');
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $tenantId = auth()->user()->currentTenant()->id;
        abort_unless($payment->tenant_id === $tenantId, 403);

        DB::transaction(function () use ($payment) {
            if ($payment->invoice_id) {
                $invoice = $payment->invoice;
                if ($invoice) {
                    $newPaidAmount = max(0, $invoice->paid_amount - $payment->amount);
                    $newStatus = $newPaidAmount <= 0 ? 'posted' : ($newPaidAmount >= $invoice->total_amount ? 'paid' : 'partially_paid');
                    $invoice->update([
                        'paid_amount' => $newPaidAmount,
                        'status' => $newStatus,
                    ]);
                }
            }
            $payment->delete();
        });

        return redirect()->back()->with('success', 'تم إلغاء/حذف سند القبض بنجاح');
    }
}
