<?php

namespace App\Filament\Resources\MasterPartResource\Pages;

use Filament\Resources\Pages\Page;
use App\Filament\Resources\SubPartResource;
use App\Models\SubPart;
use Filament\Resources\Pages\ListRecords;

class ViewSubPart extends Page
{
    protected static string $resource = SubPartResource::class;
 
    public ?SubPart $subPart = null;

    protected static string $view = 'filament.resources.master-part-resource.pages.view-sub-parts';

    public function mount($part_number)
    {
        $this->subPart = SubPart::where('part_number', $part_number)->firstOrFail();
    }
}
