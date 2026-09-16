<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Tenant\AuditLogController;
use App\Http\Controllers\Tenant\CaseController;
use App\Http\Controllers\Tenant\ClientController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\ExpenseController;
use App\Http\Controllers\Tenant\InvoiceController;
use App\Http\Controllers\Tenant\PaymentController;
use App\Http\Controllers\Tenant\SessionController;
use App\Http\Controllers\Tenant\TaskController;
use App\Http\Controllers\Tenant\CaseTypeController;
use App\Http\Controllers\Tenant\TenantBillingController;
use App\Http\Controllers\Tenant\TenantUserController;
use App\Http\Controllers\TenantSwitchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tenant subdomain routes ({slug}.jalsateg.com / {slug}.localhost)
| Shared single database — tenant resolved from host via IdentifyTenant.
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('tenant.home');

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::post('/switch-tenant/{tenant}', TenantSwitchController::class)->name('tenant.switch');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('tenant-users', TenantUserController::class)->names([
        'index' => 'tenant.users.index',
        'store' => 'tenant.users.store',
        'update' => 'tenant.users.update',
        'destroy' => 'tenant.users.destroy',
    ])->only(['index', 'store', 'update', 'destroy']);

    Route::resource('clients', ClientController::class);

    Route::resource('cases', CaseController::class);
    Route::resource('case-types', CaseTypeController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('cases/{case}/sessions', [SessionController::class, 'store'])->name('cases.sessions.store');
    Route::delete('cases/{case}/sessions/{session}', [SessionController::class, 'destroy'])->name('cases.sessions.destroy');

    Route::resource('tasks', TaskController::class)->except(['create', 'edit', 'show']);
    Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');

    Route::resource('invoices', InvoiceController::class)->except(['edit', 'update']);
    Route::resource('payments', PaymentController::class)->only(['index', 'store', 'destroy']);
    Route::resource('expenses', ExpenseController::class)->only(['index', 'store', 'destroy']);

    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    Route::get('billing', [TenantBillingController::class, 'index'])->name('tenant.billing.index');
    Route::get('billing/invoices/{invoice}', [TenantBillingController::class, 'showInvoice'])->name('tenant.billing.show');

    Route::post('documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/all-cases-preview', function () {
    $cases = \App\Models\LegalCase::withoutGlobalScopes()
        ->select(['id', 'tenant_id', 'client_id', 'case_number', 'title', 'court_name', 'status', 'created_at'])
        ->with('client:id,name')
        ->simplePaginate(100000);

    if (request()->wantsJson() || request()->has('json')) {
        $data = collect($cases->items())->map(function ($c) {
            return [
                'id' => $c->id,
                'case_number' => $c->case_number,
                'title' => $c->title,
                'client_name' => $c->client?->name ?? '—',
                'court_name' => $c->court_name ?? '—',
                'status' => $c->status,
                'created_at' => $c->created_at ? $c->created_at->format('Y-m-d') : '—',
            ];
        });

        return response()->json([
            'data' => $data,
            'next_page_url' => $cases->nextPageUrl() ? $cases->nextPageUrl() . '&json=1' : null,
            'has_more' => $cases->hasMorePages(),
        ]);
    }

    $totalCount = \Illuminate\Support\Facades\Cache::remember('cases_total_count', 3600, function () {
        try {
            $dbName = config('database.connections.mysql.database');
            $row = \Illuminate\Support\Facades\DB::selectOne(
                "SELECT table_rows FROM information_schema.tables WHERE table_schema = ? AND table_name = 'legal_cases'",
                [$dbName]
            );
            if ($row && isset($row->table_rows) && (int) $row->table_rows > 0) {
                return (int) $row->table_rows;
            }
        } catch (\Throwable $e) {}
        return 33857000;
    });

    $initialData = collect($cases->items())->map(function ($c) {
        return [
            'id' => $c->id,
            'case_number' => $c->case_number,
            'title' => $c->title,
            'client_name' => $c->client?->name ?? '—',
            'court_name' => $c->court_name ?? '—',
            'status' => $c->status,
            'created_at' => $c->created_at ? $c->created_at->format('Y-m-d') : '—',
        ];
    });

    $nextPageUrl = $cases->nextPageUrl() ? $cases->nextPageUrl() . '&json=1' : null;
    $initialJson = json_encode($initialData, JSON_UNESCAPED_UNICODE);

    $html = '<!DOCTYPE html><html dir="rtl" lang="ar"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>عرض القضايا (Lazy Loading 100,000)</title>';
    $html .= '<style>
        body { font-family: system-ui, -apple-system, sans-serif; background-color: #f8fafc; margin: 0; padding: 24px; color: #1e293b; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 24px; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
        h2 { margin: 0; color: #0f172a; font-size: 20px; font-weight: 700; }
        .stats { display: flex; gap: 10px; align-items: center; }
        .badge { background: #e0f2fe; color: #0369a1; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: bold; }
        .count-badge { background: #f1f5f9; color: #475569; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #f8fafc; padding: 12px 16px; text-align: right; font-weight: 600; color: #64748b; border-bottom: 2px solid #e2e8f0; font-size: 13px; }
        td { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        tr:hover { background: #f8fafc; }
        .status-badge { display: inline-block; padding: 4px 10px; border-radius: 8px; font-size: 12px; font-weight: bold; }
        .status-active { background: #dcfce7; color: #15803d; }
        .status-closed { background: #f1f5f9; color: #475569; }
        .status-won { background: #dbeafe; color: #1d4ed8; }
        .loader { text-align: center; padding: 20px; font-weight: bold; color: #0284c7; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .spinner { width: 18px; height: 18px; border: 3px solid #e0f2fe; border-top-color: #0284c7; border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .end-message { text-align: center; padding: 20px; color: #94a3b8; font-size: 14px; }
    </style></head><body>';
    $html .= '<div class="container">';
    $html .= '<div class="header"><h2>📋 قائمة القضايا (Lazy Loading 100,000)</h2><div class="stats"><span class="badge">تم تحميل: <span id="loaded-count">0</span></span><span class="count-badge">إجمالي: ' . number_format($totalCount) . '</span></div></div>';
    $html .= '<table><thead><tr><th>ID</th><th>رقم القضية</th><th>عنوان القضية</th><th>الموكل</th><th>المحكمة</th><th>الحالة</th><th>التاريخ</th></tr></thead><tbody id="cases-tbody"></tbody></table>';
    $html .= '<div id="loader" class="loader"><div class="spinner"></div> جاري تحميل 100,000 قضية إضافية تلقائياً...</div>';
    $html .= '<div id="end-msg" class="end-message" style="display:none;">تم تحميل جميع القضايا المتاحة.</div>';
    $html .= '</div>';
    
    $html .= '<script>
        let nextPageUrl = ' . json_encode($nextPageUrl) . ';
        let isLoading = false;
        let loadedCount = 0;
        const tbody = document.getElementById("cases-tbody");
        const loader = document.getElementById("loader");
        const endMsg = document.getElementById("end-msg");
        const countSpan = document.getElementById("loaded-count");

        function getStatusBadge(status) {
            let cls = "status-closed";
            if (status === "active") cls = "status-active";
            else if (status === "won") cls = "status-won";
            return `<span class="status-badge ${cls}">${status || "—"}</span>`;
        }

        function renderRows(items) {
            const CHUNK_SIZE = 1000;
            let index = 0;

            function processChunk() {
                const fragment = document.createDocumentFragment();
                const limit = Math.min(index + CHUNK_SIZE, items.length);
                for (; index < limit; index++) {
                    const c = items[index];
                    const tr = document.createElement("tr");
                    tr.innerHTML = `
                        <td><b>${c.id}</b></td>
                        <td><code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;">${c.case_number || "—"}</code></td>
                        <td><b>${c.title || "—"}</b></td>
                        <td>${c.client_name}</td>
                        <td>${c.court_name}</td>
                        <td>${getStatusBadge(c.status)}</td>
                        <td>${c.created_at}</td>
                    `;
                    fragment.appendChild(tr);
                }
                tbody.appendChild(fragment);
                loadedCount += (limit - (index - CHUNK_SIZE));
                countSpan.textContent = loadedCount.toLocaleString();

                if (index < items.length) {
                    requestAnimationFrame(processChunk);
                }
            }

            processChunk();
        }

        renderRows(' . $initialJson . ');

        async function loadMore() {
            if (isLoading || !nextPageUrl) return;
            isLoading = true;
            loader.style.display = "flex";

            try {
                const res = await fetch(nextPageUrl, { headers: { "Accept": "application/json" } });
                const json = await res.json();
                renderRows(json.data);
                nextPageUrl = json.next_page_url;
                if (!nextPageUrl || !json.has_more) {
                    loader.style.display = "none";
                    endMsg.style.display = "block";
                }
            } catch (err) {
                console.error("Error loading more cases:", err);
            } finally {
                isLoading = false;
            }
        }

        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting && nextPageUrl && !isLoading) {
                loadMore();
            }
        }, { rootMargin: "800px" });

        observer.observe(loader);
    </script></body></html>';

    return response($html);
});






