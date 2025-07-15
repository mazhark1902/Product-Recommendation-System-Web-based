<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryResource\Pages;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\SubPart;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class InventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')->label('Sub Part')->options(SubPart::all()->pluck('sub_part_name', 'sub_part_number'))->searchable()->required()->unique(ignoreRecord: true)->disabledOn('edit'),
                Forms\Components\TextInput::make('quantity_available')->label('Stok Tersedia')->numeric()->required()->default(0),
                Forms\Components\TextInput::make('minimum_stock')->label('Stok Minimum')->numeric()->required()->default(10),
                Forms\Components\TextInput::make('quantity_reserved')->label('Stok Dipesan')->numeric()->default(0),
                Forms\Components\TextInput::make('quantity_damaged')->label('Stok Rusak')->numeric()->default(0),
                Forms\Components\TextInput::make('location')->label('Lokasi Penyimpanan')->maxLength(100),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subPart.sub_part_name')->label('Nama Sub Part')->searchable()->sortable()->placeholder('N/A'),
                Tables\Columns\TextColumn::make('product_id')->label('Kode Sub Part')->searchable(),
                Tables\Columns\TextColumn::make('quantity_available')->label('Stok Tersedia')->numeric()->sortable()->color(fn ($state, $record) => $state > $record->minimum_stock ? 'success' : 'danger')->weight('bold'),
                Tables\Columns\TextColumn::make('minimum_stock')->label('Stok Min.')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('quantity_reserved')->label('Dipesan')->numeric()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('quantity_damaged')->label('Rusak')->numeric()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('location')->label('Lokasi')->searchable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')->label('Update Terakhir')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('critical_stock')->label('Stok Kritis')->query(fn (Builder $query): Builder => $query->whereColumn('quantity_available', '<=', 'minimum_stock')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Action::make('import_stock')
                    ->label('Impor Stok Masuk (CSV)')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary')
                    ->form([
                        FileUpload::make('attachment')
                            ->label('File CSV Stok Masuk')
                            ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel'])
                            ->required()
                            ->helperText('Unggah file CSV dengan header: product_id,quantity,location'),
                    ])
                    ->action(function (array $data) {
                        DB::beginTransaction();
                        try {
                            /** @var TemporaryUploadedFile $file */
                            $file = TemporaryUploadedFile::createFromLivewire($data['attachment']);

                            // --- KODE PERBAIKAN FINAL ---
                            // 1. Baca langsung seluruh konten file ke dalam string
                            $csvContent = $file->get();
                            // 2. Gunakan preg_split untuk memecah baris dengan cara yang lebih andal
                            $rows = preg_split('/\r\n|\r|\n/', trim($csvContent));
                            // -----------------------------

                            $header = null;
                            $processedCount = 0;
                            foreach ($rows as $rowIndex => $rowString) {
                                if (empty(trim($rowString))) {
                                    continue;
                                }

                                if ($rowIndex === 0) {
                                    $header = str_getcsv(trim($rowString), ',');
                                    $requiredHeaders = ['product_id', 'quantity', 'location'];
                                    if (count(array_diff($requiredHeaders, array_map('trim', $header))) > 0) {
                                        throw new \Exception('Header CSV tidak sesuai. Pastikan mengandung: ' . implode(', ', $requiredHeaders));
                                    }
                                    continue;
                                }

                                $row = str_getcsv(trim($rowString), ',');
                                if (count($header) !== count($row)) {
                                    continue;
                                }

                                $rowData = array_combine($header, $row);

                                $inventory = Inventory::firstOrCreate(
                                    ['product_id' => $rowData['product_id']],
                                    ['quantity_available' => 0, 'minimum_stock' => 10]
                                );

                                $quantity_in = (int) $rowData['quantity'];
                                $inventory->increment('quantity_available', $quantity_in);
                                $inventory->location = $rowData['location'];
                                $inventory->save();

                                InventoryMovement::create([
                                    'inventory_movement_id' => 'IM-' . strtoupper(Str::random(8)),
                                    'product_id' => $rowData['product_id'],
                                    'movement_type' => 'IN',
                                    'quantity' => $quantity_in,
                                    'movement_date' => now(),
                                    'reference_type' => 'CSV_IMPORT',
                                    'notes' => 'Stok masuk dari impor CSV',
                                ]);
                                $processedCount++;
                            }

                            if ($processedCount === 0 && count($rows) > 1) {
                                throw new \Exception("Tidak ada baris data valid yang ditemukan setelah header.");
                            }

                            DB::commit();
                            Notification::make()
                                ->title("Impor Selesai!")
                                ->body("Berhasil memproses {$processedCount} baris data stok masuk.")
                                ->success()->send();

                        } catch (\Exception $e) {
                            DB::rollBack();
                            Notification::make()
                                ->title('Impor Gagal!')
                                ->body('Terjadi kesalahan: ' . $e->getMessage())->danger()->send();
                        }
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventories::route('/'),
            'create' => Pages\CreateInventory::route('/create'),
            'edit' => Pages\EditInventory::route('/{record}/edit'),
        ];
    }
}