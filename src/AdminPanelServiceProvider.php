<?php

namespace WebWizr\AdminPanel;

use Filament\Panel;
use Filament\Support\Colors\Color;
use Illuminate\Support\ServiceProvider;
use WebWizr\AdminPanel\Commands\InstallCommand;

class AdminPanelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/webwizr-admin.php',
            'webwizr-admin'
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/webwizr-admin.php' => config_path('webwizr-admin.php'),
            ], 'webwizr-admin-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'webwizr-admin-migrations');

            $this->commands([
                InstallCommand::class,
            ]);
        }
    }

    public static function configurePanel(Panel $panel): Panel
    {
        $config = config('webwizr-admin');

        return $panel
            ->id($config['panel_id'] ?? 'admin')
            ->path($config['panel_path'] ?? 'admin')
            ->login()
            ->profile(\WebWizr\AdminPanel\Filament\Pages\EditProfile::class)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(
                in: __DIR__ . '/Filament/Resources',
                for: 'WebWizr\\AdminPanel\\Filament\\Resources'
            )
            ->discoverPages(
                in: __DIR__ . '/Filament/Pages',
                for: 'WebWizr\\AdminPanel\\Filament\\Pages'
            )
            ->discoverWidgets(
                in: __DIR__ . '/Filament/Widgets',
                for: 'WebWizr\\AdminPanel\\Filament\\Widgets'
            );
    }

    public static function getResources(): array
    {
        return [
            \WebWizr\AdminPanel\Filament\Resources\UserResource::class,
            \WebWizr\AdminPanel\Filament\Resources\LanguageResource::class,
            \WebWizr\AdminPanel\Filament\Resources\BlogCategoryResource::class,
            \WebWizr\AdminPanel\Filament\Resources\BlogPostResource::class,
            \WebWizr\AdminPanel\Filament\Resources\ContactInquiryResource::class,
            \WebWizr\AdminPanel\Filament\Resources\StagingCommentResource::class,
            \WebWizr\AdminPanel\Filament\Resources\CustomerResource::class,
            \WebWizr\AdminPanel\Filament\Resources\PipelineStageResource::class,
            \WebWizr\AdminPanel\Filament\Resources\DealResource::class,
            \WebWizr\AdminPanel\Filament\Resources\CallbackResource::class,
            \WebWizr\AdminPanel\Filament\Resources\TaskResource::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            \WebWizr\AdminPanel\Filament\Pages\Functionality::class,
            \WebWizr\AdminPanel\Filament\Pages\FeatureSettings::class,
            \WebWizr\AdminPanel\Filament\Pages\WebsiteAppearance::class,
            \WebWizr\AdminPanel\Filament\Pages\LeadsPipeline::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            \WebWizr\AdminPanel\Filament\Widgets\PersonalHotlistWidget::class,
        ];
    }
}
