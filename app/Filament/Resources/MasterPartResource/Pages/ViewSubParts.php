<?php

namespace App\Filament\Resources\MasterPartResource\Pages;

use App\Filament\Resources\MasterPartResource;
use App\Filament\Resources\SubPartResource;
use App\Models\MasterPart;
use App\Models\SubPart;
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

class ViewSubParts extends BaseResourcePage implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static string $resource = MasterPartResource::class;
    protected static string $view = 'filament.resources.master-part-resource.pages.view-sub-parts';

    public ?MasterPart $masterPart = null;

    public function mount($part_number): void
    {
        $this->masterPart = MasterPart::with('subParts')->where('part_number', $part_number)->firstOrFail();
    }

    protected function getTableQuery(): Builder
    {
        return $this->masterPart->subParts()->getQuery();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('sub_part_number')
                ->label('Sub Part Number')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('sub_part_name')
                ->label('Sub Part Name')
                ->searchable(),
            Tables\Columns\TextColumn::make('price')
                ->money('IDR')
                ->sortable(),
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('updated_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\EditAction::make()
                ->url(fn (SubPart $record): string => SubPartResource::getUrl('edit', ['record' => $record->sub_part_number])),
            Tables\Actions\DeleteAction::make(),
        ];
    }

    protected function getTableHeaderActions(): array
    {
        return [
            Tables\Actions\CreateAction::make()
                ->label('Add New Sub Part')
                ->model(SubPart::class)
                ->form(function (Forms\Form $form) { // $form is a fresh Filament\Forms\Form instance
                    // Call the static form method from SubPartResource, passing the fresh $form instance.
                    // This configures $form with the schema defined in SubPartResource.
                    $configuredForm = SubPartResource::form($form);

                    // Get the array of components (the schema) from the configured form instance.
                    $schemaComponents = $configuredForm->getComponents();

                    // Filter out the 'part_number' component, as we'll set it via mutateFormDataUsing.
                    // This assumes 'part_number' is a top-level component in the schema.
                    $filteredSchema = array_filter($schemaComponents, function($component) {
                        // Check if the component has a getName method (most form fields do)
                        if (method_exists($component, 'getName')) {
                            return $component->getName() !== 'part_number';
                        }
                        // Keep components that might not have a name (e.g., layout components like Grid, Section)
                        return true;
                    });

                    // Re-index the array after filtering to prevent issues with non-sequential keys.
                    return array_values($filteredSchema);
                })
                ->mutateFormDataUsing(function (array $data): array {
                    $data['part_number'] = $this->masterPart->part_number;
                    // Ensure sub_part_number is unique or handle generation
                    if (empty($data['sub_part_number'])) {
                         $data['sub_part_number'] = 'subPART-' . strtoupper(Str::random(8));
                         // Add a loop here to ensure true uniqueness if auto-generating:
                         // while (SubPart::where('sub_part_number', $data['sub_part_number'])->exists()) {
                         //     $data['sub_part_number'] = 'subPART-' . strtoupper(Str::random(8));
                         // }
                    }
                    return $data;
                }),
        ];
    }

    public function getViewData(): array
    {
        return [
            'masterPart' => $this->masterPart,
        ];
    }
}