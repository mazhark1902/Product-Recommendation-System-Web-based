<?php

namespace App\Filament\Resources\MasterPartResource\Pages;

use App\Filament\Resources\MasterPartResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use App\Models\MasterPart;
use App\Models\SubPart;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ListMasterParts extends ListRecords
{
    protected static string $resource = MasterPartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('import_csv')
                ->label('Impor dari CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->form([
                    FileUpload::make('attachment')
                        ->label('File CSV Suku Cadang')
                        ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel'])
                        ->required()
                        ->helperText('Unggah file CSV dengan header: master_part_number, master_part_name, sub_part_number, sub_part_name, sub_part_price.'),
                ])
                ->action(function (array $data) {
    // --- PENDEKATAN BARU: LANGSUNG MEMBACA KONTEN FILE ---
    try {
        $file = \Livewire\Features\SupportFileUploads\TemporaryUploadedFile::createFromLivewire($data['attachment']);
        
        // Membaca seluruh konten file CSV menjadi string
        $csvContent = $file->get();
        // Mengubah string menjadi array per baris
        $rows = explode(PHP_EOL, trim($csvContent));
        
    } catch (\Exception $e) {
        Notification::make()->title('Gagal memproses file!')->body('File sementara tidak ditemukan. Silakan coba unggah kembali.')->danger()->send();
        Log::error("CSV Import: Could not read file content from Livewire. " . $e->getMessage());
        return;
    }
    // ----------------------------------------------------

    $header = null;
    $importedCount = 0;

    DB::beginTransaction();
    try {
        foreach ($rows as $rowIndex => $rowString) {
            // Lewati baris yang kosong
            if (empty(trim($rowString))) {
                continue;
            }

            // Mem-parse setiap baris string menjadi array
            $row = str_getcsv($rowString, ',');

            if (!$header) {
                $header = array_map('trim', $row);
                // Validasi header untuk memastikan file CSV sudah benar
                $requiredHeaders = ['master_part_number', 'master_part_name', 'sub_part_number', 'sub_part_name', 'sub_part_price'];
                if (count(array_diff($requiredHeaders, $header)) > 0) {
                    throw new \Exception('Header CSV tidak sesuai. Pastikan mengandung: ' . implode(', ', $requiredHeaders));
                }
                continue;
            }
            
            // Lewati baris yang jumlah kolomnya tidak cocok dengan header
            if (count($header) !== count($row)) {
                Log::warning('CSV Import: Melewatkan baris ke-' . ($rowIndex + 1) . ' karena jumlah kolom tidak sesuai.', ['row' => $row]);
                continue;
            }
            
            $rowData = array_combine($header, $row);

            // Proses Master Part
            $masterPart = MasterPart::updateOrCreate(
                ['part_number' => $rowData['master_part_number']],
                ['part_name' => $rowData['master_part_name']]
            );
            
            if ($masterPart->wasRecentlyCreated) {
                $masterPart->part_price = 0;
                $masterPart->save();
            }

            // Proses Sub Part
            SubPart::updateOrCreate(
                ['sub_part_number' => $rowData['sub_part_number']],
                [
                    'part_number' => $masterPart->part_number,
                    'sub_part_name' => $rowData['sub_part_name'],
                    'price' => $rowData['sub_part_price'],
                ]
            );

            $importedCount++;
        }

        DB::commit();
        Notification::make()
            ->title("Impor Selesai!")
            ->body("Berhasil memproses {$importedCount} baris data.")
            ->success()
            ->send();

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('CSV Import Failed: ' . $e->getMessage());
        Notification::make()
            ->title('Impor Gagal!')
            ->body('Terjadi kesalahan: ' . $e->getMessage())
            ->danger()
            ->send();
    }
})
                // --- BARIS YANG MENYEBABKAN ERROR SUDAH DIHAPUS ---
                , // Saya menambahkan koma di sini, karena ini adalah elemen terakhir di dalam array
        ];
    }
}