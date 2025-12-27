<?php

namespace WebWizr\AdminPanel\Filament\Resources\CallbackResource\Pages;

use WebWizr\AdminPanel\Filament\Resources\CallbackResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCallback extends EditRecord
{
    protected static string $resource = CallbackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
