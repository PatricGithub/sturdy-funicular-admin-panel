<?php

namespace WebWizr\AdminPanel\Filament\Resources\UserResource\Pages;

use WebWizr\AdminPanel\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
