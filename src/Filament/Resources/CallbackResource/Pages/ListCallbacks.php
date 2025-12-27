<?php

namespace WebWizr\AdminPanel\Filament\Resources\CallbackResource\Pages;

use WebWizr\AdminPanel\Filament\Resources\CallbackResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCallbacks extends ListRecords
{
    protected static string $resource = CallbackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
