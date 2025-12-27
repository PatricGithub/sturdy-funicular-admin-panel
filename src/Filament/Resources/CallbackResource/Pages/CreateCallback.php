<?php

namespace WebWizr\AdminPanel\Filament\Resources\CallbackResource\Pages;

use WebWizr\AdminPanel\Filament\Resources\CallbackResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateCallback extends CreateRecord
{
    protected static string $resource = CallbackResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();

        return $data;
    }
}
