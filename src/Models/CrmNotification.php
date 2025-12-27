<?php

namespace WebWizr\AdminPanel\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CrmNotification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'notifiable_type',
        'notifiable_id',
        'title',
        'message',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the notifiable model
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope: Unread notifications
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope: For a specific user
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Mark as read
     */
    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }

    /**
     * Check if read
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Get type options
     */
    public static function getTypeOptions(): array
    {
        return [
            'mention' => __('admin.notification_mention'),
            'callback_assigned' => __('admin.notification_callback_assigned'),
            'task_assigned' => __('admin.notification_task_assigned'),
            'callback_reminder' => __('admin.notification_callback_reminder'),
        ];
    }

    /**
     * Get type icon
     */
    public function getTypeIcon(): string
    {
        return match ($this->type) {
            'mention' => 'heroicon-o-at-symbol',
            'callback_assigned' => 'heroicon-o-phone',
            'task_assigned' => 'heroicon-o-clipboard-document-check',
            'callback_reminder' => 'heroicon-o-bell',
            default => 'heroicon-o-bell',
        };
    }
}
