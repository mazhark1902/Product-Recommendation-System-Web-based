<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\ImportLog;
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
                ->label('Upload Incoming Stock CSV File')
                ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel'])
                ->required()
                ->helperText('Use headers: product_id, quantity, location (optional).')
                ->reactive()
                ->afterStateUpdated(function ($state) {
                    if (empty($state)) {
                        $this->previewData = [];
                        session()->forget('import_preview_data');
                        return;
                    }
                    // --- PERBAIKAN DI SINI ---
                    // Langsung kirim objek $state, bukan $state[0]
                    $this->processFile($state);
                }),
        ];
    }
    
    public function processFile($uploadedFile)
    {
        $this->previewData = [];
        session()->forget('import_preview_data');

        if (!$uploadedFile) {
            return;
        }
        
        try {
            $filePath = $uploadedFile->getRealPath();
            $fileChecksum = md5_file($filePath);

            $existingLog = ImportLog::where('file_checksum', $fileChecksum)
                                    ->where('status', 'success')
                                    ->first();

            if ($existingLog) {
                Notification::make()
                    ->title('Duplicate File Detected')
                    ->body("This file appears to have been successfully imported on {$existingLog->created_at->format('d M Y, H:i')}.")
                    ->danger()
                    ->persistent()
                    ->send();
                $this->file = [];
                return;
            }

            $fileHandle = fopen($filePath, 'r');
            $header = array_map('trim', fgetcsv($fileHandle));

            $requiredHeaders = ['product_id', 'quantity'];
            if (count(array_diff($requiredHeaders, $header)) > 0) {
                Notification::make()->title('Invalid CSV Header!')->body('Ensure the CSV file contains the columns: ' . implode(', ', $requiredHeaders))->danger()->send();
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
                    $rowData['error'] = !$product ? 'Product ID not found.' : 'Invalid quantity.';
                    $validation['invalid_rows'][] = $rowData;
                }
            }
            fclose($fileHandle);

            $this->previewData = $validation;
            session([
                'import_preview_data' => $validation['valid_rows'],
                'import_file_checksum' => $fileChecksum,
                'import_file_name' => $uploadedFile->getClientOriginalName()
            ]);

        } catch (\Exception $e) {
            Notification::make()->title('Failed to Read File')->body($e->getMessage())->danger()->send();
            $this->resetState();
        }
    }
    
    public function confirmImport()
    {
        $validRows = session('import_preview_data', []);
        $fileChecksum = session('import_file_checksum');
        $fileName = session('import_file_name');

        if (empty($validRows) || !$fileChecksum || !$fileName) {
            Notification::make()->title('No Data to Import')->body('Please upload a valid file first.')->warning()->send();
            return;
        }

        DB::transaction(function () use ($validRows, $fileChecksum, $fileName) {
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
                    'notes' => "Stock in from CSV import: {$fileName}",
                ]);
                $processedCount++;
            }
            
            ImportLog::create([
                'file_name' => $fileName,
                'file_checksum' => $fileChecksum,
                'status' => 'success',
                'total_rows' => count($validRows),
                'processed_rows' => $processedCount,
                'user_id' => auth()->id(),
            ]);
            
            Notification::make()->title('Import Successful!')->body("Successfully processed {$processedCount} rows of data.")->success()->send();
        });

        $this->resetState();
    }

    private function resetState(): void
    {
        $this->previewData = [];
        $this->file = [];
        session()->forget(['import_preview_data', 'import_file_checksum', 'import_file_name']);
        $this->form->fill();
    }
}
