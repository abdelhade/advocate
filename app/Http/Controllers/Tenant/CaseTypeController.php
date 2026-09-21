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

        $sortable = ['name', 'created_at'];
        $sort = in_array($request->input('sort'), $sortable, true)
            ? $request->input('sort')
            : 'name';
        $direction = $request->input('direction') === 'desc' ? 'desc' : 'asc';

        $query = CaseType::where('tenant_id', $tenantId);

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $caseTypes = $query->orderBy($sort, $direction)->get();

        return Inertia::render('Tenant/CaseTypes/Index', [
            'caseTypes' => $caseTypes,
            'stats' => ['total' => CaseType::where('tenant_id', $tenantId)->count()],
            'filters' => $request->only(['search', 'sort', 'direction']),
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

