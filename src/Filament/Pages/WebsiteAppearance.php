<?php

namespace WebWizr\AdminPanel\Filament\Pages;

use Filament\Forms\Components\ColorPicker;
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

class WebsiteAppearance extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';
    protected static ?string $navigationLabel = 'Appearance';
    protected static ?string $title = 'Website Appearance';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 95;

    protected static string $view = 'filament.pages.website-appearance';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user && $user->isSuperAdmin();
    }

    public function mount(): void
    {
        $this->form->fill([
            'theme' => config('company.theme_class', 'theme-trust-blue'),
            'fonts' => config('company.font_class', 'fonts-default'),
            'animation_speed' => config('company.animation_class', 'animation-normal'),
            'scroll_parallax' => config('company.scroll_parallax', true),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('admin.color_theme'))
                    ->description(__('admin.color_theme_description'))
                    ->schema([
                        Select::make('theme')
                            ->label(__('admin.select_theme'))
                            ->options([
                                'theme-trust-blue' => 'Trust Blue - Corporate, Professional',
                                'theme-pure-white' => 'Pure White - Minimal, Clean',
                                'theme-elegant-black' => 'Elegant Black - Luxury, Premium',
                                'theme-neutral-gray' => 'Neutral Gray - Professional, Versatile',
                                'theme-passion-red' => 'Passion Red - Energy, Bold',
                                'theme-growth-green' => 'Growth Green - Nature, Health',
                                'theme-vibrant-orange' => 'Vibrant Orange - Creative, Energetic',
                                'theme-sunny-yellow' => 'Sunny Yellow - Optimistic, Warm',
                                'theme-royal-purple' => 'Royal Purple - Luxury, Creative',
                                'theme-modern-teal' => 'Modern Teal - Tech, Innovation',
                                'theme-soft-pink' => 'Soft Pink - Approachable, Calm',
                                'theme-sky-blue' => 'Sky Blue - Calm, Trustworthy',
                                'theme-navy-authority' => 'Navy Authority - Corporate, Traditional',
                                'theme-earth-brown' => 'Earth Brown - Reliable, Natural',
                                'theme-gold-luxury' => 'Gold Luxury - Premium, Prestigious',
                            ])
                            ->native(false)
                            ->searchable()
                            ->required(),
                    ]),

                Section::make(__('admin.typography'))
                    ->description(__('admin.typography_description'))
                    ->schema([
                        Select::make('fonts')
                            ->label(__('admin.font_combination'))
                            ->options([
                                'fonts-default' => 'Blauer Nue - Professional, Clean (Default)',
                                'fonts-modern' => 'Blauer Nue - Clean, Contemporary',
                                'fonts-tech' => 'Just Sans + Nord - Geometric, Minimal',
                                'fonts-elegant' => 'Surgena + Aloevera - Refined, Sophisticated',
                                'fonts-friendly' => 'Carla Sans - Approachable, Warm',
                                'fonts-bold' => 'Blauer Nue + After - Strong, Impactful',
                                'fonts-minimal' => 'Just Sans - Simple, Contemporary',
                                'fonts-retro' => 'Blauer Nue + Behind The Nineties - Nostalgic, Fun',
                                'fonts-creative' => 'Carla Sans + Glitz - Classic, Artistic (Recommended)',
                            ])
                            ->native(false)
                            ->searchable()
                            ->required(),
                    ]),

                Section::make(__('admin.animations'))
                    ->description(__('admin.animations_description'))
                    ->schema([
                        Select::make('animation_speed')
                            ->label(__('admin.animation_speed'))
                            ->options([
                                'animation-none' => 'None - No animations',
                                'animation-slow' => 'Slow - Subtle, relaxed',
                                'animation-normal' => 'Normal - Balanced',
                                'animation-fast' => 'Fast - Snappy, energetic',
                            ])
                            ->native(false)
                            ->required(),
                        Toggle::make('scroll_parallax')
                            ->label(__('admin.scroll_parallax'))
                            ->helperText(__('admin.scroll_parallax_description'))
                            ->default(true),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $this->updateCompanyConfig($data);

        Notification::make()
            ->title(__('admin.appearance_saved'))
            ->success()
            ->send();
    }

    protected function updateCompanyConfig(array $data): void
    {
        $configPath = config_path('company.php');
        $content = file_get_contents($configPath);

        // Update theme_class
        $content = preg_replace(
            "/'theme_class'\s*=>\s*'[^']*'/",
            "'theme_class' => '{$data['theme']}'",
            $content
        );

        // Update font_class
        $content = preg_replace(
            "/'font_class'\s*=>\s*'[^']*'/",
            "'font_class' => '{$data['fonts']}'",
            $content
        );

        // Update animation_class
        $content = preg_replace(
            "/'animation_class'\s*=>\s*'[^']*'/",
            "'animation_class' => '{$data['animation_speed']}'",
            $content
        );

        // Update scroll_parallax
        $scrollParallax = $data['scroll_parallax'] ? 'true' : 'false';
        $content = preg_replace(
            "/'scroll_parallax'\s*=>\s*(true|false)/",
            "'scroll_parallax' => {$scrollParallax}",
            $content
        );

        file_put_contents($configPath, $content);

        // Clear config cache
        Artisan::call('config:clear');
    }
}
