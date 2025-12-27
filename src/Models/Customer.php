<?php

namespace WebWizr\AdminPanel\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Customer extends Model
{
    protected $fillable = [
        'company_name',
        'contact_name',
        'email',
        'phone',
        'address',
        'city',
        'postal_code',
        'notes',
        'source_inquiry_id',
        'created_by',
        'assigned_to',
    ];

    /**
     * Get the original inquiry this customer was converted from
     */
    public function sourceInquiry(): BelongsTo
    {
        return $this->belongsTo(ContactInquiry::class, 'source_inquiry_id');
    }

    /**
     * Get the user who created this customer
     */
    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user this customer is assigned to
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get all deals for this customer
     */
    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    /**
     * Get all callbacks for this customer
     */
    public function callbacks(): MorphMany
    {
        return $this->morphMany(Callback::class, 'callable');
    }

    /**
     * Get all tasks for this customer
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Get all activities for this customer
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(CrmActivity::class, 'activitable');
    }

    /**
     * Scope: Filter by assigned user
     */
    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Get display name (company or contact name)
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->company_name) {
            return $this->company_name . ' (' . $this->contact_name . ')';
        }
        return $this->contact_name;
    }

    /**
     * Get full address
     */
    public function getFullAddressAttribute(): ?string
    {
        $parts = array_filter([
            $this->address,
            trim($this->postal_code . ' ' . $this->city),
        ]);

        return count($parts) > 0 ? implode(', ', $parts) : null;
    }

    /**
     * Create customer from ContactInquiry
     */
    public static function createFromInquiry(ContactInquiry $inquiry, ?int $userId = null): self
    {
        return self::create([
            'contact_name' => $inquiry->name,
            'email' => $inquiry->email,
            'phone' => $inquiry->phone,
            'address' => $inquiry->address_from,
            'notes' => $inquiry->message,
            'source_inquiry_id' => $inquiry->id,
            'created_by' => $userId,
            'assigned_to' => $inquiry->assigned_to ?? $userId,
        ]);
    }
}
