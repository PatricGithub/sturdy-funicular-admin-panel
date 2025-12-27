<?php

namespace WebWizr\AdminPanel\Filament\Resources\DealResource\Pages;

use WebWizr\AdminPanel\Filament\Resources\DealResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateDeal extends CreateRecord
{
    protected static string $resource = DealResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();

        return $data;
    }
}
