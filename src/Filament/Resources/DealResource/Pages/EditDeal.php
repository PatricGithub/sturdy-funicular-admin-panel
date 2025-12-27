<?php

namespace WebWizr\AdminPanel\Filament\Resources\DealResource\Pages;

use WebWizr\AdminPanel\Filament\Resources\DealResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeal extends EditRecord
{
    protected static string $resource = DealResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
