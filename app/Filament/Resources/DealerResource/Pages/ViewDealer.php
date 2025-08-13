<?php

namespace App\Filament\Resources\DealerResource\Pages;

use App\Filament\Resources\DealerResource;
use App\Filament\Resources\OutletDealerResource;
use App\Models\Dealer;
use App\Models\OutletDealer;
use Filament\Resources\Pages\Page as BaseResourcePage;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ViewDealer extends BaseResourcePage implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static string $resource = DealerResource::class;
    protected static string $view = 'filament.resources.dealer-resource.pages.view-dealer';

    public ?Dealer $Dealer = null;

    public function mount($dealer_code): void
    {
        $this->Dealer = Dealer::with('outlets')->where('dealer_code', $dealer_code)->firstOrFail(); 
    }

    protected function getTableQuery(): Builder
    {
        return $this->Dealer->outlets()->getQuery();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('outlet_code')
                ->label('Outlet Code')
                ->searchable(),
            Tables\Columns\TextColumn::make('outlet_name')
                ->label('Outlet Name')
                ->searchable(),
            Tables\Columns\TextColumn::make('email')
                ->label('Email')
                ->searchable(),
            Tables\Columns\TextColumn::make('address')
                ->label('Address')
                ->searchable(),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\EditAction::make()
    ->url(fn (OutletDealer $record): string =>
        OutletDealerResource::getUrl('edit', ['record' => $record->getKey()])),
            Tables\Actions\DeleteAction::make(),
        ];
    }

    protected function getTableHeaderActions(): array
    {
        return [
            Tables\Actions\CreateAction::make()
                ->label('Add New Outlet')
                ->model(OutletDealer::class)
                ->form(function (Forms\Form $form) {
                    // Get OutletDealerResource form schema
                    $configuredForm = OutletDealerResource::form($form);
                    $schemaComponents = $configuredForm->getComponents();

                    // Remove dealer_code from form (we'll set it automatically)
                    $filteredSchema = array_filter($schemaComponents, function ($component) {
                        if (method_exists($component, 'getName')) {
                            return $component->getName() !== 'dealer_code';
                        }
                        return true;
                    });

                    return array_values($filteredSchema);
                })
                ->mutateFormDataUsing(function (array $data): array {
                    $data['dealer_code'] = $this->Dealer->dealer_code;

                    if (empty($data['outlet_code'])) {
                        $data['outlet_code'] = 'Outlet_' . strtolower(Str::random(8));
                    }

                    return $data;
                }),
        ];
    }

    public function getViewData(): array
    {
        return [
            'Dealer' => $this->Dealer,
        ];
    }
}
