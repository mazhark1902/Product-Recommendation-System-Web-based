<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuotationResource\Pages;
use App\Models\Quotation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\Action;
use App\Filament\Resources\QuotationResource\Pages\ViewDetailQuotation;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use BezhanSalleh\FilamentShield\Traits\HasShieldFormComponents;
use Illuminate\Support\HtmlString;
use Carbon\Carbon;


class QuotationResource extends Resource
{
    use HasShieldFormComponents;
    protected static ?string $model = Quotation::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    public static ?string $navigationGroup = 'Sales';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('quotation_id')
                    ->default(function () {
                        $latest = Quotation::orderBy('quotation_id', 'desc')->first()?->quotation_id ?? 'QUO060000';
                        $nextNumber = str_pad((intval(substr($latest, 3)) + 1), 6, '0', STR_PAD_LEFT);
                        return 'QUO' . $nextNumber;
                    })
                    ->disabled()
                    ->dehydrated()
                    ->required(),

                Select::make('outlet_code')
                    ->relationship('outlet', 'outlet_name')
                    ->required(),

                DatePicker::make('quotation_date')
                    ->label('Quotation Date')
                    ->required()
                    ->maxDate(now()) // hanya bisa pilih hari ini & ke belakang
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $set('valid_until', Carbon::parse($state)->addDays(7)->format('Y-m-d'));
                        }
                    }),
                DatePicker::make('valid_until')
                    ->label('Valid Until')
                    ->required()
                    ->disabled()   // readonly di form
                    ->dehydrated() // tetap disimpan ke DB
                    ->helperText(new HtmlString('<span class="text-red-600">* Valid until 7 days after quotation date</span>')), // ini penting supaya HTML di helperText terbaca
// tetap simpan ke DB walau disabled// tetap kirim ke DB walau disabled

                TextInput::make('total_amount')
                    ->label('Total Amount')
                    ->disabled()
                    ->dehydrated()
                    ->reactive(),

                Repeater::make('items')
                    ->relationship()
                    ->schema([
                        Select::make('part_number')
                            ->label('Master Part')
                            ->options(\App\Models\MasterPart::pluck('part_name', 'part_number'))
                            ->reactive()
                            ->afterStateUpdated(function (callable $set) {
                                $set('sub_part_number', null); // reset sub part jika master part berubah
                                $set('unit_price', null);
                                $set('quantity', null);
                                $set('subtotal', null);
                            }),

                            Select::make('sub_part_number') // <- yang disimpan
                            ->label('Sub Part')
                            ->options(function (callable $get) {
                                return \App\Models\SubPart::where('part_number', $get('part_number'))
                                    ->pluck('sub_part_name', 'sub_part_number');
                            })
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $price = \App\Models\SubPart::where('sub_part_number', $state)->value('price');
                                $set('unit_price', $price);
                            }),
                        

                        TextInput::make('quantity')
                            ->numeric()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $subtotal = ($state ?? 0) * ($get('unit_price') ?? 0);
                                $set('subtotal', $subtotal);

                                $items = $get('../../items') ?? [];
                                $total = collect($items)->sum(function ($item) {
                                    return ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
                                });
                                $set('../../total_amount', $total);
                            }),

                        TextInput::make('unit_price')
                            ->disabled()
                            ->numeric()
                            ->dehydrated()
                            ->required(),

                        TextInput::make('subtotal')
                            ->disabled()
                            ->numeric()
                            ->dehydrated()
                            ->required(),
                    ])
                    ->columns(2)
                    ->createItemButtonLabel('Add More Product')
                    ->afterStateUpdated(function ($state, callable $set) {
                        $total = collect($state)->sum(function ($item) {
                            return ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
                        });
                        $set('total_amount', $total);
                    })
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('quotation_id')->searchable(),
                TextColumn::make('outlet_code')->searchable(),
                TextColumn::make('quotation_date'),
                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'Pending',
                        'success' => 'Approved',
                        'danger' => 'Rejected',
                    ]),
                TextColumn::make('total_amount')->money('IDR'),
            ])
            ->actions([
                Action::make('Edit')
                ->slideOver()  
                ->label('Edit')
                ->url(fn (Quotation $record) => $record->status === 'Pending'
                    ? route('filament.admin.resources.quotations.edit', ['record' => $record])
                    : null)
                ->visible(fn (Quotation $record) => $record->status === 'Pending'),
                // ->openUrlInNewTab()

                Action::make('View Detail')
                ->label('View')
                ->icon('heroicon-o-eye')
                ->url(fn ($record) => QuotationResource::getUrl('view-detail', ['record' => $record]))
                ->openUrlInNewTab(),
            ])
            ->filters([
                // 1. Filter Status
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Pending' => 'Pending',
                        'Approved' => 'Approved',
                        'Rejected' => 'Rejected',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->where('status', $data['value']);
                        }
                    }),
            
                // 2. Filter Tahun Quotation Date
                SelectFilter::make('quotation_year')
                    ->label('Quotation Year')
                    ->options(
                        fn () => \App\Models\Quotation::query()
                            ->selectRaw('YEAR(quotation_date) as year')
                            ->distinct()
                            ->orderBy('year', 'desc')
                            ->pluck('year', 'year')
                            ->filter()
                            ->toArray()
                    )
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereYear('quotation_date', $data['value']);
                        }
                    }),
            
                // 3. Filter Total Amount
                SelectFilter::make('total_amount_range')
                    ->label('Total Amount')
                    ->options([
                        'below' => '< 100,000',
                        'above' => '>= 100,000',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === 'below') {
                            $query->where('total_amount', '<', 100000);
                        } elseif ($data['value'] === 'above') {
                            $query->where('total_amount', '>=', 100000);
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);            

                
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuotations::route('/'),
            'create' => Pages\CreateQuotation::route('/create'),
            'edit' => Pages\EditQuotation::route('/{record}/edit'),
            // Tambahkan ini:
            'view-detail' => ViewDetailQuotation::route('/{record}/view-detail'),
        ];
    }
}
