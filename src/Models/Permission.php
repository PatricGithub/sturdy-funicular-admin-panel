<?php

namespace WebWizr\AdminPanel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'label',
        'group',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_permissions');
    }

    /**
     * Get permissions grouped by their group field
     */
    public static function getGrouped(): array
    {
        return static::all()
            ->groupBy('group')
            ->map(fn ($permissions) => $permissions->pluck('label', 'id'))
            ->toArray();
    }

    /**
     * Default permissions to seed
     */
    public static function getDefaultPermissions(): array
    {
        return [
            ['name' => 'view_crm', 'label' => 'CRM anzeigen', 'group' => 'crm'],
            ['name' => 'manage_crm', 'label' => 'CRM verwalten', 'group' => 'crm'],
            ['name' => 'view_blog', 'label' => 'Blog anzeigen', 'group' => 'blog'],
            ['name' => 'manage_blog', 'label' => 'Blog verwalten', 'group' => 'blog'],
            ['name' => 'view_services', 'label' => 'Leistungen anzeigen', 'group' => 'content'],
            ['name' => 'manage_services', 'label' => 'Leistungen verwalten', 'group' => 'content'],
        ];
    }
}
