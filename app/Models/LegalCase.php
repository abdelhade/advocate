<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class LegalCase extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, BelongsToTenant, InteractsWithMedia;

    /**
     * Register media collections for case attachments.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments')
            ->acceptsMimeTypes([
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/gif',
            ]);
    }

    /**
     * Register media conversions (thumbnails for images).
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(200)
            ->height(200)
            ->sharpen(10)
            ->nonQueued();
    }

    protected $table = 'legal_cases';

    protected $fillable = [
        'tenant_id',
        'client_id',
        'case_number',
        'internal_number',
        'title',
        'court_name',
        'circuit',
        'case_type',
        'status',
        'primary_lawyer_id',
        'reminder_at',
    ];

    protected function casts(): array
    {
        return [
            'reminder_at' => 'datetime',
        ];
    }

    /**
     * Client who owns this case.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * Primary lawyer assigned to this case.
     */
    public function primaryLawyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_lawyer_id');
    }

    /**
     * Team members assigned to this case.
     */
    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'case_user', 'case_id', 'user_id')
            ->withPivot('assigned_role')
            ->withTimestamps();
    }

    /**
     * Parties involved in this case (opponents, third parties).
     */
    public function parties(): HasMany
    {
        return $this->hasMany(CaseParty::class, 'case_id');
    }

    /**
     * Court sessions for this case.
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(CourtSession::class, 'case_id');
    }

    /**
     * Documents associated with this case.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'case_id');
    }

    /**
     * Tasks associated with this case.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'case_id');
    }

    /**
     * Invoices generated for this case.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'case_id');
    }

    /**
     * Expenses recorded for this case.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'case_id');
    }
}
