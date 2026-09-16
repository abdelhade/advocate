<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\CaseType;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CaseTypeController extends Controller
{
    private function getTenantId(): string
    {
        $tenantId = app(TenantContext::class)->id() ?? auth()->user()?->currentTenant()?->id;
        if (!$tenantId) {
            abort(403, 'تعذر تحديد هوية المكتب.');
        }
        return $tenantId;
    }

    public function index(Request $request): Response
    {
        $tenantId = $this->getTenantId();

        $caseTypes = CaseType::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();

        return Inertia::render('Tenant/CaseTypes/Index', [
            'caseTypes' => $caseTypes,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = $this->getTenantId();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('case_types', 'name')->where('tenant_id', $tenantId),
            ],
            'color' => 'nullable|string|max:20',
        ], [
            'name.required' => 'اسم نوع القضية مطلوب.',
            'name.unique' => 'نوع القضية هذا مسجل بالفعل في مكتبك.',
        ]);

        CaseType::create([
            'tenant_id' => $tenantId,
            'name' => trim($validated['name']),
            'color' => $validated['color'] ?? '#3b82f6',
        ]);

        return redirect()->back()->with('success', 'تم إضافة نوع القضية بنجاح');
    }

    public function update(Request $request, CaseType $caseType): RedirectResponse
    {
        $tenantId = $this->getTenantId();
        abort_unless($caseType->tenant_id === $tenantId, 403);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('case_types', 'name')->where('tenant_id', $tenantId)->ignore($caseType->id),
            ],
            'color' => 'nullable|string|max:20',
        ], [
            'name.required' => 'اسم نوع القضية مطلوب.',
            'name.unique' => 'نوع القضية هذا مسجل بالفعل في مكتبك.',
        ]);

        $caseType->update([
            'name' => trim($validated['name']),
            'color' => $validated['color'] ?? '#3b82f6',
        ]);

        return redirect()->back()->with('success', 'تم تحديث نوع القضية بنجاح');
    }

    public function destroy(CaseType $caseType): RedirectResponse
    {
        $tenantId = $this->getTenantId();
        abort_unless($caseType->tenant_id === $tenantId, 403);

        $caseType->delete();

        return redirect()->back()->with('success', 'تم حذف نوع القضية بنجاح');
    }
}

