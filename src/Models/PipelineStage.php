<?php

namespace WebWizr\AdminPanel\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PipelineStage extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'color',
        'order',
        'is_won',
        'is_lost',
        'is_active',
    ];

    protected $casts = [
        'is_won' => 'boolean',
        'is_lost' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get all deals in this stage
     */
    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    /**
     * Scope: Active stages only
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Order by position
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order');
    }

    /**
     * Scope: Stages that are not end states (won/lost)
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('is_won', false)->where('is_lost', false);
    }

    /**
     * Get total deal value for this stage
     */
    public function getTotalValueAttribute(): float
    {
        return $this->deals()->sum('value') ?? 0;
    }

    /**
     * Get deal count for this stage
     */
    public function getDealCountAttribute(): int
    {
        return $this->deals()->count();
    }

    /**
     * Get all stages for select options
     */
    public static function getSelectOptions(): array
    {
        return self::active()
            ->ordered()
            ->pluck('name', 'id')
            ->toArray();
    }
}
