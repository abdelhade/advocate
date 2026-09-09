<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\CourtSession;
use App\Models\LegalCase;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function store(Request $request, LegalCase $case)
    {
        $validated = $request->validate([
            'session_date' => ['required', 'date'],
            'session_time' => ['nullable', 'date_format:H:i'],
            'location' => ['nullable', 'string', 'max:255'],
            'decision' => ['nullable', 'string'],
            'next_session_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $case->courtSessions()->create($validated);

        return back()->with('success', 'تم إضافة الجلسة بنجاح.');
    }

    public function destroy(LegalCase $case, CourtSession $session)
    {
        $session->delete();

        return back()->with('success', 'تم حذف الجلسة بنجاح.');
    }
}
