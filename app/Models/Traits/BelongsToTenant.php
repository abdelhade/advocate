<?php

namespace App\Models\Traits;

use App\Models\Tenant;
use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    /**
     * Boot the trait to apply tenant global scope and auto-assign tenant_id.
     */
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $query) {
            $context = app(TenantContext::class);
            if ($context->check()) {
                $query->where($query->getModel()->getTable() . '.tenant_id', $context->id());
            }
        });

        static::creating(function ($model) {
            $context = app(TenantContext::class);
            if ($context->check() && empty($model->tenant_id)) {
                $model->tenant_id = $context->id();
            }
        });
    }

    /**
     * Get the tenant that owns the model.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
