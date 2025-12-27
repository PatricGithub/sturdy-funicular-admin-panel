<?php

namespace WebWizr\AdminPanel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GdprSetting extends Model
{
    protected $fillable = [
        'is_active',
        'background_color',
        'text_color',
        'button_color',
        'button_text_color',
        'link_color',
        'privacy_policy_url',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(GdprSettingTranslation::class);
    }

    public function getTranslation(?string $languageCode = null): ?GdprSettingTranslation
    {
        $language = $languageCode
            ? Language::where('code', $languageCode)->first()
            : Language::getDefault();

        if (!$language) {
            return $this->translations()->first();
        }

        return $this->translations()->where('language_id', $language->id)->first()
            ?? $this->translations()->first();
    }

    public static function getInstance(): self
    {
        return static::firstOrCreate([], [
            'is_active' => true,
            'background_color' => '#1f2937',
            'text_color' => '#ffffff',
            'button_color' => '#3b82f6',
            'button_text_color' => '#ffffff',
            'link_color' => '#60a5fa',
        ]);
    }
}
