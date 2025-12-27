<?php

namespace WebWizr\AdminPanel\Filament\Resources\BlogCategoryResource\Pages;

use WebWizr\AdminPanel\Filament\Resources\BlogCategoryResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateBlogCategory extends CreateRecord
{
    protected static string $resource = BlogCategoryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public static function canAccess(array $parameters = []): bool
    {
        return Auth::user()?->hasPermission('manage_blog') ?? false;
    }
}
