<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogObserver
{
    public function created(Model $model): void
    {
        $this->logAction($model, 'created', null, $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $old = array_intersect_key($model->getOriginal(), $model->getChanges());
        $new = $model->getChanges();

        $this->logAction($model, 'updated', $old, $new);
    }

    public function deleted(Model $model): void
    {
        $this->logAction($model, 'deleted', $model->getOriginal(), null);
    }

    public function restored(Model $model): void
    {
        $this->logAction($model, 'restored', null, $model->getAttributes());
    }

    protected function logAction(Model $model, string $action, ?array $old, ?array $new): void
    {
        $tenantId = $model->tenant_id ?? app(TenantContext::class)->id();

        AuditLog::create([
            'tenant_id' => $tenantId,
            'user_id' => Auth::id(),
            'action' => $action,
            'entity_type' => get_class($model),
            'entity_id' => (string) $model->getKey(),
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'created_at' => now(),
        ]);
    }
}
