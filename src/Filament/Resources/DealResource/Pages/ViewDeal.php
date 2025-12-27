<?php

namespace WebWizr\AdminPanel\Filament\Resources\DealResource\Pages;

use WebWizr\AdminPanel\Filament\Resources\DealResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDeal extends ViewRecord
{
    protected static string $resource = DealResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
