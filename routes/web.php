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

    return Inertia::render('CentralWelcome', $data);
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
