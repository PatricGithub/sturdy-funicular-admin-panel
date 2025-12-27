<?php

namespace WebWizr\AdminPanel\Filament\Resources\BlogCategoryResource\Pages;

use WebWizr\AdminPanel\Filament\Resources\BlogCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListBlogCategories extends ListRecords
{
    protected static string $resource = BlogCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => Auth::user()?->hasPermission('manage_blog')),
        ];
    }
}
