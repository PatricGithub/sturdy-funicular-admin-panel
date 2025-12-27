<?php

namespace WebWizr\AdminPanel\Filament\Resources\StagingCommentResource\Pages;

use WebWizr\AdminPanel\Filament\Resources\StagingCommentResource;
use Filament\Resources\Pages\ListRecords;

class ListStagingComments extends ListRecords
{
    protected static string $resource = StagingCommentResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
