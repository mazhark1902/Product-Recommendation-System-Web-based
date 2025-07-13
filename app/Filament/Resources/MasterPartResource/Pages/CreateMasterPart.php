<?php

namespace App\Filament\Resources\MasterPartResource\Pages;

use App\Filament\Resources\MasterPartResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMasterPart extends CreateRecord
{
    protected static string $resource = MasterPartResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Jika part_price tidak ada dalam data dari form (sesuai harapan kita),
        // atur defaultnya menjadi 0.00 sebelum data disimpan.
        if (!isset($data['part_price'])) {
            $data['part_price'] = 0.00;
        }
        return $data;
    }

    // Anda mungkin ingin mengarahkan pengguna kembali ke halaman index setelah berhasil membuat record
    // protected function getRedirectUrl(): string
    // {
    //     return $this->getResource()::getUrl('index');
    // }
}