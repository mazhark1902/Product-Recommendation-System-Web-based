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
            
            // Fitur 'import_csv' telah dihapus sepenuhnya dari sini
        ];
    }
}