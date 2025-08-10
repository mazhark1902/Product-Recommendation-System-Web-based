<?php

namespace App\Filament\Resources\StockRequestResource\Pages;

use App\Filament\Resources\StockRequestResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;

class ViewStockRequest extends ViewRecord
{
    protected static string $resource = StockRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Request Information')
                    ->schema([
                        TextEntry::make('request_id'),
                        TextEntry::make('user.name')->label('Requester'),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'PENDING' => 'warning',
                                'COMPLETED' => 'success',
                                'REJECTED' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('created_at')->dateTime(),
                        TextEntry::make('notes')->columnSpanFull(),
                    ])->columns(2),
                Section::make('Requested Items')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('') // Hide the main label for a cleaner look
                            ->schema([
                                TextEntry::make('subPart.sub_part_name')
                                    ->label('Item Name')
                                    ->weight('bold'),
                                TextEntry::make('sub_part_number')
                                    ->label('Part Number'),
                                TextEntry::make('quantity_requested')
                                    ->label('Qty Requested'),
                                TextEntry::make('quantity_received')
                                    ->label('Qty Received')
                                    ->badge()
                                    ->color('success'),
                            ])
                            ->columns(4)
                    ])
            ]);
    }
}