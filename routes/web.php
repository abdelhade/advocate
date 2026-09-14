<?php

use App\Http\Controllers\Admin\AdminAdminController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminTenantController;
use App\Http\Controllers\Central\RegisterController;
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
use App\Http\Controllers\TenantSwitchController;
use App\Http\Middleware\AdminAuthenticated;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Central / Home Routes
Route::get('/', function () {
    $tenantCount = \App\Models\Tenant::count();
    $clientCount = \App\Models\Client::withoutGlobalScopes()->count();
    $caseCount = \App\Models\LegalCase::withoutGlobalScopes()->count();
    $invoiceCount = \App\Models\Invoice::withoutGlobalScopes()->count();

    $formatNumber = function ($number) {
        if ($number >= 1000000) {
            return '+' . round($number / 1000000, 1) . ' مليون';
        }
        if ($number >= 1000) {
            return '+' . round($number / 1000, 1) . ' ألف';
        }
        return '+' . number_format($number);
    };

    $stats = [
        ['value' => $formatNumber($tenantCount), 'label' => 'مكتب محاماة'],
        ['value' => $formatNumber($clientCount), 'label' => 'موكل مخدوم'],
        ['value' => $formatNumber($caseCount), 'label' => 'قضية مُدارة'],
        ['value' => $formatNumber($invoiceCount), 'label' => 'فاتورة ومطالبة أتعاب'],
    ];

    return Inertia::render('CentralWelcome', [
        'stats' => $stats,
        'realCounts' => [
            'tenants' => $tenantCount,
            'clients' => $clientCount,
            'cases' => $caseCount,
            'invoices' => $invoiceCount,
        ],
    ]);
});

Route::get('/pricing', function () {
    return Inertia::render('Central/Pricing');
})->name('central.pricing');

// Central Registration
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('central.register');
Route::post('/register', [RegisterController::class, 'register'])->name('central.register.submit');

// Admin Auth (Guest)
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');

// Admin Protected Routes
Route::middleware(AdminAuthenticated::class)->prefix('admin')->group(function () {
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // Tenant Management
    Route::get('/tenants', [AdminTenantController::class, 'index'])->name('admin.tenants.index');
    Route::get('/tenants/create', [AdminTenantController::class, 'create'])->name('admin.tenants.create');
    Route::post('/tenants', [AdminTenantController::class, 'store'])->name('admin.tenants.store');
    Route::get('/tenants/{id}', [AdminTenantController::class, 'show'])->name('admin.tenants.show');
    Route::get('/tenants/{id}/edit', [AdminTenantController::class, 'edit'])->name('admin.tenants.edit');
    Route::delete('/tenants/{id}', [AdminTenantController::class, 'destroy'])->name('admin.tenants.destroy');

    Route::post('/tenants/{id}/extend-trial', [AdminTenantController::class, 'extendTrial'])->name('admin.tenants.extend_trial');
    Route::post('/tenants/{id}/activate', [AdminTenantController::class, 'activateSubscription'])->name('admin.tenants.activate');

    // Admins
    Route::get('/admins', [AdminAdminController::class, 'index'])->name('admin.admins.index');
    Route::get('/admins/create', [AdminAdminController::class, 'create'])->name('admin.admins.create');
    Route::post('/admins', [AdminAdminController::class, 'store'])->name('admin.admins.store');
    Route::get('/admins/{id}/edit', [AdminAdminController::class, 'edit'])->name('admin.admins.edit');
    Route::put('/admins/{id}', [AdminAdminController::class, 'update'])->name('admin.admins.update');
    Route::delete('/admins/{id}', [AdminAdminController::class, 'destroy'])->name('admin.admins.destroy');
});

use App\Http\Controllers\Tenant\TenantUserController;

// Authenticated Tenant Operational Routes
Route::middleware(['auth'])->group(function () {
    // Tenant Switching
    Route::post('/switch-tenant/{tenant}', TenantSwitchController::class)->name('tenant.switch');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Office Team / Users Management
    Route::resource('tenant-users', TenantUserController::class)->names([
        'index' => 'tenant.users.index',
        'store' => 'tenant.users.store',
        'destroy' => 'tenant.users.destroy',
    ])->only(['index', 'store', 'destroy']);

    // Clients
    Route::resource('clients', ClientController::class);

    // Legal Cases & Sessions
    Route::resource('cases', CaseController::class);
    Route::post('cases/{case}/sessions', [SessionController::class, 'store'])->name('cases.sessions.store');
    Route::delete('cases/{case}/sessions/{session}', [SessionController::class, 'destroy'])->name('cases.sessions.destroy');

    // Tasks Management
    Route::resource('tasks', TaskController::class)->except(['create', 'edit', 'show']);
    Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');

    // Financials: Invoices, Payments, Expenses
    Route::resource('invoices', InvoiceController::class)->except(['edit', 'update']);
    Route::resource('payments', PaymentController::class)->only(['index', 'store', 'destroy']);
    Route::resource('expenses', ExpenseController::class)->only(['index', 'store', 'destroy']);

    // Audit Logs
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    // Document Upload & Secure Private Streamed Download
    Route::post('documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
