<?php

namespace WebWizr\AdminPanel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatStepTranslation extends Model
{
    protected $fillable = [
        'chat_step_id',
        'language_id',
        'question',
        'options',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
        ];
    }

    public function chatStep(): BelongsTo
    {
        return $this->belongsTo(ChatStep::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
