<?php

namespace WebWizr\AdminPanel\Filament\Resources\PipelineStageResource\Pages;

use WebWizr\AdminPanel\Filament\Resources\PipelineStageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPipelineStage extends EditRecord
{
    protected static string $resource = PipelineStageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
