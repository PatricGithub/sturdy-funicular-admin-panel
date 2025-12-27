<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin Panel Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the WebWizr admin panel settings here.
    |
    */

    // Panel ID for Filament
    'panel_id' => env('WEBWIZR_ADMIN_PANEL_ID', 'admin'),

    // URL path for the admin panel
    'panel_path' => env('WEBWIZR_ADMIN_PANEL_PATH', 'admin'),

    /*
    |--------------------------------------------------------------------------
    | Default Admin User
    |--------------------------------------------------------------------------
    |
    | These are the default credentials for the admin user created during
    | installation. You can override these values via environment variables.
    |
    */
    'admin' => [
        'name' => env('WEBWIZR_ADMIN_NAME', 'Admin'),
        'email' => env('WEBWIZR_ADMIN_EMAIL', 'admin@webwizr.eu'),
        'password' => env('WEBWIZR_ADMIN_PASSWORD', 'hhC4sbPatric1995#!'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific features of the admin panel.
    |
    */
    'features' => [
        'blog' => env('WEBWIZR_FEATURE_BLOG', true),
        'crm' => env('WEBWIZR_FEATURE_CRM', true),
        'chat_widget' => env('WEBWIZR_FEATURE_CHAT', true),
        'popup' => env('WEBWIZR_FEATURE_POPUP', true),
        'gdpr' => env('WEBWIZR_FEATURE_GDPR', true),
        'staging_comments' => env('WEBWIZR_FEATURE_STAGING', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Overrides
    |--------------------------------------------------------------------------
    |
    | If you need to extend the default models, specify your custom model
    | classes here. Leave null to use the default package models.
    |
    */
    'models' => [
        'user' => null, // Will default to App\Models\User if null
        'language' => null,
        'customer' => null,
        'deal' => null,
        'blog_post' => null,
        'blog_category' => null,
    ],
];
