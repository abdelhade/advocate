<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\CourtSession;
use App\Models\Document;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\LegalCase;
use App\Models\Payment;
use App\Models\Task;
use App\Models\User;
use App\Observers\AuditLogObserver;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\TenantContext::class, function () {
            return new \App\Services\TenantContext();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Register AuditLogObserver for all system entities
        Client::observe(AuditLogObserver::class);
        LegalCase::observe(AuditLogObserver::class);
        CourtSession::observe(AuditLogObserver::class);
        Document::observe(AuditLogObserver::class);
        Invoice::observe(AuditLogObserver::class);
        Payment::observe(AuditLogObserver::class);
        Expense::observe(AuditLogObserver::class);
        Task::observe(AuditLogObserver::class);
        User::observe(AuditLogObserver::class);
    }
}
