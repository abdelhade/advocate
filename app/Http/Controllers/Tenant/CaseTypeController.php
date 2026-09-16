<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\CaseType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CaseTypeController extends Controller
{
    public function index(Request $request): Response
    {
        $tenantId = auth()->user()->currentTenant()->id;

        $caseTypes = CaseType::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();

        return Inertia::render('Tenant/CaseTypes/Index', [
            'caseTypes' => $caseTypes,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = auth()->user()->currentTenant()->id;

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'nullable|string|max:20',
        ], [
            'name.required' => 'اسم نوع القضية مطلوب.',
        ]);

        CaseType::create([
            ...$validated,
            'tenant_id' => $tenantId,
        ]);

        return redirect()->back()->with('success', 'تم إضافة نوع القضية بنجاح');
    }

    public function update(Request $request, CaseType $caseType): RedirectResponse
    {
        $tenantId = auth()->user()->currentTenant()->id;
        abort_unless($caseType->tenant_id === $tenantId, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'nullable|string|max:20',
        ], [
            'name.required' => 'اسم نوع القضية مطلوب.',
        ]);

        $caseType->update($validated);

        return redirect()->back()->with('success', 'تم تحديث نوع القضية بنجاح');
    }

    public function destroy(CaseType $caseType): RedirectResponse
    {
        $tenantId = auth()->user()->currentTenant()->id;
        abort_unless($caseType->tenant_id === $tenantId, 403);

        $caseType->delete();

        return redirect()->back()->with('success', 'تم حذف نوع القضية بنجاح');
    }
}
