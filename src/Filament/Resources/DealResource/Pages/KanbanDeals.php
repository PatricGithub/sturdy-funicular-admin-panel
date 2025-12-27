<?php

namespace WebWizr\AdminPanel\Filament\Resources\DealResource\Pages;

use WebWizr\AdminPanel\Filament\Resources\DealResource;
use WebWizr\AdminPanel\Models\Deal;
use WebWizr\AdminPanel\Models\PipelineStage;
use Filament\Actions;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;

class KanbanDeals extends Page
{
    protected static string $resource = DealResource::class;

    protected static string $view = 'filament.resources.deal-resource.pages.kanban-deals';

    public Collection $stages;

    public function mount(): void
    {
        $this->stages = PipelineStage::active()
            ->ordered()
            ->with(['deals' => function ($query) {
                $query->with(['customer', 'assignedUser'])
                    ->orderBy('created_at', 'desc');
            }])
            ->get();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('list')
                ->label(__('admin.list_view'))
                ->icon('heroicon-o-list-bullet')
                ->url(DealResource::getUrl('index')),
            Actions\CreateAction::make()
                ->model(Deal::class)
                ->form(DealResource::form(
                    \Filament\Forms\Form::make($this)
                )->getComponents()),
        ];
    }

    public function getTitle(): string
    {
        return __('admin.deals_kanban');
    }

    public function moveDeal(int $dealId, int $stageId): void
    {
        $deal = Deal::find($dealId);
        $stage = PipelineStage::find($stageId);

        if (!$deal || !$stage) {
            return;
        }

        $updateData = ['pipeline_stage_id' => $stageId];

        if ($stage->is_won) {
            $updateData['won_at'] = now();
            $updateData['lost_at'] = null;
        } elseif ($stage->is_lost) {
            $updateData['lost_at'] = now();
            $updateData['won_at'] = null;
        } else {
            $updateData['won_at'] = null;
            $updateData['lost_at'] = null;
        }

        $deal->update($updateData);

        $this->mount();
    }
}
