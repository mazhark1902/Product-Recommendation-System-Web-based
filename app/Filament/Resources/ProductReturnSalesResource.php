<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductReturnSalesResource\Pages;
use App\Filament\Resources\ProductReturnSalesResource\RelationManagers;
use App\Models\ProductReturnSales;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use BezhanSalleh\FilamentShield\Traits\HasShieldFormComponents;


class ProductReturnSalesResource extends Resource
{
      use HasShieldFormComponents;
    protected static ?string $model = ProductReturnSales::class;

   
    protected static ?string $navigationGroup = 'Sales';
    
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';

        public static function canCreate(): bool
{
    return true;
}

public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('return_id')
                ->label('Return ID')
                ->disabled()
                ->dehydrated()
                ->default(function () {
                    $today = now()->format('Ymd');
                    $count = ProductReturnSales::whereDate('created_at', today())->count() + 1;
                    return "RTN-{$today}-" . str_pad($count, 2, '0', STR_PAD_LEFT);
                }),
            Forms\Components\Select::make('sales_order_id')
    ->label('Sales Order ID')
    ->placeholder('Click & type to search Sales Order ID')
    ->searchable()
    ->getSearchResultsUsing(function (string $search) {
        return \App\Models\SalesOrder::query()
            ->where('sales_order_id', 'like', "%{$search}%")
            ->limit(5)
            ->pluck('sales_order_id', 'sales_order_id')
            ->toArray();
    })
    ->getOptionLabelUsing(fn ($value): ?string => \App\Models\SalesOrder::find($value)?->sales_order_id)
    ->required()
    ->reactive()
    ->afterStateUpdated(fn ($state, callable $set) => $set('part_number', null)),
            Forms\Components\Select::make('part_number')
                ->label('Part Number')
                ->options(function (callable $get) {
                    $so = \App\Models\SalesOrderItem::where('sales_order_id', $get('sales_order_id'))->get();
                    return $so->pluck('part_number', 'part_number')->toArray();
                })
                ->searchable()
                ->required()
                ->disabled(fn ($get) => empty($get('sales_order_id'))),
            Forms\Components\TextInput::make('quantity')->numeric()->required(),
            Forms\Components\DatePicker::make('return_date')->required(),
            Forms\Components\Select::make('reason')
                ->options([
                    'Wrong item delivered'=>'Wrong item delivered',
                    'Item defective on arrival'=>'Item defective on arrival',
                    'Customer changed mind'=>'Customer changed mind',
                    'Packaging damaged'=>'Packaging damaged',
                ])
                ->required(),

            Forms\Components\Select::make('refund_action')
                ->label('Refund Action')
                ->options(['RETURN'=>'RETURN','CREDIT_MEMO'=>'CREDIT_MEMO'])
                ->reactive()
                ->required(),
        ]);
    }


    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            TextColumn::make('return_id')->sortable(),
            TextColumn::make('sales_order_id'),
            TextColumn::make('part_number'),
            TextColumn::make('quantity'),
            TextColumn::make('refund_action'),
            TextColumn::make('return_date')->date(),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\ViewAction::make(),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductReturnSales::route('/'),
            'create' => Pages\CreateProductReturnSales::route('/create'),
            'edit' => Pages\EditProductReturnSales::route('/{record}/edit'),
        ];
    }
}
