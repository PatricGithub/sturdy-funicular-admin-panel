<?php

namespace WebWizr\AdminPanel\Filament\Resources\StagingCommentResource\Pages;

use WebWizr\AdminPanel\Filament\Resources\StagingCommentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditStagingComment extends EditRecord
{
    protected static string $resource = StagingCommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Set resolved_by and resolved_at if status is being changed to resolved
        if ($data['status'] === 'resolved' && $this->record->status !== 'resolved') {
            $data['resolved_by'] = Auth::id();
            $data['resolved_at'] = now();
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
