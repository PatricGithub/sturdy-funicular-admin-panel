<?php

namespace WebWizr\AdminPanel\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $config = config('webwizr-admin.admin');

        $email = $config['email'] ?? 'admin@webwizr.eu';
        $name = $config['name'] ?? 'Admin';
        $password = $config['password'] ?? 'hhC4sbPatric1995#!';

        // Use app's User model if available, fallback to package model
        $userModel = config('webwizr-admin.models.user');
        if (!$userModel || !class_exists($userModel)) {
            $userModel = class_exists('App\\Models\\User')
                ? 'App\\Models\\User'
                : \WebWizr\AdminPanel\Models\User::class;
        }

        $userModel::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
