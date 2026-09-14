<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\LegalCase;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(Request $request): Response
    {
        $tenantId = auth()->user()->currentTenant()->id;

        $query = Task::where('tenant_id', $tenantId)
            ->with(['case:id,title,case_number', 'assignee:id,name', 'creator:id,name']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('case_id')) {
            $query->where('case_id', $request->case_id);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $tasks = $query->latest()->paginate(15)->withQueryString();

        $cases = LegalCase::where('tenant_id', $tenantId)->select('id', 'title', 'case_number')->get();
        $users = auth()->user()->currentTenant()->users()->select('users.id', 'users.name')->get();

        return Inertia::render('Tenant/Tasks/Index', [
            'tasks' => $tasks,
            'cases' => $cases,
            'users' => $users,
            'filters' => $request->only(['status', 'priority', 'case_id', 'search']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = auth()->user()->currentTenant()->id;

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'case_id' => 'nullable|exists:legal_cases,id',
            'assignee_id' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
        ]);

        Task::create([
            ...$validated,
            'tenant_id' => $tenantId,
            'creator_id' => auth()->id(),
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'تم إضافة المهمة بنجاح');
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $tenantId = auth()->user()->currentTenant()->id;
        abort_unless($task->tenant_id === $tenantId, 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'case_id' => 'nullable|exists:legal_cases,id',
            'assignee_id' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'due_date' => 'nullable|date',
        ]);

        if ($validated['status'] === 'completed' && $task->status !== 'completed') {
            $validated['completed_at'] = now();
        } elseif ($validated['status'] !== 'completed') {
            $validated['completed_at'] = null;
        }

        $task->update($validated);

        return redirect()->back()->with('success', 'تم تحديث البيانات بنجاح');
    }

    public function updateStatus(Request $request, Task $task): RedirectResponse
    {
        $tenantId = auth()->user()->currentTenant()->id;
        abort_unless($task->tenant_id === $tenantId, 403);

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled',
        ]);

        $task->update([
            'status' => $validated['status'],
            'completed_at' => $validated['status'] === 'completed' ? now() : null,
        ]);

        return redirect()->back()->with('success', 'تم تغيير حالة المهمة بنجاح');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $tenantId = auth()->user()->currentTenant()->id;
        abort_unless($task->tenant_id === $tenantId, 403);

        $task->delete();

        return redirect()->back()->with('success', 'تم حذف المهمة بنجاح');
    }
}
