<?php

// app/Filament/Resources/QuotationApproveResource.php

namespace App\Filament\Resources;

use App\Models\Quotation;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\QuotationApproveResource\Pages;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\BadgeColumn;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use BezhanSalleh\FilamentShield\Traits\HasShieldFormComponents;

class QuotationApproveResource extends Resource
{
    use HasShieldFormComponents;
    protected static ?string $model = Quotation::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationLabel = 'Quotations Approve';
    protected static ?string $navigationGroup = 'Sales';

    public static function getModelLabel(): string
    {
        return 'Quotation Approval';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Quotations Approval';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                Quotation::query()->where('status', 'Pending')
            )
            ->columns([
                TextColumn::make('quotation_id')
                ->searchable()
                ->sortable(), // Tambahkan sortable di sini,
                TextColumn::make('outlet_code'),
                TextColumn::make('quotation_date')->date(),
                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'Pending',
                        'success' => 'Approved',
                        'danger' => 'Rejected',
                    ]),
                TextColumn::make('total_amount')->money('IDR'),
            ])
            ->filters([
                // Filter Tahun
                SelectFilter::make('quotation_year')
                    ->label('Tahun')
                    ->options(
                        fn () => \App\Models\Quotation::query()
                            ->selectRaw('YEAR(quotation_date) as year')
                            ->distinct()
                            ->orderBy('year', 'desc')
                            ->pluck('year', 'year')
                            ->toArray()
                    )
                    ->query(function ($query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereYear('quotation_date', $data['value']);
                        }
                    }),
    
                // Filter Bulan
                SelectFilter::make('quotation_month')
                    ->label('Bulan')
                    ->options([
                        '01' => 'Januari',
                        '02' => 'Februari',
                        '03' => 'Maret',
                        '04' => 'April',
                        '05' => 'Mei',
                        '06' => 'Juni',
                        '07' => 'Juli',
                        '08' => 'Agustus',
                        '09' => 'September',
                        '10' => 'Oktober',
                        '11' => 'November',
                        '12' => 'Desember',
                    ])
                    ->query(function ($query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereMonth('quotation_date', $data['value']);
                        }
                    }),
    
                // Filter Total Amount
                SelectFilter::make('total_amount_range')
                    ->label('Total Amount')
                    ->options([
                        'below' => '< 100,000',
                        'above' => '>= 100,000',
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['value'] === 'below') {
                            $query->where('total_amount', '<', 100000);
                        } elseif ($data['value'] === 'above') {
                            $query->where('total_amount', '>=', 100000);
                        }
                    }),
            ])
            ->actions([
                Action::make('Show Detail')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => static::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab()
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuotationApproves::route('/'),
            'view' => Pages\ViewQuotationApprove::route('/{record}'),
        ];
    }
}
