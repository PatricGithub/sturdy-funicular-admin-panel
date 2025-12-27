<?php

namespace WebWizr\AdminPanel\Filament\Resources\CustomerResource\Pages;

use WebWizr\AdminPanel\Filament\Resources\CustomerResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();

        return $data;
    }
}
