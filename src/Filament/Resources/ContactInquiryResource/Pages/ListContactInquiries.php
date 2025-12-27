<?php

namespace WebWizr\AdminPanel\Filament\Resources\ContactInquiryResource\Pages;

use WebWizr\AdminPanel\Filament\Resources\ContactInquiryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListContactInquiries extends ListRecords
{
    protected static string $resource = ContactInquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        $mode = ContactInquiryResource::getCrmMode();

        // Contact only mode - no tabs
        if ($mode === 'contact_only') {
            return [];
        }

        $tabs = [
            'all' => Tab::make(__('admin.contact_inquiries'))
                ->badge($this->getModel()::count()),
            'new' => Tab::make(__('admin.new_inquiries'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'new'))
                ->badge($this->getModel()::where('status', 'new')->count())
                ->badgeColor('danger'),
        ];

        if ($mode === 'full') {
            $tabs['contacted'] = Tab::make(__('admin.status_contacted'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'contacted'))
                ->badge($this->getModel()::where('status', 'contacted')->count());

            $tabs['qualified'] = Tab::make(__('admin.status_qualified'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'qualified'))
                ->badge($this->getModel()::where('status', 'qualified')->count());
        }

        if ($mode === 'light') {
            $tabs['in_progress'] = Tab::make(__('admin.status_in_progress'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'in_progress'))
                ->badge($this->getModel()::where('status', 'in_progress')->count());

            $tabs['done'] = Tab::make(__('admin.status_resolved'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'done'))
                ->badge($this->getModel()::where('status', 'done')->count())
                ->badgeColor('success');
        }

        return $tabs;
    }
}
