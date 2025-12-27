<?php

namespace WebWizr\AdminPanel\Models;

use Illuminate\Database\Eloquent\Model;

class StagingReviewSettings extends Model
{
    protected $fillable = [
        'review_mode_enabled',
        'allow_direct_changes',
        'ai_requests_per_day',
        'ai_requests_used_today',
        'ai_requests_reset_date',
    ];

    protected function casts(): array
    {
        return [
            'review_mode_enabled' => 'boolean',
            'allow_direct_changes' => 'boolean',
            'ai_requests_reset_date' => 'date',
        ];
    }

    /**
     * Get singleton instance of settings
     */
    public static function getInstance(): self
    {
        $settings = static::first();

        if (!$settings) {
            $settings = static::create([
                'review_mode_enabled' => true,
                'allow_direct_changes' => false,
                'ai_requests_per_day' => 10,
                'ai_requests_used_today' => 0,
            ]);
        }

        // Reset daily counter if needed
        if ($settings->ai_requests_reset_date !== today()) {
            $settings->update([
                'ai_requests_used_today' => 0,
                'ai_requests_reset_date' => today(),
            ]);
        }

        return $settings;
    }

    /**
     * Check if AI requests are available
     */
    public function canMakeAiRequest(): bool
    {
        return $this->ai_requests_used_today < $this->ai_requests_per_day;
    }

    /**
     * Increment the AI request counter
     */
    public function useAiRequest(): void
    {
        $this->increment('ai_requests_used_today');
    }

    /**
     * Get remaining AI requests for today
     */
    public function getRemainingAiRequests(): int
    {
        return max(0, $this->ai_requests_per_day - $this->ai_requests_used_today);
    }
}
