<?php

namespace WebWizr\AdminPanel\Filament\Resources;

use WebWizr\AdminPanel\Filament\Resources\TaskResource\Pages;
use WebWizr\AdminPanel\Models\ContactInquiry;
use WebWizr\AdminPanel\Models\Customer;
use WebWizr\AdminPanel\Models\Deal;
use WebWizr\AdminPanel\Models\Task;
use WebWizr\AdminPanel\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 35;

    public static function getNavigationLabel(): string
    {
        return __('admin.tasks');
    }

    public static function getModelLabel(): string
    {
        return __('admin.task');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.tasks');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::notCompleted()
            ->where(function ($query) {
                $query->whereNull('due_date')
                    ->orWhere('due_date', '<=', today());
            })
            ->count();

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

        // Simplified types for light mode
        $typeOptions = $mode === 'full'
            ? Task::getTypeOptions()
            : [
                'follow_up' => __('admin.task_type_follow_up'),
                'internal' => __('admin.task_type_internal'),
                'other' => __('admin.task_type_other'),
            ];

        $schema = [
            Forms\Components\Section::make(__('admin.task_details'))
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label(__('admin.title'))
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('type')
                        ->label(__('admin.type'))
                        ->options($typeOptions)
                        ->default('other')
                        ->required(),

                    Forms\Components\Select::make('priority')
                        ->label(__('admin.priority'))
                        ->options(Task::getPriorityOptions())
                        ->default('normal')
                        ->required(),

                    Forms\Components\DatePicker::make('due_date')
                        ->label(__('admin.due_date')),

                    Forms\Components\Select::make('status')
                        ->label(__('admin.status'))
                        ->options(Task::getStatusOptions())
                        ->default('open')
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

                    Forms\Components\Textarea::make('description')
                        ->label(__('admin.description'))
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ];

        // Only show polymorphic relation in full mode
        if ($mode === 'full') {
            $schema[] = Forms\Components\Section::make(__('admin.link_to'))
                ->schema([
                    Forms\Components\MorphToSelect::make('taskable')
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

        return $form->schema($schema);
    }

    public static function table(Table $table): Table
    {
        $mode = config('company.crm_mode');

        $columns = [
            Tables\Columns\TextColumn::make('title')
                ->label(__('admin.title'))
                ->searchable()
                ->sortable()
                ->limit(40),

            Tables\Columns\TextColumn::make('type')
                ->label(__('admin.type'))
                ->badge()
                ->icon(fn (Task $record): string => $record->getTypeIcon())
                ->color('gray')
                ->formatStateUsing(fn (string $state): string => Task::getTypeOptions()[$state] ?? $state),

            Tables\Columns\TextColumn::make('priority')
                ->label(__('admin.priority'))
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'high' => 'danger',
                    'low' => 'gray',
                    default => 'warning',
                })
                ->formatStateUsing(fn (string $state): string => Task::getPriorityOptions()[$state] ?? $state),

            Tables\Columns\TextColumn::make('due_date')
                ->label(__('admin.due_date'))
                ->date('d.m.Y')
                ->sortable()
                ->color(fn (Task $record): string => match (true) {
                    $record->isOverdue() => 'danger',
                    $record->isDueToday() => 'warning',
                    default => 'gray',
                })
                ->weight(fn (Task $record): string => $record->isOverdue() || $record->isDueToday() ? 'bold' : 'normal')
                ->icon(fn (Task $record): ?string => $record->isOverdue() ? 'heroicon-o-exclamation-triangle' : null),

            Tables\Columns\TextColumn::make('status')
                ->label(__('admin.status'))
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'open' => 'gray',
                    'in_progress' => 'warning',
                    'completed' => 'success',
                    default => 'gray',
                })
                ->formatStateUsing(fn (string $state): string => Task::getStatusOptions()[$state] ?? $state),
        ];

        // Show taskable column only in full mode
        if ($mode === 'full') {
            $columns[] = Tables\Columns\TextColumn::make('taskable_name')
                ->label(__('admin.linked_to'))
                ->placeholder('-')
                ->limit(25)
                ->toggleable();
        }

        $columns[] = Tables\Columns\TextColumn::make('assignedUser.name')
            ->label(__('admin.assigned_to'))
            ->placeholder(__('admin.unassigned'))
            ->toggleable();

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
                    ->options(Task::getStatusOptions())
                    ->multiple(),

                Tables\Filters\SelectFilter::make('type')
                    ->label(__('admin.type'))
                    ->options(Task::getTypeOptions()),

                Tables\Filters\SelectFilter::make('priority')
                    ->label(__('admin.priority'))
                    ->options(Task::getPriorityOptions()),

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

                Tables\Filters\Filter::make('due_today')
                    ->label(__('admin.due_today'))
                    ->query(fn (Builder $query): Builder => $query->dueToday())
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\Action::make('start')
                    ->label(__('admin.start'))
                    ->icon('heroicon-o-play')
                    ->color('warning')
                    ->action(fn (Task $record) => $record->markAsInProgress())
                    ->visible(fn (Task $record) => $record->status === 'open'),

                Tables\Actions\Action::make('complete')
                    ->label(__('admin.complete'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Task $record) => $record->markAsCompleted())
                    ->visible(fn (Task $record) => in_array($record->status, ['open', 'in_progress'])),

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
            });
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'view' => Pages\ViewTask::route('/{record}'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
