<?php

namespace WebWizr\AdminPanel\Filament\Resources\ContactInquiryResource\Pages;

use WebWizr\AdminPanel\Filament\Resources\ContactInquiryResource;
use WebWizr\AdminPanel\Models\ContactInquiry;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewContactInquiry extends ViewRecord
{
    protected static string $resource = ContactInquiryResource::class;

    protected function getHeaderActions(): array
    {
        $mode = ContactInquiryResource::getCrmMode();

        if ($mode === 'contact_only') {
            return [];
        }

        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $mode = ContactInquiryResource::getCrmMode();

        return $infolist
            ->schema([
                Section::make(__('admin.inquiry_details'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('name')
                                    ->label(__('admin.name')),
                                TextEntry::make('email')
                                    ->label(__('admin.email'))
                                    ->copyable(),
                                TextEntry::make('phone')
                                    ->label(__('admin.phone'))
                                    ->copyable()
                                    ->placeholder('-'),
                                TextEntry::make('preferred_contact')
                                    ->label(__('admin.preferred_contact'))
                                    ->placeholder('-'),
                                TextEntry::make('source')
                                    ->label(__('admin.source'))
                                    ->badge()
                                    ->formatStateUsing(fn (string $state): string => ContactInquiry::getSourceOptions()[$state] ?? $state),
                                TextEntry::make('created_at')
                                    ->label(__('admin.date'))
                                    ->dateTime('d.m.Y H:i'),
                            ]),
                    ]),

                Section::make(__('admin.service'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('move_type')
                                    ->label(__('admin.move_type'))
                                    ->placeholder('-'),
                                TextEntry::make('move_size')
                                    ->label(__('admin.move_size'))
                                    ->placeholder('-'),
                                TextEntry::make('move_date')
                                    ->label(__('admin.move_date'))
                                    ->placeholder('-'),
                                TextEntry::make('address_from')
                                    ->label(__('admin.address_from'))
                                    ->placeholder('-'),
                                TextEntry::make('address_to')
                                    ->label(__('admin.address_to'))
                                    ->placeholder('-'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make(__('admin.message'))
                    ->schema([
                        TextEntry::make('message')
                            ->label('')
                            ->placeholder(__('admin.message') . ' -')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->visible(fn ($record) => !empty($record->message)),

                Section::make(__('admin.status'))
                    ->schema(array_filter([
                        TextEntry::make('status')
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
                            ->formatStateUsing(fn (string $state): string => ContactInquiry::getStatusOptions()[$state] ?? $state),
                        $mode === 'full' ? TextEntry::make('assignedUser.name')
                            ->label(__('admin.assigned_to'))
                            ->placeholder(__('admin.unassigned')) : null,
                        $mode === 'full' ? TextEntry::make('notes')
                            ->label(__('admin.notes'))
                            ->placeholder('-')
                            ->columnSpanFull() : null,
                    ]))
                    ->columns(2),
            ]);
    }
}
