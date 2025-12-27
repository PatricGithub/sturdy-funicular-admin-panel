<?php

namespace WebWizr\AdminPanel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Translation extends Model
{
    protected $fillable = [
        'group',
        'key',
        'language_id',
        'value',
    ];

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public static function get(string $group, string $key, ?string $languageCode = null): ?string
    {
        $language = $languageCode
            ? Language::where('code', $languageCode)->first()
            : Language::getDefault();

        if (!$language) {
            return null;
        }

        $translation = static::where('group', $group)
            ->where('key', $key)
            ->where('language_id', $language->id)
            ->first();

        return $translation?->value;
    }
}
