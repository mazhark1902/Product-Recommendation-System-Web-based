<?php

namespace App\Filament\Resources\MasterPaymentMethodResource\Pages;

use App\Filament\Resources\MasterPaymentMethodResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMasterPaymentMethod extends CreateRecord
{
    protected static string $resource = MasterPaymentMethodResource::class;
}
