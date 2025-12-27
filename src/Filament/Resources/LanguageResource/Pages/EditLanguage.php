<?php

namespace WebWizr\AdminPanel\Filament\Resources\LanguageResource\Pages;

use WebWizr\AdminPanel\Filament\Resources\LanguageResource;
use WebWizr\AdminPanel\Models\Language;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLanguage extends EditRecord
{
    protected static string $resource = LanguageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        if ($this->record->is_default) {
            Language::where('id', '!=', $this->record->id)
                ->update(['is_default' => false]);
        }
    }
}
