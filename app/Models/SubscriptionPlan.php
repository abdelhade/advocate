<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'tagline',
        'max_users',
        'max_storage_mb',
        'features',
        'price_monthly',
        'price_yearly',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'price_monthly' => 'decimal:2',
            'price_yearly' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    public function isFree(): bool
    {
        return (float) $this->price_monthly <= 0 && (float) $this->price_yearly <= 0;
    }

    public function priceLabel(string $period = 'yearly'): string
    {
        if ($this->isFree()) {
            return 'مجاني';
        }

        $amount = $period === 'monthly' ? $this->price_monthly : $this->price_yearly;

        return number_format((float) $amount).' ج.م / '.($period === 'monthly' ? 'شهر' : 'سنة');
    }
}
