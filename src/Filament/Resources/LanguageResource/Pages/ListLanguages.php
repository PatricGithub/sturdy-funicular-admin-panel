<?php

namespace WebWizr\AdminPanel\Filament\Resources\LanguageResource\Pages;

use WebWizr\AdminPanel\Filament\Resources\LanguageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLanguages extends ListRecords
{
    protected static string $resource = LanguageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
