<?php

namespace WebWizr\AdminPanel\Filament\Pages;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class FeatureSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Feature Toggles';
    protected static ?string $title = 'Feature Einstellungen';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 96;

    protected static string $view = 'webwizr-admin::filament.pages.feature-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user && $user->isSuperAdmin();
    }

    public function mount(): void
    {
        $this->form->fill([
            'crm_mode' => config('company.crm_mode', 'full'),
            'blog_enabled' => config('company.blog_enabled', false),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('admin.crm_settings'))
                    ->description(__('admin.crm_settings_description'))
                    ->schema([
                        Select::make('crm_mode')
                            ->label(__('admin.crm_mode'))
                            ->options([
                                'full' => __('admin.crm_mode_full'),
                                'light' => __('admin.crm_mode_light'),
                                'contact_only' => __('admin.crm_mode_contact_only'),
                            ])
                            ->native(false)
                            ->required()
                            ->helperText(__('admin.crm_mode_help')),
                    ]),

                Section::make(__('admin.blog_settings'))
                    ->description(__('admin.blog_settings_description'))
                    ->schema([
                        Toggle::make('blog_enabled')
                            ->label(__('admin.blog_enabled'))
                            ->helperText(__('admin.blog_enabled_help')),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $this->updateCompanyConfig($data);

        Notification::make()
            ->title(__('admin.feature_settings_saved'))
            ->success()
            ->send();
    }

    protected function updateCompanyConfig(array $data): void
    {
        $configPath = config_path('company.php');
        $content = file_get_contents($configPath);

        // Update crm_mode
        $content = preg_replace(
            "/'crm_mode'\s*=>\s*'[^']*'/",
            "'crm_mode' => '{$data['crm_mode']}'",
            $content
        );

        // Update blog_enabled
        $blogEnabled = $data['blog_enabled'] ? 'true' : 'false';
        $content = preg_replace(
            "/'blog_enabled'\s*=>\s*(true|false)/",
            "'blog_enabled' => {$blogEnabled}",
            $content
        );

        file_put_contents($configPath, $content);

        // Clear config cache
        Artisan::call('config:clear');
    }
}
