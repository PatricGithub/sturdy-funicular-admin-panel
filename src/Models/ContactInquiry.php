<?php

namespace WebWizr\AdminPanel\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ContactInquiry extends Model
{
    protected $fillable = [
        'source',
        'move_type',
        'move_size',
        'move_date',
        'address_from',
        'address_to',
        'name',
        'email',
        'phone',
        'preferred_contact',
        'message',
        'status',
        'assigned_to',
        'notes',
    ];

    /**
     * Get the user this inquiry is assigned to
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get all activities for this inquiry
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(CrmActivity::class, 'activitable')->latest();
    }

    /**
     * Get all callbacks for this inquiry
     */
    public function callbacks(): MorphMany
    {
        return $this->morphMany(Callback::class, 'callable');
    }

    /**
     * Get all tasks for this inquiry
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Scope: Filter by status 'new'
     */
    public function scopeNew(Builder $query): Builder
    {
        return $query->where('status', 'new');
    }

    /**
     * Scope: Filter by source
     */
    public function scopeBySource(Builder $query, string $source): Builder
    {
        return $query->where('source', $source);
    }

    /**
     * Scope: Unassigned inquiries
     */
    public function scopeUnassigned(Builder $query): Builder
    {
        return $query->whereNull('assigned_to');
    }

    /**
     * Scope: Assigned to a specific user
     */
    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Get all available sources
     */
    public static function getSources(): array
    {
        return [
            'home_form' => 'Startseite',
            'chat_widget' => 'Chat Widget',
            'consultation' => 'Beratungsseite',
            'lead_magnet' => 'Lead Magnet',
            'service_private' => 'Service: Privatumzug',
            'service_business' => 'Service: Firmenumzug',
            'service_furniture' => 'Service: Möbeltransport',
            'service_international' => 'Service: Umzug ins Ausland',
            'service_assembly' => 'Service: Möbelmontage',
            'service_clearance' => 'Service: Entrümpelung',
            'service_storage' => 'Service: Lagerung',
            'service_jobcenter' => 'Service: Bürgergeld Umzug',
        ];
    }

    /**
     * Get source options for Filament
     */
    public static function getSourceOptions(): array
    {
        return self::getSources();
    }

    /**
     * Get statuses from PipelineStage model
     */
    public static function getStatuses(): array
    {
        return PipelineStage::active()
            ->ordered()
            ->pluck('name', 'slug')
            ->toArray();
    }

    /**
     * Get status options for Filament
     */
    public static function getStatusOptions(): array
    {
        return self::getStatuses();
    }

    /**
     * Get status color for badges (Filament color name)
     */
    public function getStatusColor(): string
    {
        $stage = PipelineStage::where('slug', $this->status)->first();
        if ($stage) {
            // Map PipelineStage to Filament color names
            if ($stage->is_won) {
                return 'success';
            }
            if ($stage->is_lost) {
                return 'danger';
            }
        }

        return match ($this->status) {
            'lead' => 'info',
            'qualified' => 'primary',
            'proposal', 'negotiation' => 'warning',
            'won' => 'success',
            'lost' => 'danger',
            default => 'gray',
        };
    }

    /**
     * Get status hex color from PipelineStage
     */
    public function getStatusHexColor(): string
    {
        $stage = PipelineStage::where('slug', $this->status)->first();

        return $stage?->color ?? '#6b7280';
    }

    /**
     * Get source label
     */
    public function getSourceLabel(): string
    {
        return self::getSources()[$this->source] ?? $this->source;
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    /**
     * Get move type options
     */
    public static function getMoveTypes(): array
    {
        return [
            'privat' => 'Privatumzug',
            'firma' => 'Firmenumzug',
            'transport' => 'Möbeltransport',
            'ausland' => 'Umzug ins Ausland',
            'montage' => 'Möbelmontage',
            'entruempelung' => 'Entrümpelung',
        ];
    }

    /**
     * Get move size options
     */
    public static function getMoveSizes(): array
    {
        return [
            'klein' => '1-2 Zimmer',
            'mittel' => '3-4 Zimmer',
            'haus' => 'Haus',
            'buero' => 'Büro/Gewerbe',
        ];
    }

    /**
     * Get preferred contact options
     */
    public static function getContactPreferences(): array
    {
        return [
            'phone' => 'Telefon',
            'email' => 'E-Mail',
            'whatsapp' => 'WhatsApp',
        ];
    }
}
