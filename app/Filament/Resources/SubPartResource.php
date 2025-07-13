<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubPartResource\Pages;
use App\Models\SubPart;
use App\Models\MasterPart; // Import MasterPart
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str; // For generating unique sub_part_number if needed

class SubPartResource extends Resource
{
    protected static ?string $model = SubPart::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list'; // Changed for distinction
    protected static ?string $navigationGroup = 'Inventory'; // Or keep it hidden if only managed via MasterPart
    protected static bool $shouldRegisterNavigation = false; // Hide from main navigation if managed contextually


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('part_number')
                    ->relationship('masterPart', 'part_name') // Assumes masterPart relation exists on SubPart model
                    ->label('Master Part')
                    ->options(MasterPart::pluck('part_name', 'part_number')) // Provide options
                    ->searchable()
                    ->required()
                    // When creating from ViewSubParts, this will be pre-filled and potentially disabled.
                    // ->disabled(fn (string $context, ?SubPart $record, Forms\Get $get) => $context === 'edit' || $get('is_contextual_create') === true)
                    ->dehydrated(), // Ensure it's saved

                Forms\Components\TextInput::make('sub_part_number')
                    ->label('Sub Part Number')
                    ->required()
                    ->maxLength(50)
                    ->unique(SubPart::class, 'sub_part_number', ignoreRecord: true)
                    ->default(fn () => 'subPART-' . strtoupper(Str::random(8))) // Optional: default generator
                    ->disabledOn('edit'), // Usually PKs are not editable

                Forms\Components\TextInput::make('sub_part_name')
                    ->label('Sub Part Name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('price')
                    ->label('Price')
                    ->numeric()
                    ->required()
                    ->prefix('IDR'), // Assuming IDR
            ]);
    }

    public static function table(Table $table): Table
    {
        // This table definition is for the standalone SubPartResource index page,
        // which you might not use if managing sub-parts contextually.
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('masterPart.part_name')->label('Master Part')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('sub_part_number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('sub_part_name')->searchable(),
                Tables\Columns\TextColumn::make('price')->money('IDR')->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListSubParts::route('/'),
            'create' => Pages\CreateSubPart::route('/create'),
            'edit' => Pages\EditSubPart::route('/{record}/edit'),
        ];
    }
}