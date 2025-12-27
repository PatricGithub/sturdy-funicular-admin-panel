<?php

namespace WebWizr\AdminPanel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CrmActivity extends Model
{
    protected $fillable = [
        'activitable_type',
        'activitable_id',
        'type',
        'description',
        'old_value',
        'new_value',
        'user_id',
    ];

    /**
     * Get the activitable model (Customer, Deal, ContactInquiry)
     */
    public function activitable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who performed the activity
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get type options
     */
    public static function getTypeOptions(): array
    {
        return [
            'note' => __('admin.activity_note'),
            'status_change' => __('admin.activity_status_change'),
            'assignment' => __('admin.activity_assignment'),
            'callback_created' => __('admin.activity_callback_created'),
            'task_created' => __('admin.activity_task_created'),
            'email_sent' => __('admin.activity_email_sent'),
            'deal_stage_change' => __('admin.activity_deal_stage_change'),
            'phone_call_initiated' => __('admin.activity_phone_call'),
        ];
    }

    /**
     * Get type icon
     */
    public function getTypeIcon(): string
    {
        return match ($this->type) {
            'note' => 'heroicon-o-chat-bubble-left-ellipsis',
            'status_change' => 'heroicon-o-arrow-path',
            'assignment' => 'heroicon-o-user-plus',
            'callback_created' => 'heroicon-o-phone',
            'task_created' => 'heroicon-o-clipboard-document-check',
            'email_sent' => 'heroicon-o-envelope',
            'deal_stage_change' => 'heroicon-o-arrows-right-left',
            'phone_call_initiated' => 'heroicon-o-phone-arrow-up-right',
            default => 'heroicon-o-clock',
        };
    }

    /**
     * Get type color
     */
    public function getTypeColor(): string
    {
        return match ($this->type) {
            'note' => 'blue',
            'status_change' => 'yellow',
            'assignment' => 'purple',
            'callback_created' => 'green',
            'task_created' => 'indigo',
            'email_sent' => 'cyan',
            'deal_stage_change' => 'orange',
            'phone_call_initiated' => 'success',
            default => 'gray',
        };
    }

    /**
     * Log a note activity
     */
    public static function logNote(Model $model, string $description, ?int $userId = null): self
    {
        return self::create([
            'activitable_type' => get_class($model),
            'activitable_id' => $model->id,
            'type' => 'note',
            'description' => $description,
            'user_id' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Log a status change
     */
    public static function logStatusChange(Model $model, string $oldStatus, string $newStatus, ?int $userId = null): self
    {
        return self::create([
            'activitable_type' => get_class($model),
            'activitable_id' => $model->id,
            'type' => 'status_change',
            'old_value' => $oldStatus,
            'new_value' => $newStatus,
            'user_id' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Log an assignment change
     */
    public static function logAssignment(Model $model, ?string $oldAssignee, ?string $newAssignee, ?int $userId = null): self
    {
        return self::create([
            'activitable_type' => get_class($model),
            'activitable_id' => $model->id,
            'type' => 'assignment',
            'old_value' => $oldAssignee,
            'new_value' => $newAssignee,
            'user_id' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Log a phone call initiation
     */
    public static function logPhoneCall(Model $model, ?int $userId = null): self
    {
        return self::create([
            'activitable_type' => get_class($model),
            'activitable_id' => $model->id,
            'type' => 'phone_call_initiated',
            'description' => 'Anruf gestartet',
            'user_id' => $userId ?? auth()->id(),
        ]);
    }
}
