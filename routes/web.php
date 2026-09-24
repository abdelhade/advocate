<?php

use App\Http\Controllers\Admin\AdminAdminController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminSubscriptionInvoiceController;
use App\Http\Controllers\Admin\AdminTenantController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Central\RegisterController;
use App\Http\Middleware\AdminAuthenticated;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Central domain routes (jalsateg.com / localhost)
| Admin lives at /admin — not on a subdomain.
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    $data = Cache::remember('central_welcome_stats', 3600, function () {
        $getFastCount = function (string $tableName, callable $fallbackCount) {
            try {
                $dbName = config('database.connections.mysql.database');
                $row = \Illuminate\Support\Facades\DB::selectOne(
                    "SELECT table_rows FROM information_schema.tables WHERE table_schema = ? AND table_name = ?",
                    [$dbName, $tableName]
                );
                if ($row && isset($row->table_rows) && (int) $row->table_rows > 0) {
                    return (int) $row->table_rows;
                }
            } catch (\Throwable $e) {
                // Ignore and use fallback
            }

            return $fallbackCount();
        };

        $tenantCount = $getFastCount('tenants', fn () => \App\Models\Tenant::count());
        $clientCount = $getFastCount('clients', fn () => \App\Models\Client::withoutGlobalScopes()->count());
        $caseCount = $getFastCount('legal_cases', fn () => \App\Models\LegalCase::withoutGlobalScopes()->count());
        $invoiceCount = $getFastCount('invoices', fn () => \App\Models\Invoice::withoutGlobalScopes()->count());

        $formatNumber = function ($number) {
            if ($number >= 1000000) {
                return '+'.round($number / 1000000, 1).' مليون';
            }
            if ($number >= 1000) {
                return '+'.round($number / 1000, 1).' ألف';
            }

            return '+'.number_format($number);
        };

        return [
            'stats' => [
                ['value' => $formatNumber($tenantCount), 'label' => 'مكتب محاماة'],
                ['value' => $formatNumber($clientCount), 'label' => 'موكل مخدوم'],
                ['value' => $formatNumber($caseCount), 'label' => 'قضية مُدارة'],
                ['value' => $formatNumber($invoiceCount), 'label' => 'فاتورة ومطالبة أتعاب'],
            ],
            'realCounts' => [
                'tenants' => $tenantCount,
                'clients' => $clientCount,
                'cases' => $caseCount,
                'invoices' => $invoiceCount,
            ],
        ];
    });

    $tenantDashboardUrl = null;
    $user = auth()->user();
    if ($user) {
        $tenant = $user->tenants()->where('tenants.status', 'active')->first();
        if ($tenant) {
            $tenantDashboardUrl = \App\Support\TenantUrl::for($tenant, '/dashboard', request());
        }
    }

    return Inertia::render('CentralWelcome', array_merge($data, [
        'tenantDashboardUrl' => $tenantDashboardUrl,
    ]));
})->name('central.home');

Route::get('/pricing', function () {
    return Inertia::render('Central/Pricing');
})->name('central.pricing');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('central.register');
Route::post('/register', [RegisterController::class, 'register'])->name('central.register.submit');

// Central login → redirects to the user's office subdomain after auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('central.login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('central.login.submit');
});

// Named separately from tenant `dashboard` — Laravel keeps the FIRST name
// registration, so a shared name would always resolve to a central domain (e.g. 127.0.0.1).
Route::get('/dashboard', function (\Illuminate\Http\Request $request) {
    $user = $request->user();

    if (! $user) {
        return redirect()->route('central.login');
    }

    if ($user->is_super_admin) {
        return redirect()->route('admin.dashboard');
    }

    $tenant = $user->tenants()->where('tenants.status', 'active')->first();

    if ($tenant) {
        return Inertia::location(\App\Support\TenantUrl::for($tenant, '/dashboard', $request));
    }

    return redirect()->route('central.home');
})->middleware('auth')->name('central.dashboard');


// Admin Auth (Guest)
Route::redirect('/admin', '/admin/dashboard');
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');

// Admin Protected Routes — path /admin on central domain only
Route::middleware(AdminAuthenticated::class)->prefix('admin')->group(function () {
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/tenants', [AdminTenantController::class, 'index'])->name('admin.tenants.index');
    Route::get('/tenants/create', [AdminTenantController::class, 'create'])->name('admin.tenants.create');
    Route::post('/tenants', [AdminTenantController::class, 'store'])->name('admin.tenants.store');
    Route::get('/tenants/{id}', [AdminTenantController::class, 'show'])->name('admin.tenants.show');
    Route::get('/tenants/{id}/edit', [AdminTenantController::class, 'edit'])->name('admin.tenants.edit');
    Route::delete('/tenants/{id}', [AdminTenantController::class, 'destroy'])->name('admin.tenants.destroy');

    Route::post('/tenants/{id}/extend-trial', [AdminTenantController::class, 'extendSubscription'])->name('admin.tenants.extend_trial');
    Route::post('/tenants/{id}/extend', [AdminTenantController::class, 'extendSubscription'])->name('admin.tenants.extend');
    Route::post('/tenants/{id}/activate', [AdminTenantController::class, 'activateSubscription'])->name('admin.tenants.activate');
    Route::post('/tenants/{id}/toggle-status', [AdminTenantController::class, 'toggleStatus'])->name('admin.tenants.toggle_status');
    Route::post('/tenants/{id}/plan', [AdminTenantController::class, 'updatePlan'])->name('admin.tenants.update_plan');
    Route::post('/tenants/bulk-auto-renew', [AdminTenantController::class, 'bulkAutoRenew'])->name('admin.tenants.bulk_auto_renew');

    Route::get('/admins', [AdminAdminController::class, 'index'])->name('admin.admins.index');
    Route::get('/admins/create', [AdminAdminController::class, 'create'])->name('admin.admins.create');
    Route::post('/admins', [AdminAdminController::class, 'store'])->name('admin.admins.store');
    Route::get('/admins/{id}/edit', [AdminAdminController::class, 'edit'])->name('admin.admins.edit');
    Route::put('/admins/{id}', [AdminAdminController::class, 'update'])->name('admin.admins.update');
    Route::delete('/admins/{id}', [AdminAdminController::class, 'destroy'])->name('admin.admins.destroy');

    Route::get('/tenants/search-api', [AdminSubscriptionInvoiceController::class, 'searchTenants'])->name('admin.tenants.search_api');
    Route::get('/invoices', [AdminSubscriptionInvoiceController::class, 'index'])->name('admin.invoices.index');
    Route::post('/invoices', [AdminSubscriptionInvoiceController::class, 'store'])->name('admin.invoices.store');
    Route::get('/invoices/{id}', [AdminSubscriptionInvoiceController::class, 'show'])->name('admin.invoices.show');
    Route::post('/invoices/{id}/status', [AdminSubscriptionInvoiceController::class, 'updateStatus'])->name('admin.invoices.update_status');
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






