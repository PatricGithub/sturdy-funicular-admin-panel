<?php

namespace WebWizr\AdminPanel\Filament\Resources\BlogPostResource\Pages;

use WebWizr\AdminPanel\Filament\Resources\BlogPostResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateBlogPost extends CreateRecord
{
    protected static string $resource = BlogPostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['author_id'] = Auth::id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public static function canAccess(array $parameters = []): bool
    {
        return Auth::user()?->hasPermission('manage_blog') ?? false;
    }
}
