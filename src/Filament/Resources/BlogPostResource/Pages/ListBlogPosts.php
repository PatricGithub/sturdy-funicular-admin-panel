<?php

namespace WebWizr\AdminPanel\Filament\Resources\BlogPostResource\Pages;

use WebWizr\AdminPanel\Filament\Resources\BlogPostResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListBlogPosts extends ListRecords
{
    protected static string $resource = BlogPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => Auth::user()?->hasPermission('manage_blog')),
        ];
    }
}
