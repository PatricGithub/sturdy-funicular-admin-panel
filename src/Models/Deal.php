<?php

namespace WebWizr\AdminPanel\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Deal extends Model
{
    protected $fillable = [
        'title',
        'customer_id',
        'contact_inquiry_id',
        'pipeline_stage_id',
        'value',
        'expected_close_date',
        'assigned_to',
        'notes',
        'won_at',
        'lost_at',
        'lost_reason',
        'created_by',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'expected_close_date' => 'date',
        'won_at' => 'datetime',
        'lost_at' => 'datetime',
    ];

    /**
     * Get the customer for this deal
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the original inquiry for this deal
     */
    public function contactInquiry(): BelongsTo
    {
        return $this->belongsTo(ContactInquiry::class);
    }

    /**
     * Get the pipeline stage for this deal
     */
    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'pipeline_stage_id');
    }

    /**
     * Get the assigned user for this deal
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who created this deal
     */
    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all callbacks for this deal
     */
    public function callbacks(): MorphMany
    {
        return $this->morphMany(Callback::class, 'callable');
    }

    /**
     * Get all tasks for this deal
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Get all activities for this deal
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(CrmActivity::class, 'activitable');
    }

    /**
     * Scope: Open deals (not won or lost)
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNull('won_at')->whereNull('lost_at');
    }

    /**
     * Scope: Won deals
     */
    public function scopeWon(Builder $query): Builder
    {
        return $query->whereNotNull('won_at');
    }

    /**
     * Scope: Lost deals
     */
    public function scopeLost(Builder $query): Builder
    {
        return $query->whereNotNull('lost_at');
    }

    /**
     * Scope: Deals assigned to a user
     */
    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope: Deals in a specific stage
     */
    public function scopeInStage(Builder $query, int $stageId): Builder
    {
        return $query->where('pipeline_stage_id', $stageId);
    }

    /**
     * Check if deal is won
     */
    public function isWon(): bool
    {
        return $this->won_at !== null;
    }

    /**
     * Check if deal is lost
     */
    public function isLost(): bool
    {
        return $this->lost_at !== null;
    }

    /**
     * Check if deal is open (not closed)
     */
    public function isOpen(): bool
    {
        return !$this->isWon() && !$this->isLost();
    }

    /**
     * Mark deal as won
     */
    public function markAsWon(): void
    {
        $wonStage = PipelineStage::where('is_won', true)->first();

        $this->update([
            'won_at' => now(),
            'pipeline_stage_id' => $wonStage?->id ?? $this->pipeline_stage_id,
        ]);
    }

    /**
     * Mark deal as lost
     */
    public function markAsLost(?string $reason = null): void
    {
        $lostStage = PipelineStage::where('is_lost', true)->first();

        $this->update([
            'lost_at' => now(),
            'lost_reason' => $reason,
            'pipeline_stage_id' => $lostStage?->id ?? $this->pipeline_stage_id,
        ]);
    }

    /**
     * Get formatted value
     */
    public function getFormattedValueAttribute(): string
    {
        if ($this->value === null) {
            return '-';
        }

        return number_format($this->value, 2, ',', '.') . ' €';
    }
}
