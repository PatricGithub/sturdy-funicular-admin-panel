<?php

namespace WebWizr\AdminPanel\Filament\Resources;

use WebWizr\AdminPanel\Filament\Resources\DealResource\Pages;
use WebWizr\AdminPanel\Models\Customer;
use WebWizr\AdminPanel\Models\Deal;
use WebWizr\AdminPanel\Models\PipelineStage;
use WebWizr\AdminPanel\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DealResource extends Resource
{
    protected static ?string $model = Deal::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-euro';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 20;

    public static function getNavigationLabel(): string
    {
        return __('admin.deals');
    }

    public static function getModelLabel(): string
    {
        return __('admin.deal');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.deals');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::open()->count() ?: null;
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
                Forms\Components\Section::make(__('admin.deal_details'))
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label(__('admin.title'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('customer_id')
                            ->label(__('admin.customer'))
                            ->relationship('customer', 'contact_name')
                            ->getOptionLabelFromRecordUsing(fn (Customer $record) => $record->display_name)
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('contact_name')
                                    ->label(__('admin.contact_name'))
                                    ->required(),
                                Forms\Components\TextInput::make('email')
                                    ->label(__('admin.email'))
                                    ->email(),
                                Forms\Components\TextInput::make('phone')
                                    ->label(__('admin.phone')),
                            ]),

                        Forms\Components\Select::make('pipeline_stage_id')
                            ->label(__('admin.stage'))
                            ->options(PipelineStage::getSelectOptions())
                            ->required()
                            ->default(fn () => PipelineStage::active()->ordered()->first()?->id),

                        Forms\Components\TextInput::make('value')
                            ->label(__('admin.value'))
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01),

                        Forms\Components\DatePicker::make('expected_close_date')
                            ->label(__('admin.expected_close_date')),
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

                Forms\Components\Section::make(__('admin.closing'))
                    ->schema([
                        Forms\Components\Placeholder::make('won_at_display')
                            ->label(__('admin.won_at'))
                            ->content(fn (?Deal $record) => $record?->won_at?->format('d.m.Y H:i') ?? '-')
                            ->visible(fn (?Deal $record) => $record?->isWon()),

                        Forms\Components\Placeholder::make('lost_at_display')
                            ->label(__('admin.lost_at'))
                            ->content(fn (?Deal $record) => $record?->lost_at?->format('d.m.Y H:i') ?? '-')
                            ->visible(fn (?Deal $record) => $record?->isLost()),

                        Forms\Components\TextInput::make('lost_reason')
                            ->label(__('admin.lost_reason'))
                            ->visible(fn (?Deal $record) => $record?->isLost())
                            ->disabled(),
                    ])
                    ->visible(fn (?Deal $record) => $record?->isWon() || $record?->isLost())
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('admin.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('customer.contact_name')
                    ->label(__('admin.customer'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('stage.name')
                    ->label(__('admin.stage'))
                    ->badge()
                    ->color(fn (Deal $record): string => $record->stage?->color ?? 'gray'),

                Tables\Columns\TextColumn::make('value')
                    ->label(__('admin.value'))
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('expected_close_date')
                    ->label(__('admin.expected_close'))
                    ->date('d.m.Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('assignedUser.name')
                    ->label(__('admin.assigned_to'))
                    ->placeholder(__('admin.unassigned'))
                    ->toggleable(),

                Tables\Columns\IconColumn::make('status')
                    ->label(__('admin.status'))
                    ->icon(fn (Deal $record): string => match (true) {
                        $record->isWon() => 'heroicon-o-check-circle',
                        $record->isLost() => 'heroicon-o-x-circle',
                        default => 'heroicon-o-clock',
                    })
                    ->color(fn (Deal $record): string => match (true) {
                        $record->isWon() => 'success',
                        $record->isLost() => 'danger',
                        default => 'warning',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.date'))
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('pipeline_stage_id')
                    ->label(__('admin.stage'))
                    ->options(PipelineStage::getSelectOptions()),

                Tables\Filters\SelectFilter::make('assigned_to')
                    ->label(__('admin.assigned_to'))
                    ->options(function () {
                        return User::whereHas('permissions', function ($query) {
                            $query->where('name', 'view_crm');
                        })->orWhere('is_super_admin', true)->pluck('name', 'id');
                    }),

                Tables\Filters\TernaryFilter::make('status')
                    ->label(__('admin.status'))
                    ->placeholder(__('admin.all'))
                    ->trueLabel(__('admin.won'))
                    ->falseLabel(__('admin.lost'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('won_at'),
                        false: fn (Builder $query) => $query->whereNotNull('lost_at'),
                        blank: fn (Builder $query) => $query,
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('move_stage')
                    ->label(__('admin.move'))
                    ->icon('heroicon-o-arrows-right-left')
                    ->form([
                        Forms\Components\Select::make('pipeline_stage_id')
                            ->label(__('admin.stage'))
                            ->options(PipelineStage::getSelectOptions())
                            ->required(),
                    ])
                    ->action(function (Deal $record, array $data): void {
                        $stage = PipelineStage::find($data['pipeline_stage_id']);

                        $updateData = ['pipeline_stage_id' => $data['pipeline_stage_id']];

                        if ($stage?->is_won) {
                            $updateData['won_at'] = now();
                            $updateData['lost_at'] = null;
                        } elseif ($stage?->is_lost) {
                            $updateData['lost_at'] = now();
                            $updateData['won_at'] = null;
                        } else {
                            $updateData['won_at'] = null;
                            $updateData['lost_at'] = null;
                        }

                        $record->update($updateData);
                    })
                    ->visible(fn (Deal $record) => $record->isOpen()),

                Tables\Actions\Action::make('mark_won')
                    ->label(__('admin.mark_won'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Deal $record) => $record->markAsWon())
                    ->visible(fn (Deal $record) => $record->isOpen()),

                Tables\Actions\Action::make('mark_lost')
                    ->label(__('admin.mark_lost'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\TextInput::make('lost_reason')
                            ->label(__('admin.lost_reason')),
                    ])
                    ->action(fn (Deal $record, array $data) => $record->markAsLost($data['lost_reason'] ?? null))
                    ->visible(fn (Deal $record) => $record->isOpen()),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('change_stage')
                        ->label(__('admin.change_stage'))
                        ->icon('heroicon-o-arrows-right-left')
                        ->form([
                            Forms\Components\Select::make('pipeline_stage_id')
                                ->label(__('admin.stage'))
                                ->options(PipelineStage::getSelectOptions())
                                ->required(),
                        ])
                        ->action(function ($records, array $data): void {
                            $records->each->update(['pipeline_stage_id' => $data['pipeline_stage_id']]);
                        }),

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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeals::route('/'),
            'kanban' => Pages\KanbanDeals::route('/kanban'),
            'create' => Pages\CreateDeal::route('/create'),
            'view' => Pages\ViewDeal::route('/{record}'),
            'edit' => Pages\EditDeal::route('/{record}/edit'),
        ];
    }
}
