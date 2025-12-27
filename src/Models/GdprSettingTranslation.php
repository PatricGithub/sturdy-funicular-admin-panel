<?php

namespace WebWizr\AdminPanel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GdprSettingTranslation extends Model
{
    protected $fillable = [
        'gdpr_setting_id',
        'language_id',
        'message',
        'accept_button_text',
        'decline_button_text',
        'privacy_link_text',
    ];

    public function gdprSetting(): BelongsTo
    {
        return $this->belongsTo(GdprSetting::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
