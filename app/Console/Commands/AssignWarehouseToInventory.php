<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inventory;
use App\Models\Warehouse;

class AssignWarehouseToInventory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:assign-warehouse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign a warehouse_id to inventory records where it is null';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to assign warehouses to inventory...');

        // 1. Ambil semua ID gudang yang valid dari tabel warehouses.
        $warehouseIds = Warehouse::pluck('id')->toArray();

        if (empty($warehouseIds)) {
            $this->error('No warehouses found in the warehouses table. Please seed the warehouses first.');
            return 1; // Keluar dengan error
        }

        // 2. Ambil semua record inventaris yang warehouse_id-nya masih NULL.
        $inventoriesToUpdate = Inventory::whereNull('warehouse_id')->get();

        if ($inventoriesToUpdate->isEmpty()) {
            $this->info('No inventory records need an update. All records already have a warehouse_id.');
            return 0; // Selesai dengan sukses
        }

        $progressBar = $this->output->createProgressBar($inventoriesToUpdate->count());
        $progressBar->start();

        // 3. Loop setiap record dan tetapkan warehouse_id secara acak.
        foreach ($inventoriesToUpdate as $inventory) {
            $randomWarehouseId = $warehouseIds[array_rand($warehouseIds)];
            $inventory->update(['warehouse_id' => $randomWarehouseId]);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->info("\n\nSuccessfully assigned warehouse IDs to " . $inventoriesToUpdate->count() . " inventory records.");
        return 0; // Selesai dengan sukses
    }
}