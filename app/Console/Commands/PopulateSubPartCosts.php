<?php

namespace App\Console\Commands;

use App\Models\SubPart;
use Illuminate\Console\Command;

class PopulateSubPartCosts extends Command
{
    protected $signature = 'subparts:populate-cost';
    protected $description = 'Populates the cost for existing sub_parts based on their price.';

    public function handle()
    {
        $this->info('Starting to populate costs for sub_parts...');

        // Ambil semua sub_parts yang harga modalnya masih 0
        $subPartsToUpdate = SubPart::where('cost', 0)->get();

        if ($subPartsToUpdate->isEmpty()) {
            $this->info('No sub_parts needed an update. All costs are already populated.');
            return 0;
        }

        $progressBar = $this->output->createProgressBar($subPartsToUpdate->count());
        $progressBar->start();

        // Asumsi margin keuntungan 25%, jadi harga modal adalah 75% dari harga jual
        $costPercentage = 0.75; 

        foreach ($subPartsToUpdate as $subPart) {
            $calculatedCost = $subPart->price * $costPercentage;
            $subPart->update(['cost' => $calculatedCost]);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->info("\nSuccessfully populated costs for " . $subPartsToUpdate->count() . " sub_parts.");
        return 0;
    }
}
