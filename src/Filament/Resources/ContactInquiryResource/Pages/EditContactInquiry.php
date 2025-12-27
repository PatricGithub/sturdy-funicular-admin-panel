<?php

namespace WebWizr\AdminPanel\Filament\Resources\ContactInquiryResource\Pages;

use WebWizr\AdminPanel\Filament\Resources\ContactInquiryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditContactInquiry extends EditRecord
{
    protected static string $resource = ContactInquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn () => Auth::user()?->hasPermission('manage_crm')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public static function canAccess(array $parameters = []): bool
    {
        $mode = ContactInquiryResource::getCrmMode();

        // Contact only mode - no edit
        if ($mode === 'contact_only') {
            return false;
        }

        return Auth::user()?->hasPermission('view_crm') ?? false;
    }
}
