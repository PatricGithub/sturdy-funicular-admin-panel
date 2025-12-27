<?php

namespace WebWizr\AdminPanel\Filament\Pages;

use WebWizr\AdminPanel\Models\ButtonSetting;
use WebWizr\AdminPanel\Models\ChatSetting;
use WebWizr\AdminPanel\Models\PopupSetting;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Functionality extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $slug = 'feature-settings';
    protected static ?string $title = 'Feature Settings';
    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.functionality';

    public ?array $popupData = [];
    public ?array $phoneData = [];
    public ?array $emailData = [];
    public ?array $chatData = [];

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user && $user->isSuperAdmin();
    }

    public function mount(): void
    {
        // Popup
        $popup = PopupSetting::getInstance();
        $this->popupData = [
            'is_active' => $popup->is_active,
        ];

        // Phone Button
        $phone = ButtonSetting::getPhone();
        $this->phoneData = [
            'is_active' => $phone->is_active,
        ];

        // Email Button
        $email = ButtonSetting::getEmail();
        $this->emailData = [
            'is_active' => $email->is_active,
        ];

        // Chat
        $chat = ChatSetting::getInstance();
        $this->chatData = [
            'is_active' => $chat->is_active,
        ];
    }

    public function popupForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Scroll Popup')
                    ->description('A popup that appears after the user scrolls a certain percentage of the page.')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Enable Popup'),
                    ]),
            ])
            ->statePath('popupData');
    }

    public function phoneForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Phone Button')
                    ->description('A floating phone button for quick calls.')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Enable Phone Button'),
                    ]),
            ])
            ->statePath('phoneData');
    }

    public function emailForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Email Button')
                    ->description('A floating email button for quick contact.')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Enable Email Button'),
                    ]),
            ])
            ->statePath('emailData');
    }

    public function chatForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Chat Widget')
                    ->description('An interactive chat questionnaire widget.')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Enable Chat Widget'),
                    ]),
            ])
            ->statePath('chatData');
    }

    protected function getForms(): array
    {
        return [
            'popupForm',
            'phoneForm',
            'emailForm',
            'chatForm',
        ];
    }

    public function savePopup(): void
    {
        $data = $this->popupData;

        $popup = PopupSetting::getInstance();
        $popup->update([
            'is_active' => $data['is_active'] ?? false,
        ]);

        Notification::make()->title('Popup settings saved!')->success()->send();
    }

    public function savePhone(): void
    {
        $data = $this->phoneData;

        ButtonSetting::where('type', 'phone')->update([
            'is_active' => $data['is_active'] ?? false,
        ]);

        Notification::make()->title('Phone button saved!')->success()->send();
    }

    public function saveEmail(): void
    {
        $data = $this->emailData;

        ButtonSetting::where('type', 'email')->update([
            'is_active' => $data['is_active'] ?? false,
        ]);

        Notification::make()->title('Email button saved!')->success()->send();
    }

    public function saveChat(): void
    {
        $data = $this->chatData;

        $chat = ChatSetting::getInstance();
        $chat->update([
            'is_active' => $data['is_active'] ?? false,
        ]);

        Notification::make()->title('Chat settings saved!')->success()->send();
    }
}
