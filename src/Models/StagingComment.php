<?php

namespace WebWizr\AdminPanel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class StagingComment extends Model
{
    protected $fillable = [
        'session_id',
        'author_name',
        'author_email',
        'page_url',
        'section_selector',
        'position',
        'content',
        'attachments',
        'ai_suggestion',
        'ai_suggestion_approved',
        'status',
        'admin_response',
        'resolved_by',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'array',
            'attachments' => 'array',
            'ai_suggestion_approved' => 'boolean',
            'resolved_at' => 'datetime',
        ];
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // Scopes
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('status', 'resolved');
    }

    public function scopeForPage(Builder $query, string $pageUrl): Builder
    {
        return $query->where('page_url', $pageUrl);
    }

    public function scopeForSection(Builder $query, string $selector): Builder
    {
        return $query->where('section_selector', $selector);
    }

    public function scopeBySession(Builder $query, string $sessionId): Builder
    {
        return $query->where('session_id', $sessionId);
    }

    // Actions
    public function markAsInProgress(): void
    {
        $this->update(['status' => 'in_progress']);
    }

    public function resolve(?User $user = null, ?string $response = null): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_by' => $user?->id,
            'resolved_at' => now(),
            'admin_response' => $response,
        ]);
    }

    public function reject(?string $response = null): void
    {
        $this->update([
            'status' => 'rejected',
            'admin_response' => $response,
        ]);
    }
}
