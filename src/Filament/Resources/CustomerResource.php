<?php

namespace WebWizr\AdminPanel\Filament\Resources;

use WebWizr\AdminPanel\Filament\Resources\CustomerResource\Pages;
use WebWizr\AdminPanel\Models\Customer;
use WebWizr\AdminPanel\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 15;

    public static function getNavigationLabel(): string
    {
        return __('admin.customers');
    }

    public static function getModelLabel(): string
    {
        return __('admin.customer');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.customers');
    }

    public static function shouldRegisterNavigation(): bool
    {
        // Only show in full CRM mode
        return config('company.crm_mode') === 'full';
    }

    public static function canAccess(): bool
    {
        $mode = config('company.crm_mode');
        if ($mode !== 'full') {
            return false;
        }

        return Auth::user()?->hasPermission('view_crm') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->hasPermission('manage_crm') ?? false;
    }

    public static function canEdit($record): bool
    {
        return Auth::user()?->hasPermission('manage_crm') ?? false;
    }

    public static function canDelete($record): bool
    {
        return Auth::user()?->hasPermission('manage_crm') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('admin.contact_details'))
                    ->schema([
                        Forms\Components\TextInput::make('company_name')
                            ->label(__('admin.company_name'))
                            ->maxLength(255),

                        Forms\Components\TextInput::make('contact_name')
                            ->label(__('admin.contact_name'))
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label(__('admin.email'))
                            ->email()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->label(__('admin.phone'))
                            ->tel()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make(__('admin.address'))
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->label(__('admin.street'))
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('postal_code')
                            ->label(__('admin.postal_code'))
                            ->maxLength(20),

                        Forms\Components\TextInput::make('city')
                            ->label(__('admin.city'))
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make(__('admin.assignment'))
                    ->schema([
                        Forms\Components\Select::make('assigned_to')
                            ->label(__('admin.assigned_to'))
                            ->options(function () {
                                return User::whereHas('permissions', function ($query) {
                                    $query->where('name', 'view_crm');
                                })->orWhere('is_super_admin', true)->pluck('name', 'id');
                            })
                            ->searchable()
                            ->placeholder(__('admin.unassigned')),

                        Forms\Components\Textarea::make('notes')
                            ->label(__('admin.notes'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('contact_name')
                    ->label(__('admin.name'))
                    ->description(fn (Customer $record): ?string => $record->company_name)
                    ->searchable(['contact_name', 'company_name'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label(__('admin.email'))
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label(__('admin.phone'))
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('city')
                    ->label(__('admin.city'))
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('deals_count')
                    ->label(__('admin.deals'))
                    ->counts('deals')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('assignedUser.name')
                    ->label(__('admin.assigned_to'))
                    ->placeholder(__('admin.unassigned'))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.date'))
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('assigned_to')
                    ->label(__('admin.assigned_to'))
                    ->options(function () {
                        return User::whereHas('permissions', function ($query) {
                            $query->where('name', 'view_crm');
                        })->orWhere('is_super_admin', true)->pluck('name', 'id');
                    }),

                Tables\Filters\Filter::make('has_deals')
                    ->label(__('admin.has_deals'))
                    ->query(fn (Builder $query): Builder => $query->has('deals')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('assign')
                        ->label(__('admin.assign'))
                        ->icon('heroicon-o-user')
                        ->form([
                            Forms\Components\Select::make('assigned_to')
                                ->label(__('admin.assigned_to'))
                                ->options(function () {
                                    return User::whereHas('permissions', function ($query) {
                                        $query->where('name', 'view_crm');
                                    })->orWhere('is_super_admin', true)->pluck('name', 'id');
                                })
                                ->required(),
                        ])
                        ->action(function ($records, array $data): void {
                            $records->each->update(['assigned_to' => $data['assigned_to']]);
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            // Will add RelationManagers for deals, callbacks, tasks later
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'view' => Pages\ViewCustomer::route('/{record}'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
