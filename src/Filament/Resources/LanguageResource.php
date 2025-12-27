<?php

namespace WebWizr\AdminPanel\Filament\Resources;

use WebWizr\AdminPanel\Filament\Resources\LanguageResource\Pages;
use WebWizr\AdminPanel\Models\Language;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class LanguageResource extends Resource
{
    protected static ?string $model = Language::class;
    protected static ?string $navigationIcon = 'heroicon-o-language';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 90;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user && $user->isSuperAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('Language Code')
                    ->required()
                    ->maxLength(5)
                    ->placeholder('en')
                    ->helperText('ISO 639-1 code (e.g., en, de, fr)'),
                Forms\Components\TextInput::make('name')
                    ->label('Language Name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('English'),
                Forms\Components\TextInput::make('native_name')
                    ->label('Native Name')
                    ->maxLength(255)
                    ->placeholder('English'),
                Forms\Components\Toggle::make('is_default')
                    ->label('Default Language')
                    ->helperText('Setting this as default will unset other languages'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
                Forms\Components\TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('native_name')
                    ->label('Native Name'),
                Tables\Columns\IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLanguages::route('/'),
            'create' => Pages\CreateLanguage::route('/create'),
            'edit' => Pages\EditLanguage::route('/{record}/edit'),
        ];
    }
}
