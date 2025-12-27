<?php

namespace WebWizr\AdminPanel\Filament\Pages;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class EditProfile extends BaseEditProfile
{
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('admin.profile_information'))
                    ->description(__('admin.profile_information_description'))
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                    ]),

                Section::make(__('admin.update_password'))
                    ->description(__('admin.update_password_description'))
                    ->schema([
                        TextInput::make('current_password')
                            ->label(__('admin.current_password'))
                            ->password()
                            ->revealable()
                            ->required(fn ($get) => filled($get('password')))
                            ->currentPassword()
                            ->dehydrated(false),

                        TextInput::make('password')
                            ->label(__('admin.new_password'))
                            ->password()
                            ->revealable()
                            ->rule(Password::default())
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->same('password_confirmation'),

                        TextInput::make('password_confirmation')
                            ->label(__('admin.confirm_password'))
                            ->password()
                            ->revealable()
                            ->dehydrated(false),
                    ]),
            ]);
    }
}
