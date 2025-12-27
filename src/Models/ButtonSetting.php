<?php

namespace WebWizr\AdminPanel\Models;

use Illuminate\Database\Eloquent\Model;

class ButtonSetting extends Model
{
    protected $fillable = [
        'type',
        'is_active',
        'value',
        'icon_color',
        'background_color',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public static function getPhone(): self
    {
        return static::firstOrCreate(
            ['type' => 'phone'],
            [
                'is_active' => false,
                'icon_color' => '#ffffff',
                'background_color' => '#22c55e',
                'sort_order' => 1,
            ]
        );
    }

    public static function getEmail(): self
    {
        return static::firstOrCreate(
            ['type' => 'email'],
            [
                'is_active' => false,
                'icon_color' => '#ffffff',
                'background_color' => '#3b82f6',
                'sort_order' => 2,
            ]
        );
    }

    public static function getAllActive(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_active', true)->orderBy('sort_order')->get();
    }
}
