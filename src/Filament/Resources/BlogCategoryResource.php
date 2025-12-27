<?php

namespace WebWizr\AdminPanel\Filament\Resources;

use WebWizr\AdminPanel\Filament\Resources\BlogCategoryResource\Pages;
use WebWizr\AdminPanel\Models\BlogCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BlogCategoryResource extends Resource
{
    protected static ?string $model = BlogCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?string $navigationGroup = 'Blog';

    protected static ?int $navigationSort = 21;

    public static function getNavigationLabel(): string
    {
        return __('admin.blog_categories');
    }

    public static function getModelLabel(): string
    {
        return __('admin.blog_category');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.blog_categories');
    }

    public static function canAccess(): bool
    {
        if (!config('company.blog_enabled')) {
            return false;
        }

        return Auth::user()?->hasPermission('view_blog') ?? false;
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
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                if ($operation === 'create') {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        Forms\Components\TextInput::make('slug')
                            ->label(__('admin.slug'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText(__('admin.slug_help')),

                        Forms\Components\Textarea::make('description')
                            ->label(__('admin.description'))
                            ->rows(3),

                        Forms\Components\TextInput::make('order')
                            ->label(__('admin.order'))
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('SEO')
                    ->schema([
                        Forms\Components\TextInput::make('seo_title')
                            ->label(__('admin.seo_title'))
                            ->maxLength(60)
                            ->helperText(__('admin.seo_title_help')),

                        Forms\Components\Textarea::make('seo_description')
                            ->label(__('admin.seo_description'))
                            ->rows(3)
                            ->maxLength(160)
                            ->helperText(__('admin.seo_description_help')),
                    ])
                    ->collapsible(),
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

                Tables\Columns\TextColumn::make('slug')
                    ->label(__('admin.slug'))
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('posts_count')
                    ->label(__('admin.blog_posts'))
                    ->counts('posts')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('order')
                    ->label(__('admin.order'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('admin.updated'))
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => Auth::user()?->hasPermission('manage_blog')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()?->hasPermission('manage_blog')),
                ]),
            ])
            ->defaultSort('order')
            ->reorderable('order');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogCategories::route('/'),
            'create' => Pages\CreateBlogCategory::route('/create'),
            'edit' => Pages\EditBlogCategory::route('/{record}/edit'),
        ];
    }
}
