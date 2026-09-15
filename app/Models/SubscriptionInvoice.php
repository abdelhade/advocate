<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'tenant_id',
        'subscription_id',
        'plan_id',
        'plan_name',
        'billing_period',
        'amount',
        'tax_amount',
        'total_amount',
        'status',
        'issued_at',
        'due_date',
        'paid_at',
        'payment_method',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'issued_at' => 'date',
            'due_date' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'overdue' || ($this->status === 'pending' && $this->due_date && $this->due_date->isPast());
    }

    public function displayStatus(): string
    {
        return match ($this->status) {
            'paid' => 'مدفوعة',
            'pending' => 'معلقة',
            'overdue' => 'متأخرة',
            'cancelled' => 'ملغاة',
            default => $this->status,
        };
    }

    public function displayPaymentMethod(): string
    {
        return match ($this->payment_method) {
            'bank_transfer' => 'تحويل بنكي',
            'credit_card' => 'بطاقة ائتمان',
            'cash' => 'نقداً',
            'free' => 'مجاني / تجريبي',
            'other' => 'طريقة أخرى',
            default => $this->payment_method ?? 'غير محدد',
        };
    }
}
