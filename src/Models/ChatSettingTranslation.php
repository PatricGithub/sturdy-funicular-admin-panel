<?php

namespace WebWizr\AdminPanel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatSettingTranslation extends Model
{
    protected $fillable = [
        'chat_setting_id',
        'language_id',
        'welcome_title',
        'welcome_message',
        'final_title',
        'final_message',
        'final_cta_text',
    ];

    public function chatSetting(): BelongsTo
    {
        return $this->belongsTo(ChatSetting::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
