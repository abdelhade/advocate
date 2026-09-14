<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\LegalCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseController extends Controller
{
    public function index(Request $request): Response
    {
        $tenantId = auth()->user()->currentTenant()->id;

        $query = Expense::where('tenant_id', $tenantId)
            ->with(['case:id,title,case_number', 'paidBy:id,name']);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('case_id')) {
            $query->where('case_id', $request->case_id);
        }

        if ($request->filled('search')) {
            $query->where('category', 'like', '%' . $request->search . '%');
        }

        $expenses = $query->latest()->paginate(15)->withQueryString();

        $totalExpenses = Expense::where('tenant_id', $tenantId)->sum('amount');
        $cases = LegalCase::where('tenant_id', $tenantId)->select('id', 'title', 'case_number')->get();

        return Inertia::render('Tenant/Expenses/Index', [
            'expenses' => $expenses,
            'totalExpenses' => $totalExpenses,
            'cases' => $cases,
            'filters' => $request->only(['category', 'case_id', 'search']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = auth()->user()->currentTenant()->id;

        $validated = $request->validate([
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'case_id' => 'nullable|exists:legal_cases,id',
        ]);

        Expense::create([
            'tenant_id' => $tenantId,
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'case_id' => $validated['case_id'] ?? null,
            'paid_by_id' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'تم إضافة المصروف بنجاح');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $tenantId = auth()->user()->currentTenant()->id;
        abort_unless($expense->tenant_id === $tenantId, 403);

        $expense->delete();

        return redirect()->back()->with('success', 'تم حذف المصروف بنجاح');
    }
}
