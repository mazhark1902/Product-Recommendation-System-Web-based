<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class ImportStock extends Page implements HasForms
{
    use InteractsWithForms, WithFileUploads;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';
    protected static string $view = 'filament.pages.import-stock';
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?int $navigationSort = 3;

    public ?array $file = [];
    public array $previewData = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            FileUpload::make('file')
                ->label('Upload File CSV Stok Masuk')
                ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel'])
                ->required()
                ->helperText('Gunakan header: product_id, quantity, location (opsional).')
                ->reactive()
                ->afterStateUpdated(function ($state) {
                    if (empty($state)) {
                        $this->previewData = [];
                        session()->forget('import_preview_data');
                        return;
                    }
                    // === PERUBAHAN UTAMA DI SINI ===
                    // Langsung kirim objek $state, bukan $state[0]
                    $this->processFile($state);
                    // ==============================
                }),
        ];
    }
    
    public function processFile($uploadedFile)
    {
        $this->previewData = [];
        
        try {
            $filePath = $uploadedFile->getRealPath();
            $fileHandle = fopen($filePath, 'r');
            $header = array_map('trim', fgetcsv($fileHandle));

            $requiredHeaders = ['product_id', 'quantity'];
            if (count(array_diff($requiredHeaders, $header)) > 0) {
                Notification::make()->title('Header CSV Tidak Sesuai!')->body('Pastikan file CSV mengandung kolom: ' . implode(', ', $requiredHeaders))->danger()->send();
                fclose($fileHandle);
                return;
            }

            $validation = ['valid_rows' => [], 'invalid_rows' => []];

            while (($row = fgetcsv($fileHandle)) !== false) {
                if (count(array_filter($row)) == 0) continue;
                $rowData = array_combine($header, $row);
                
                $product = DB::table('sub_parts')->where('sub_part_number', $rowData['product_id'])->first();
                $quantity = filter_var($rowData['quantity'], FILTER_VALIDATE_INT);
                
                if ($product && $quantity > 0) {
                    $rowData['product_name'] = $product->sub_part_name;
                    $validation['valid_rows'][] = $rowData;
                } else {
                    $rowData['error'] = !$product ? 'Product ID tidak ditemukan.' : 'Kuantitas tidak valid.';
                    $validation['invalid_rows'][] = $rowData;
                }
            }
            fclose($fileHandle);

            $this->previewData = $validation;
            session(['import_preview_data' => $validation['valid_rows']]);
        } catch (\Exception $e) {
            Notification::make()->title('Gagal Membaca File')->body($e->getMessage())->danger()->send();
            $this->previewData = [];
            session()->forget('import_preview_data');
        }
    }
    
    public function confirmImport()
    {
        $validRows = session('import_preview_data', []);
        if (empty($validRows)) {
            Notification::make()->title('Tidak Ada Data untuk Diimpor')->warning()->send();
            return;
        }

        DB::transaction(function () use ($validRows) {
            $processedCount = 0;
            foreach ($validRows as $row) {
                $inventory = Inventory::firstOrCreate(['product_id' => $row['product_id']]);
                $inventory->increment('quantity_available', (int)$row['quantity']);
                $inventory->save();

                InventoryMovement::create([
                    'inventory_movement_id' => 'IM-' . strtoupper(Str::random(8)),
                    'product_id' => $row['product_id'],
                    'movement_type' => 'IN',
                    'quantity' => (int)$row['quantity'],
                    'movement_date' => now(),
                    'reference_type' => 'CSV_IMPORT',
                    'notes' => 'Stok masuk dari impor CSV.',
                ]);
                $processedCount++;
            }
            
            Notification::make()->title('Impor Berhasil!')->body("Berhasil memproses {$processedCount} baris data.")->success()->send();
        });

        $this->previewData = [];
        $this->file = null;
        session()->forget('import_preview_data');
        $this->form->fill();
    }
}