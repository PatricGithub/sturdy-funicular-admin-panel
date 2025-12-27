<?php

namespace WebWizr\AdminPanel\Filament\Resources;

use WebWizr\AdminPanel\Filament\Resources\CallbackResource\Pages;
use WebWizr\AdminPanel\Models\Callback;
use WebWizr\AdminPanel\Models\ContactInquiry;
use WebWizr\AdminPanel\Models\Customer;
use WebWizr\AdminPanel\Models\Deal;
use WebWizr\AdminPanel\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CallbackResource extends Resource
{
    protected static ?string $model = Callback::class;

    protected static ?string $navigationIcon = 'heroicon-o-phone-arrow-up-right';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 30;

    public static function getNavigationLabel(): string
    {
        return __('admin.callbacks');
    }

    public static function getModelLabel(): string
    {
        return __('admin.callback');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.callbacks');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::open()->where(function ($query) {
            $query->where('scheduled_at', '<', now())
                  ->orWhereDate('scheduled_at', today());
        })->count();

        return $count ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $overdueCount = static::getModel()::overdue()->count();
        return $overdueCount > 0 ? 'danger' : 'warning';
    }

    public static function shouldRegisterNavigation(): bool
    {
        $mode = config('company.crm_mode');
        return in_array($mode, ['full', 'light']);
    }

    public static function canAccess(): bool
    {
        $mode = config('company.crm_mode');
        if (!in_array($mode, ['full', 'light'])) {
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
        $mode = config('company.crm_mode');

        $schema = [
            Forms\Components\Section::make(__('admin.callback_details'))
                ->schema([
                    Forms\Components\DateTimePicker::make('scheduled_at')
                        ->label(__('admin.scheduled_at'))
                        ->required()
                        ->default(now()->addHour()->startOfHour())
                        ->seconds(false),

                    Forms\Components\Select::make('priority')
                        ->label(__('admin.priority'))
                        ->options(Callback::getPriorityOptions())
                        ->default('normal')
                        ->required(),

                    Forms\Components\Select::make('assigned_to')
                        ->label(__('admin.assigned_to'))
                        ->options(function () {
                            return User::whereHas('permissions', function ($query) {
                                $query->where('name', 'view_crm');
                            })->orWhere('is_super_admin', true)->pluck('name', 'id');
                        })
                        ->searchable()
                        ->default(Auth::id()),

                    Forms\Components\Select::make('status')
                        ->label(__('admin.status'))
                        ->options(Callback::getStatusOptions())
                        ->default('open')
                        ->required(),
                ])
                ->columns(2),
        ];

        // Only show polymorphic relation in full mode
        if ($mode === 'full') {
            $schema[] = Forms\Components\Section::make(__('admin.link_to'))
                ->schema([
                    Forms\Components\MorphToSelect::make('callable')
                        ->label(__('admin.link_to'))
                        ->types([
                            Forms\Components\MorphToSelect\Type::make(Customer::class)
                                ->titleAttribute('contact_name')
                                ->label(__('admin.customer')),
                            Forms\Components\MorphToSelect\Type::make(ContactInquiry::class)
                                ->titleAttribute('name')
                                ->label(__('admin.contact_inquiry')),
                            Forms\Components\MorphToSelect\Type::make(Deal::class)
                                ->titleAttribute('title')
                                ->label(__('admin.deal')),
                        ])
                        ->searchable()
                        ->preload(),
                ]);
        }

        $schema[] = Forms\Components\Section::make(__('admin.notes'))
            ->schema([
                Forms\Components\Textarea::make('notes')
                    ->label(__('admin.notes'))
                    ->rows(3)
                    ->columnSpanFull(),
            ]);

        return $form->schema($schema);
    }

    public static function table(Table $table): Table
    {
        $mode = config('company.crm_mode');

        $columns = [
            Tables\Columns\TextColumn::make('scheduled_at')
                ->label(__('admin.scheduled_at'))
                ->dateTime('d.m.Y H:i')
                ->sortable()
                ->color(fn (Callback $record): string => match (true) {
                    $record->isOverdue() => 'danger',
                    $record->isToday() => 'warning',
                    default => 'gray',
                })
                ->weight(fn (Callback $record): string => $record->isOverdue() || $record->isToday() ? 'bold' : 'normal')
                ->icon(fn (Callback $record): ?string => $record->isOverdue() ? 'heroicon-o-exclamation-triangle' : null),

            Tables\Columns\TextColumn::make('priority')
                ->label(__('admin.priority'))
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'high' => 'danger',
                    default => 'gray',
                })
                ->formatStateUsing(fn (string $state): string => Callback::getPriorityOptions()[$state] ?? $state),

            Tables\Columns\TextColumn::make('status')
                ->label(__('admin.status'))
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'open' => 'warning',
                    'completed' => 'success',
                    'cancelled' => 'gray',
                    default => 'gray',
                })
                ->formatStateUsing(fn (string $state): string => Callback::getStatusOptions()[$state] ?? $state),
        ];

        // Show callable column only in full mode
        if ($mode === 'full') {
            $columns[] = Tables\Columns\TextColumn::make('callable_name')
                ->label(__('admin.linked_to'))
                ->placeholder('-')
                ->limit(25);
        }

        $columns[] = Tables\Columns\TextColumn::make('assignedUser.name')
            ->label(__('admin.assigned_to'))
            ->placeholder(__('admin.unassigned'))
            ->toggleable();

        $columns[] = Tables\Columns\TextColumn::make('notes')
            ->label(__('admin.notes'))
            ->limit(30)
            ->toggleable(isToggledHiddenByDefault: true);

        $columns[] = Tables\Columns\TextColumn::make('created_at')
            ->label(__('admin.created'))
            ->dateTime('d.m.Y')
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);

        return $table
            ->columns($columns)
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('admin.status'))
                    ->options(Callback::getStatusOptions())
                    ->default('open'),

                Tables\Filters\SelectFilter::make('priority')
                    ->label(__('admin.priority'))
                    ->options(Callback::getPriorityOptions()),

                Tables\Filters\SelectFilter::make('assigned_to')
                    ->label(__('admin.assigned_to'))
                    ->options(function () {
                        return User::whereHas('permissions', function ($query) {
                            $query->where('name', 'view_crm');
                        })->orWhere('is_super_admin', true)->pluck('name', 'id');
                    }),

                Tables\Filters\Filter::make('overdue')
                    ->label(__('admin.overdue'))
                    ->query(fn (Builder $query): Builder => $query->overdue())
                    ->toggle(),

                Tables\Filters\Filter::make('today')
                    ->label(__('admin.today'))
                    ->query(fn (Builder $query): Builder => $query->today())
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\Action::make('complete')
                    ->label(__('admin.complete'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Callback $record) => $record->markAsCompleted())
                    ->visible(fn (Callback $record) => $record->status === 'open'),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('mark_completed')
                        ->label(__('admin.mark_completed'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records): void {
                            $records->each->markAsCompleted();
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
            ->defaultSort(function (Builder $query): Builder {
                return $query->prioritySorted();
            })
            ->poll('60s');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCallbacks::route('/'),
            'create' => Pages\CreateCallback::route('/create'),
            'view' => Pages\ViewCallback::route('/{record}'),
            'edit' => Pages\EditCallback::route('/{record}/edit'),
        ];
    }
}
