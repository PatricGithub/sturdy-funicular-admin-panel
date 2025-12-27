<?php

namespace WebWizr\AdminPanel\Filament\Resources;

use WebWizr\AdminPanel\Filament\Resources\PipelineStageResource\Pages;
use WebWizr\AdminPanel\Models\PipelineStage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PipelineStageResource extends Resource
{
    protected static ?string $model = PipelineStage::class;

    protected static ?string $navigationIcon = 'heroicon-o-funnel';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 25;

    public static function getNavigationLabel(): string
    {
        return __('admin.pipeline_stages');
    }

    public static function getModelLabel(): string
    {
        return __('admin.pipeline_stage');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.pipeline_stages');
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

        return Auth::user()?->hasPermission('manage_crm') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('admin.name'))
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('slug')
                            ->label(__('admin.slug'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\ColorPicker::make('color')
                            ->label(__('admin.color'))
                            ->required(),

                        Forms\Components\TextInput::make('order')
                            ->label(__('admin.order'))
                            ->numeric()
                            ->default(0),

                        Forms\Components\Toggle::make('is_won')
                            ->label(__('admin.is_won_stage'))
                            ->helperText(__('admin.is_won_stage_help')),

                        Forms\Components\Toggle::make('is_lost')
                            ->label(__('admin.is_lost_stage'))
                            ->helperText(__('admin.is_lost_stage_help')),

                        Forms\Components\Toggle::make('is_active')
                            ->label(__('admin.is_active'))
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->label(__('admin.order'))
                    ->sortable(),

                Tables\Columns\ColorColumn::make('color')
                    ->label(__('admin.color')),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('deals_count')
                    ->label(__('admin.deals'))
                    ->counts('deals')
                    ->badge(),

                Tables\Columns\IconColumn::make('is_won')
                    ->label(__('admin.won'))
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_lost')
                    ->label(__('admin.lost'))
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('admin.active'))
                    ->boolean(),
            ])
            ->defaultSort('order')
            ->reorderable('order')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPipelineStages::route('/'),
            'create' => Pages\CreatePipelineStage::route('/create'),
            'edit' => Pages\EditPipelineStage::route('/{record}/edit'),
        ];
    }
}
