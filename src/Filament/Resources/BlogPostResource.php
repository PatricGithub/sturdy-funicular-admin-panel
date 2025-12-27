<?php

namespace WebWizr\AdminPanel\Filament\Resources;

use WebWizr\AdminPanel\Filament\Resources\BlogPostResource\Pages;
use WebWizr\AdminPanel\Models\BlogCategory;
use WebWizr\AdminPanel\Models\BlogPost;
use WebWizr\AdminPanel\Models\BlogTag;
use WebWizr\AdminPanel\Services\ImageService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Blog';

    protected static ?int $navigationSort = 20;

    public static function getNavigationLabel(): string
    {
        return __('admin.blog_posts');
    }

    public static function getModelLabel(): string
    {
        return __('admin.blog_post');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.blog_posts');
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
                Forms\Components\Grid::make(3)
                    ->schema([
                        // Main content (2/3 width)
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label(__('admin.title'))
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

                                Forms\Components\Textarea::make('excerpt')
                                    ->label(__('admin.excerpt'))
                                    ->rows(3)
                                    ->helperText(__('admin.excerpt_help')),

                                Forms\Components\RichEditor::make('content')
                                    ->label(__('admin.content'))
                                    ->required()
                                    ->columnSpanFull()
                                    ->toolbarButtons([
                                        'blockquote',
                                        'bold',
                                        'bulletList',
                                        'h2',
                                        'h3',
                                        'italic',
                                        'link',
                                        'orderedList',
                                        'redo',
                                        'strike',
                                        'undo',
                                    ]),
                            ])
                            ->columnSpan(2),

                        // Sidebar (1/3 width)
                        Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\Section::make(__('admin.status'))
                                    ->schema([
                                        Forms\Components\Toggle::make('is_published')
                                            ->label(__('admin.is_published')),

                                        Forms\Components\Toggle::make('is_featured')
                                            ->label(__('admin.is_featured')),

                                        Forms\Components\DateTimePicker::make('published_at')
                                            ->label(__('admin.published_at'))
                                            ->native(false),
                                    ]),

                                Forms\Components\Section::make(__('admin.category'))
                                    ->schema([
                                        Forms\Components\Select::make('category_id')
                                            ->label(__('admin.category'))
                                            ->relationship('category', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->createOptionForm([
                                                Forms\Components\TextInput::make('name')
                                                    ->label(__('admin.name'))
                                                    ->required()
                                                    ->maxLength(255),
                                            ]),

                                        Forms\Components\Select::make('tags')
                                            ->label(__('admin.tags'))
                                            ->multiple()
                                            ->relationship('tags', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->createOptionForm([
                                                Forms\Components\TextInput::make('name')
                                                    ->label(__('admin.name'))
                                                    ->required()
                                                    ->maxLength(255),
                                            ]),
                                    ]),

                                Forms\Components\Section::make(__('admin.featured_image'))
                                    ->schema([
                                        Forms\Components\FileUpload::make('featured_image')
                                            ->label('')
                                            ->image()
                                            ->directory('blog')
                                            ->imageResizeMode('cover')
                                            ->imageCropAspectRatio('16:9')
                                            ->imageResizeTargetWidth('1920')
                                            ->imageResizeTargetHeight('1080')
                                            ->maxSize(5120)
                                            ->saveUploadedFileUsing(function ($file) {
                                                $imageService = new ImageService();
                                                return $imageService->compressAndStore($file, 'blog');
                                            }),
                                    ]),

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
                            ])
                            ->columnSpan(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('featured_image')
                    ->label('')
                    ->circular()
                    ->size(40),

                Tables\Columns\TextColumn::make('title')
                    ->label(__('admin.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('admin.category'))
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('author.name')
                    ->label(__('admin.author'))
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_published')
                    ->label(__('admin.is_published'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label(__('admin.is_featured'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label(__('admin.published_at'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('admin.updated'))
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label(__('admin.is_published')),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label(__('admin.is_featured')),
                Tables\Filters\SelectFilter::make('category_id')
                    ->label(__('admin.category'))
                    ->relationship('category', 'name'),
            ])
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogPosts::route('/'),
            'create' => Pages\CreateBlogPost::route('/create'),
            'edit' => Pages\EditBlogPost::route('/{record}/edit'),
        ];
    }
}
