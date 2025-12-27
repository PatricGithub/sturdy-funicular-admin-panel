<?php

namespace WebWizr\AdminPanel\Filament\Resources\DealResource\Pages;

use WebWizr\AdminPanel\Filament\Resources\DealResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeals extends ListRecords
{
    protected static string $resource = DealResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('kanban')
                ->label(__('admin.kanban_view'))
                ->icon('heroicon-o-view-columns')
                ->url(DealResource::getUrl('kanban')),
            Actions\CreateAction::make(),
        ];
    }
}
