<?php

namespace WebWizr\AdminPanel\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Callback extends Model
{
    protected $fillable = [
        'callable_type',
        'callable_id',
        'scheduled_at',
        'completed_at',
        'status',
        'priority',
        'assigned_to',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the parent callable model (Customer, ContactInquiry, Deal)
     */
    public function callable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the assigned user
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who created this callback
     */
    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: Open callbacks only
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope: Completed callbacks
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: Overdue callbacks (past scheduled time, still open)
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', 'open')
            ->where('scheduled_at', '<', now());
    }

    /**
     * Scope: Today's callbacks
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->where('status', 'open')
            ->whereDate('scheduled_at', today());
    }

    /**
     * Scope: Future callbacks (after today)
     */
    public function scopeFuture(Builder $query): Builder
    {
        return $query->where('status', 'open')
            ->where('scheduled_at', '>', now()->endOfDay());
    }

    /**
     * Scope: Priority sorted (overdue first, then today, then future)
     */
    public function scopePrioritySorted(Builder $query): Builder
    {
        return $query->orderByRaw("
            CASE
                WHEN scheduled_at < NOW() THEN 1
                WHEN DATE(scheduled_at) = CURDATE() THEN 2
                ELSE 3
            END
        ")
        ->orderBy('priority', 'desc') // high priority first
        ->orderBy('scheduled_at', 'asc');
    }

    /**
     * Scope: Assigned to user
     */
    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Check if callback is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status === 'open' && $this->scheduled_at->isPast();
    }

    /**
     * Check if callback is today
     */
    public function isToday(): bool
    {
        return $this->scheduled_at->isToday();
    }

    /**
     * Mark callback as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark callback as cancelled
     */
    public function markAsCancelled(): void
    {
        $this->update([
            'status' => 'cancelled',
        ]);
    }

    /**
     * Get status options
     */
    public static function getStatusOptions(): array
    {
        return [
            'open' => __('admin.status_open'),
            'completed' => __('admin.status_completed'),
            'cancelled' => __('admin.status_cancelled'),
        ];
    }

    /**
     * Get priority options
     */
    public static function getPriorityOptions(): array
    {
        return [
            'normal' => __('admin.priority_normal'),
            'high' => __('admin.priority_high'),
        ];
    }

    /**
     * Get callable type options for forms
     */
    public static function getCallableTypes(): array
    {
        return [
            Customer::class => __('admin.customer'),
            ContactInquiry::class => __('admin.contact_inquiry'),
            Deal::class => __('admin.deal'),
        ];
    }

    /**
     * Get display name for the callable
     */
    public function getCallableNameAttribute(): string
    {
        if (!$this->callable) {
            return '-';
        }

        return match ($this->callable_type) {
            Customer::class => $this->callable->display_name,
            ContactInquiry::class => $this->callable->name . ' (' . $this->callable->getSourceLabel() . ')',
            Deal::class => $this->callable->title,
            default => '-',
        };
    }
}
