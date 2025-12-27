<?php

namespace WebWizr\AdminPanel\Filament\Resources\PipelineStageResource\Pages;

use WebWizr\AdminPanel\Filament\Resources\PipelineStageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPipelineStages extends ListRecords
{
    protected static string $resource = PipelineStageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
