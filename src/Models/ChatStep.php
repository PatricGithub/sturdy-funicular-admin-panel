<?php

namespace WebWizr\AdminPanel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatStep extends Model
{
    protected $fillable = [
        'chat_setting_id',
        'type',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function chatSetting(): BelongsTo
    {
        return $this->belongsTo(ChatSetting::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ChatStepTranslation::class);
    }

    public function getTranslation(?string $languageCode = null): ?ChatStepTranslation
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
}
