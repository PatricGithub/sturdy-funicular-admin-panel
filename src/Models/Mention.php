<?php

namespace WebWizr\AdminPanel\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Mention extends Model
{
    protected $fillable = [
        'mentionable_type',
        'mentionable_id',
        'user_id',
        'mentioned_by',
        'note_content',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    /**
     * Get the mentionable model (where mention occurred)
     */
    public function mentionable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the mentioned user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who made the mention
     */
    public function mentionedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentioned_by');
    }

    /**
     * Scope: Unread mentions
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
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
        $this->update(['is_read' => true]);
    }

    /**
     * Get mentionable display name
     */
    public function getMentionableNameAttribute(): string
    {
        if (!$this->mentionable) {
            return '-';
        }

        return match ($this->mentionable_type) {
            Customer::class => $this->mentionable->display_name,
            ContactInquiry::class => $this->mentionable->name,
            Deal::class => $this->mentionable->title,
            default => '-',
        };
    }
}
