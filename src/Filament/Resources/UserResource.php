<?php

namespace WebWizr\AdminPanel\Filament\Resources;

use WebWizr\AdminPanel\Filament\Resources\UserResource\Pages;
use WebWizr\AdminPanel\Models\Permission;
use WebWizr\AdminPanel\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 95;

    public static function getNavigationLabel(): string
    {
        return __('admin.users');
    }

    public static function getModelLabel(): string
    {
        return __('admin.user');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.users');
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->isSuperAdmin() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('admin.user'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('admin.name'))
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label(__('admin.email'))
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('password')
                            ->label(__('admin.password'))
                            ->password()
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->helperText(__('admin.password_help'))
                            ->maxLength(255),

                        Forms\Components\Toggle::make('is_super_admin')
                            ->label(__('admin.super_admin'))
                            ->helperText(__('admin.super_admin_note'))
                            ->disabled(fn (?User $record): bool => $record?->id === Auth::id())
                            ->dehydrated(fn (?User $record): bool => $record?->id !== Auth::id()),
                    ])
                    ->columns(2),

                Forms\Components\Section::make(__('admin.permissions'))
                    ->schema([
                        Forms\Components\Placeholder::make('super_admin_info')
                            ->content(__('admin.super_admin_note'))
                            ->visible(fn (Forms\Get $get): bool => $get('is_super_admin')),

                        Forms\Components\CheckboxList::make('permissions')
                            ->label('')
                            ->relationship('permissions', 'label')
                            ->options(function () {
                                return Permission::query()
                                    ->get()
                                    ->groupBy('group')
                                    ->mapWithKeys(function ($permissions, $group) {
                                        return $permissions->pluck('label', 'id')->toArray();
                                    });
                            })
                            ->columns(3)
                            ->gridDirection('row')
                            ->bulkToggleable()
                            ->visible(fn (Forms\Get $get): bool => !$get('is_super_admin'))
                            ->descriptions(function () {
                                $descriptions = [];
                                $permissions = Permission::all();
                                foreach ($permissions as $permission) {
                                    $descriptions[$permission->id] = __('admin.' . $permission->group . '_permissions');
                                }
                                return $descriptions;
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label(__('admin.email'))
                    ->searchable()
                    ->copyable(),

                Tables\Columns\IconColumn::make('is_super_admin')
                    ->label(__('admin.super_admin'))
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-user')
                    ->trueColor('warning')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('permissions_count')
                    ->label(__('admin.permissions'))
                    ->counts('permissions')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.date'))
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('admin.updated'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_super_admin')
                    ->label(__('admin.super_admin')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            // Prevent deleting the current user or super admins
                            $records = $records->filter(function ($record) {
                                return $record->id !== Auth::id() && !$record->is_super_admin;
                            });
                        }),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }
}
