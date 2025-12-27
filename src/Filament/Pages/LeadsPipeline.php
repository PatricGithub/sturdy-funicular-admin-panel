<?php

namespace WebWizr\AdminPanel\Filament\Pages;

use WebWizr\AdminPanel\Models\ContactInquiry;
use WebWizr\AdminPanel\Models\CrmActivity;
use WebWizr\AdminPanel\Models\PipelineStage;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class LeadsPipeline extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-view-columns';

    protected static string $view = 'webwizr-admin::filament.pages.leads-pipeline';

    protected static ?int $navigationSort = -2;

    protected static ?string $slug = '';

    protected static bool $shouldRegisterNavigation = true;

    public Collection $statuses;

    public static function getNavigationLabel(): string
    {
        return __('admin.leads_pipeline');
    }

    public function getTitle(): string
    {
        return __('admin.leads_pipeline');
    }

    public function mount(): void
    {
        $this->loadLeads();
    }

    protected function loadLeads(): void
    {
        // Load stages from PipelineStage model
        $stages = PipelineStage::active()->ordered()->get();

        $this->statuses = $stages->map(function ($stage) {
            return (object) [
                'key' => $stage->slug,
                'label' => $stage->name,
                'color' => $stage->color ?? '#6b7280',
                'inquiries' => ContactInquiry::where('status', $stage->slug)
                    ->with('assignedUser')
                    ->orderBy('created_at', 'desc')
                    ->get(),
            ];
        });
    }

    public function moveInquiry(int $inquiryId, string $newStatus): void
    {
        $inquiry = ContactInquiry::find($inquiryId);

        if (! $inquiry) {
            return;
        }

        $oldStatus = $inquiry->status;

        if ($oldStatus !== $newStatus) {
            $inquiry->update(['status' => $newStatus]);

            // Log the status change
            CrmActivity::logStatusChange($inquiry, $oldStatus, $newStatus);
        }

        $this->loadLeads();
    }

    public function trackPhoneClick(int $inquiryId): void
    {
        $inquiry = ContactInquiry::find($inquiryId);

        if ($inquiry) {
            CrmActivity::logPhoneCall($inquiry);
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        return config('company.crm_mode', 'full') !== 'contact_only';
    }
}
