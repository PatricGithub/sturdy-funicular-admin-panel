<?php

namespace WebWizr\AdminPanel\Filament\Resources\LanguageResource\Pages;

use WebWizr\AdminPanel\Filament\Resources\LanguageResource;
use WebWizr\AdminPanel\Models\Language;
use Filament\Resources\Pages\CreateRecord;

class CreateLanguage extends CreateRecord
{
    protected static string $resource = LanguageResource::class;

    protected function afterCreate(): void
    {
        if ($this->record->is_default) {
            Language::where('id', '!=', $this->record->id)
                ->update(['is_default' => false]);
        }
    }
}
