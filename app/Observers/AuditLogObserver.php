<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogObserver
{
    protected array $hiddenAttributes = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    public function created(Model $model): void
    {
        $this->logAction($model, 'created', null, $this->filterAttributes($model->getAttributes()));
    }

    public function updated(Model $model): void
    {
        $old = array_intersect_key($model->getOriginal(), $model->getChanges());
        $new = $model->getChanges();

        $this->logAction(
            $model,
            'updated',
            $this->filterAttributes($old),
            $this->filterAttributes($new)
        );
    }

    public function deleted(Model $model): void
    {
        $this->logAction($model, 'deleted', $this->filterAttributes($model->getOriginal()), null);
    }

    public function restored(Model $model): void
    {
        $this->logAction($model, 'restored', null, $this->filterAttributes($model->getAttributes()));
    }

    protected function logAction(Model $model, string $action, ?array $old, ?array $new): void
    {
        $tenantId = $model->tenant_id 
            ?? app(TenantContext::class)->id() 
            ?? (Auth::check() ? Auth::user()->currentTenant()?->id : null);

        if (!$tenantId) {
            return;
        }

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

    protected function filterAttributes(?array $attributes): ?array
    {
        if (!$attributes) {
            return null;
        }

        foreach ($this->hiddenAttributes as $key) {
            unset($attributes[$key]);
        }

        return $attributes;
    }
}
