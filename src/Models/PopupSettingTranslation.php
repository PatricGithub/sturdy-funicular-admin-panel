<?php

namespace WebWizr\AdminPanel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PopupSettingTranslation extends Model
{
    protected $fillable = [
        'popup_setting_id',
        'language_id',
        'headline',
        'subheadline',
        'cta_text',
        'cta_url',
    ];

    public function popupSetting(): BelongsTo
    {
        return $this->belongsTo(PopupSetting::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
