<?php

namespace WebWizr\AdminPanel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatSetting extends Model
{
    protected $fillable = [
        'is_active',
        'icon_color',
        'background_color',
        'chat_background_color',
        'chat_text_color',
        'button_color',
        'button_text_color',
        'final_cta_phone',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ChatSettingTranslation::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ChatStep::class)->orderBy('sort_order');
    }

    public function getTranslation(?string $languageCode = null): ?ChatSettingTranslation
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
            'icon_color' => '#ffffff',
            'background_color' => '#3b82f6',
            'chat_background_color' => '#ffffff',
            'chat_text_color' => '#000000',
            'button_color' => '#3b82f6',
            'button_text_color' => '#ffffff',
        ]);
    }
}
