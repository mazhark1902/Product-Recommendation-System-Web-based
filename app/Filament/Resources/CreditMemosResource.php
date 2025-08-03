<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CreditMemosResource\Pages;
use App\Filament\Resources\CreditMemosResource\RelationManagers;
use App\Models\CreditMemos;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use BezhanSalleh\FilamentShield\Traits\HasShieldFormComponents;

class CreditMemosResource extends Resource 
{

    use HasShieldFormComponents;
    protected static ?string $model = CreditMemos::class;
    public static ?string $navigationGroup = 'Sales';
    

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListCreditMemos::route('/'),
            'create' => Pages\CreateCreditMemos::route('/create'),
            'edit' => Pages\EditCreditMemos::route('/{record}/edit'),
        ];
    }
}
