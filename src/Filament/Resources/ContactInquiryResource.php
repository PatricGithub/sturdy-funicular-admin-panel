<?php

namespace WebWizr\AdminPanel\Filament\Resources;

use WebWizr\AdminPanel\Filament\Resources\ContactInquiryResource\Pages;
use WebWizr\AdminPanel\Models\ContactInquiry;
use WebWizr\AdminPanel\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ContactInquiryResource extends Resource
{
    protected static ?string $model = ContactInquiry::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 10;

    public static function getNavigationLabel(): string
    {
        return __('admin.contact_inquiries');
    }

    public static function getModelLabel(): string
    {
        return __('admin.contact_inquiry');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.contact_inquiries');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'new')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->hasPermission('view_crm') ?? false;
    }

    public static function getCrmMode(): string
    {
        return config('company.crm_mode', 'full');
    }

    public static function form(Form $form): Form
    {
        $mode = static::getCrmMode();

        // Contact Only mode - no edit form
        if ($mode === 'contact_only') {
            return $form->schema([]);
        }

        $schema = [
            Forms\Components\Section::make(__('admin.inquiry_details'))
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label(__('admin.name'))
                        ->disabled(),
                    Forms\Components\TextInput::make('email')
                        ->label(__('admin.email'))
                        ->disabled(),
                    Forms\Components\TextInput::make('phone')
                        ->label(__('admin.phone'))
                        ->disabled(),
                    Forms\Components\TextInput::make('source')
                        ->label(__('admin.source'))
                        ->disabled(),
                    Forms\Components\TextInput::make('move_type')
                        ->label(__('admin.move_type'))
                        ->disabled(),
                    Forms\Components\TextInput::make('move_size')
                        ->label(__('admin.move_size'))
                        ->disabled(),
                    Forms\Components\TextInput::make('move_date')
                        ->label(__('admin.move_date'))
                        ->disabled(),
                    Forms\Components\TextInput::make('address_from')
                        ->label(__('admin.address_from'))
                        ->disabled(),
                    Forms\Components\TextInput::make('address_to')
                        ->label(__('admin.address_to'))
                        ->disabled(),
                    Forms\Components\Textarea::make('message')
                        ->label(__('admin.message'))
                        ->disabled()
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ];

        // Status section
        $statusSchema = [
            Forms\Components\Select::make('status')
                ->label(__('admin.status'))
                ->options(ContactInquiry::getStatusOptions())
                ->required(),
        ];

        // Full mode gets assignment and notes
        if ($mode === 'full') {
            $statusSchema[] = Forms\Components\Select::make('assigned_to')
                ->label(__('admin.assigned_to'))
                ->options(User::whereHas('permissions', function ($query) {
                    $query->where('name', 'view_crm');
                })->orWhere('is_super_admin', true)->pluck('name', 'id'))
                ->searchable()
                ->placeholder(__('admin.unassigned'));

            $statusSchema[] = Forms\Components\Textarea::make('notes')
                ->label(__('admin.notes'))
                ->columnSpanFull();
        }

        $schema[] = Forms\Components\Section::make(__('admin.status'))
            ->schema($statusSchema)
            ->columns(2);

        return $form->schema($schema);
    }

    public static function table(Table $table): Table
    {
        $mode = static::getCrmMode();

        // Base columns for all modes
        $columns = [
            Tables\Columns\TextColumn::make('name')
                ->label(__('admin.name'))
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('email')
                ->label(__('admin.email'))
                ->searchable()
                ->copyable(),
            Tables\Columns\TextColumn::make('phone')
                ->label(__('admin.phone'))
                ->searchable(),
        ];

        // Full mode gets more columns
        if ($mode === 'full') {
            $columns[] = Tables\Columns\TextColumn::make('source')
                ->label(__('admin.source'))
                ->badge()
                ->formatStateUsing(fn (string $state): string => ContactInquiry::getSourceOptions()[$state] ?? $state);

            $columns[] = Tables\Columns\TextColumn::make('status')
                ->label(__('admin.status'))
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'new' => 'danger',
                    'contacted' => 'warning',
                    'qualified' => 'info',
                    'converted' => 'success',
                    'lost' => 'gray',
                    'in_progress' => 'warning',
                    'done' => 'success',
                    default => 'gray',
                })
                ->formatStateUsing(fn (string $state): string => ContactInquiry::getStatusOptions()[$state] ?? $state);

            $columns[] = Tables\Columns\TextColumn::make('assignedUser.name')
                ->label(__('admin.assigned_to'))
                ->placeholder(__('admin.unassigned'))
                ->sortable();
        }

        // Light mode gets simple status
        if ($mode === 'light') {
            $columns[] = Tables\Columns\TextColumn::make('status')
                ->label(__('admin.status'))
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'new' => 'danger',
                    'in_progress' => 'warning',
                    'done' => 'success',
                    default => 'gray',
                })
                ->formatStateUsing(fn (string $state): string => ContactInquiry::getStatusOptions()[$state] ?? $state);
        }

        // Date column for all modes
        $columns[] = Tables\Columns\TextColumn::make('created_at')
            ->label(__('admin.date'))
            ->dateTime('d.m.Y H:i')
            ->sortable();

        // Build filters based on mode
        $filters = [];

        if ($mode === 'full') {
            $filters = [
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('admin.status'))
                    ->options(ContactInquiry::getStatusOptions()),
                Tables\Filters\SelectFilter::make('source')
                    ->label(__('admin.source'))
                    ->options(ContactInquiry::getSourceOptions()),
                Tables\Filters\SelectFilter::make('assigned_to')
                    ->label(__('admin.assigned_to'))
                    ->options(User::whereHas('permissions', function ($query) {
                        $query->where('name', 'view_crm');
                    })->orWhere('is_super_admin', true)->pluck('name', 'id'))
                    ->placeholder(__('admin.unassigned')),
            ];
        }

        if ($mode === 'light') {
            $filters = [
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('admin.status'))
                    ->options(ContactInquiry::getStatusOptions()),
            ];
        }

        // Build actions based on mode
        $actions = [];

        if ($mode === 'contact_only') {
            $actions[] = Tables\Actions\ViewAction::make();
        } else {
            $actions[] = Tables\Actions\ViewAction::make();
            $actions[] = Tables\Actions\EditAction::make();
        }

        // Build bulk actions based on mode
        $bulkActions = [];

        if ($mode === 'full') {
            $bulkActions = [
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('change_status')
                        ->label(__('admin.status'))
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label(__('admin.status'))
                                ->options(ContactInquiry::getStatusOptions())
                                ->required(),
                        ])
                        ->action(function ($records, array $data): void {
                            $records->each->update(['status' => $data['status']]);
                        }),
                    Tables\Actions\BulkAction::make('assign')
                        ->label(__('admin.assigned_to'))
                        ->icon('heroicon-o-user')
                        ->form([
                            Forms\Components\Select::make('assigned_to')
                                ->label(__('admin.assigned_to'))
                                ->options(User::whereHas('permissions', function ($query) {
                                    $query->where('name', 'view_crm');
                                })->orWhere('is_super_admin', true)->pluck('name', 'id'))
                                ->placeholder(__('admin.unassigned')),
                        ])
                        ->action(function ($records, array $data): void {
                            $records->each->update(['assigned_to' => $data['assigned_to']]);
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ];
        }

        return $table
            ->columns($columns)
            ->filters($filters)
            ->actions($actions)
            ->bulkActions($bulkActions)
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        $mode = static::getCrmMode();

        $pages = [
            'index' => Pages\ListContactInquiries::route('/'),
            'view' => Pages\ViewContactInquiry::route('/{record}'),
        ];

        // Only add edit page if not contact_only mode
        if ($mode !== 'contact_only') {
            $pages['edit'] = Pages\EditContactInquiry::route('/{record}/edit');
        }

        return $pages;
    }
}
