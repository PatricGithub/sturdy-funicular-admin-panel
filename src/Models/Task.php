<?php

namespace WebWizr\AdminPanel\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Task extends Model
{
    protected $fillable = [
        'taskable_type',
        'taskable_id',
        'title',
        'description',
        'type',
        'status',
        'priority',
        'due_date',
        'assigned_to',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the parent taskable model (Customer, ContactInquiry, Deal)
     */
    public function taskable(): MorphTo
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
     * Get the user who created this task
     */
    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: Open tasks only
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope: In progress tasks
     */
    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope: Completed tasks
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: Not completed tasks
     */
    public function scopeNotCompleted(Builder $query): Builder
    {
        return $query->whereIn('status', ['open', 'in_progress']);
    }

    /**
     * Scope: Overdue tasks (past due date, not completed)
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereIn('status', ['open', 'in_progress'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', today());
    }

    /**
     * Scope: Due today
     */
    public function scopeDueToday(Builder $query): Builder
    {
        return $query->whereIn('status', ['open', 'in_progress'])
            ->whereDate('due_date', today());
    }

    /**
     * Scope: Due this week
     */
    public function scopeDueThisWeek(Builder $query): Builder
    {
        return $query->whereIn('status', ['open', 'in_progress'])
            ->whereBetween('due_date', [today(), today()->endOfWeek()]);
    }

    /**
     * Scope: Priority sorted (overdue first, then today, then by due date)
     */
    public function scopePrioritySorted(Builder $query): Builder
    {
        return $query->orderByRaw("
            CASE
                WHEN due_date IS NOT NULL AND due_date < CURDATE() THEN 1
                WHEN due_date = CURDATE() THEN 2
                WHEN due_date IS NOT NULL THEN 3
                ELSE 4
            END
        ")
        ->orderByRaw("
            CASE priority
                WHEN 'high' THEN 1
                WHEN 'normal' THEN 2
                WHEN 'low' THEN 3
            END
        ")
        ->orderBy('due_date', 'asc');
    }

    /**
     * Scope: Assigned to user
     */
    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope: Filter by type
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Check if task is overdue
     */
    public function isOverdue(): bool
    {
        if (!$this->due_date || $this->status === 'completed') {
            return false;
        }

        return $this->due_date->isPast();
    }

    /**
     * Check if task is due today
     */
    public function isDueToday(): bool
    {
        return $this->due_date?->isToday() ?? false;
    }

    /**
     * Mark task as in progress
     */
    public function markAsInProgress(): void
    {
        $this->update(['status' => 'in_progress']);
    }

    /**
     * Mark task as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Get status options
     */
    public static function getStatusOptions(): array
    {
        return [
            'open' => __('admin.status_open'),
            'in_progress' => __('admin.status_in_progress'),
            'completed' => __('admin.status_completed'),
        ];
    }

    /**
     * Get priority options
     */
    public static function getPriorityOptions(): array
    {
        return [
            'low' => __('admin.priority_low'),
            'normal' => __('admin.priority_normal'),
            'high' => __('admin.priority_high'),
        ];
    }

    /**
     * Get type options
     */
    public static function getTypeOptions(): array
    {
        return [
            'email' => __('admin.task_type_email'),
            'document_review' => __('admin.task_type_document_review'),
            'follow_up' => __('admin.task_type_follow_up'),
            'internal' => __('admin.task_type_internal'),
            'other' => __('admin.task_type_other'),
        ];
    }

    /**
     * Get type icon
     */
    public function getTypeIcon(): string
    {
        return match ($this->type) {
            'email' => 'heroicon-o-envelope',
            'document_review' => 'heroicon-o-document-text',
            'follow_up' => 'heroicon-o-arrow-path',
            'internal' => 'heroicon-o-building-office',
            default => 'heroicon-o-clipboard-document-list',
        };
    }

    /**
     * Get taskable types for forms
     */
    public static function getTaskableTypes(): array
    {
        return [
            Customer::class => __('admin.customer'),
            ContactInquiry::class => __('admin.contact_inquiry'),
            Deal::class => __('admin.deal'),
        ];
    }

    /**
     * Get display name for the taskable
     */
    public function getTaskableNameAttribute(): ?string
    {
        if (!$this->taskable) {
            return null;
        }

        return match ($this->taskable_type) {
            Customer::class => $this->taskable->display_name,
            ContactInquiry::class => $this->taskable->name . ' (' . $this->taskable->getSourceLabel() . ')',
            Deal::class => $this->taskable->title,
            default => null,
        };
    }
}
