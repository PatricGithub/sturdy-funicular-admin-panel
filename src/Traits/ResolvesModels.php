<?php

namespace WebWizr\AdminPanel\Traits;

trait ResolvesModels
{
    protected static function resolveModel(string $key, string $default): string
    {
        $configured = config("webwizr-admin.models.{$key}");

        if ($configured && class_exists($configured)) {
            return $configured;
        }

        // Check if app model exists
        $appModel = 'App\\Models\\' . class_basename($default);
        if (class_exists($appModel)) {
            return $appModel;
        }

        return $default;
    }

    protected static function getUserModel(): string
    {
        return static::resolveModel('user', \WebWizr\AdminPanel\Models\User::class);
    }

    protected static function getLanguageModel(): string
    {
        return static::resolveModel('language', \WebWizr\AdminPanel\Models\Language::class);
    }

    protected static function getCustomerModel(): string
    {
        return static::resolveModel('customer', \WebWizr\AdminPanel\Models\Customer::class);
    }

    protected static function getDealModel(): string
    {
        return static::resolveModel('deal', \WebWizr\AdminPanel\Models\Deal::class);
    }
}
