<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\CourtSession;
use App\Models\LegalCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SessionController extends Controller
{
    public function store(Request $request, LegalCase $case)
    {
        Gate::authorize('update', $case);

        $validated = $request->validate([
            'session_date' => ['required', 'date'],
            'status' => ['required', 'in:scheduled,completed,postponed,cancelled'],
            'requirements' => ['nullable', 'string'],
            'results' => ['nullable', 'string'],
            'next_session_date' => ['nullable', 'date'],
        ]);

        $case->sessions()->create($validated);

        return back()->with('success', 'تم إضافة الجلسة بنجاح.');
    }

    public function destroy(LegalCase $case, CourtSession $session)
    {
        Gate::authorize('update', $case);

        $session->delete();

        return back()->with('success', 'تم نقل الجلسة إلى سلة المهملات بنجاح.');
    }
}
