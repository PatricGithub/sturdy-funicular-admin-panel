<?php

namespace WebWizr\AdminPanel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PopupSetting extends Model
{
    protected $fillable = [
        'is_active',
        'scroll_percentage',
        'image_path',
        'background_color',
        'text_color',
        'button_color',
        'button_text_color',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'scroll_percentage' => 'integer',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(PopupSettingTranslation::class);
    }

    public function getTranslation(?string $languageCode = null): ?PopupSettingTranslation
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
            'is_active' => false,
            'scroll_percentage' => 30,
            'background_color' => '#ffffff',
            'text_color' => '#000000',
            'button_color' => '#3b82f6',
            'button_text_color' => '#ffffff',
        ]);
    }
}
