<?php

namespace WebWizr\AdminPanel\Filament\Resources;

use WebWizr\AdminPanel\Filament\Resources\StagingCommentResource\Pages;
use WebWizr\AdminPanel\Models\StagingComment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class StagingCommentResource extends Resource
{
    protected static ?string $model = StagingComment::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationGroup = 'Website';
    protected static ?int $navigationSort = 30;

    public static function getNavigationLabel(): string
    {
        return __('admin.staging_comments');
    }

    public static function getModelLabel(): string
    {
        return __('admin.staging_comment');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('admin.comment_details'))
                ->schema([
                    Forms\Components\TextInput::make('author_name')
                        ->label(__('admin.author_name'))
                        ->disabled(),
                    Forms\Components\TextInput::make('author_email')
                        ->label(__('admin.author_email'))
                        ->disabled(),
                    Forms\Components\TextInput::make('page_url')
                        ->label(__('admin.page_url'))
                        ->disabled(),
                    Forms\Components\TextInput::make('section_selector')
                        ->label(__('admin.section'))
                        ->disabled(),
                    Forms\Components\Textarea::make('content')
                        ->label(__('admin.content'))
                        ->disabled()
                        ->columnSpanFull()
                        ->rows(4),
                ])
                ->columns(2),

            Forms\Components\Section::make(__('admin.ai_suggestion'))
                ->schema([
                    Forms\Components\Textarea::make('ai_suggestion')
                        ->label(__('admin.suggestion'))
                        ->disabled()
                        ->columnSpanFull()
                        ->rows(4),
                    Forms\Components\Toggle::make('ai_suggestion_approved')
                        ->label(__('admin.approve_suggestion')),
                ])
                ->visible(fn ($record) => $record?->ai_suggestion),

            Forms\Components\Section::make(__('admin.response'))
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label(__('admin.status'))
                        ->options([
                            'pending' => __('admin.status_pending'),
                            'in_progress' => __('admin.status_in_progress'),
                            'resolved' => __('admin.status_resolved'),
                            'rejected' => __('admin.status_rejected'),
                        ])
                        ->required(),
                    Forms\Components\Textarea::make('admin_response')
                        ->label(__('admin.your_response'))
                        ->rows(4)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.date'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('page_url')
                    ->label(__('admin.page'))
                    ->limit(30)
                    ->searchable(),
                Tables\Columns\TextColumn::make('section_selector')
                    ->label(__('admin.section'))
                    ->limit(20),
                Tables\Columns\TextColumn::make('content')
                    ->label(__('admin.content'))
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\IconColumn::make('ai_suggestion')
                    ->label('AI')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !empty($record->ai_suggestion)),
                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('admin.status'))
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'in_progress',
                        'success' => 'resolved',
                        'danger' => 'rejected',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('admin.status'))
                    ->options([
                        'pending' => __('admin.status_pending'),
                        'in_progress' => __('admin.status_in_progress'),
                        'resolved' => __('admin.status_resolved'),
                        'rejected' => __('admin.status_rejected'),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('view_page')
                    ->label(__('admin.view'))
                    ->icon('heroicon-o-eye')
                    ->url(fn (StagingComment $record) => url($record->page_url))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('resolve')
                    ->label(__('admin.resolve'))
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (StagingComment $record) => $record->resolve(Auth::user()))
                    ->visible(fn (StagingComment $record) => $record->status !== 'resolved'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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
            'index' => Pages\ListStagingComments::route('/'),
            'edit' => Pages\EditStagingComment::route('/{record}/edit'),
        ];
    }
}
