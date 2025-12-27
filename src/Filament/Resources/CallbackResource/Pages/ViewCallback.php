<?php

namespace WebWizr\AdminPanel\Filament\Resources\CallbackResource\Pages;

use WebWizr\AdminPanel\Filament\Resources\CallbackResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCallback extends ViewRecord
{
    protected static string $resource = CallbackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
