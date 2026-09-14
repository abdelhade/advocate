<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'guard_name',
        'description',
    ];

    public function users(): BelongsToMany
    {
        return $this->morphedByMany(User::class, 'model', 'model_has_permissions')
            ->withPivot('tenant_id');
    }
}
