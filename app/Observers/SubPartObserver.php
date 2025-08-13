<?php

namespace App\Observers;

use App\Models\SubPart;
use App\Models\MasterPart;

class SubPartObserver
{
    /**
     * Handle events after a SubPart has been created, updated, or restored.
     * The "saved" event covers both "created" and "updated".
     */
    public function saved(SubPart $subPart): void
    {
        $this->updateMasterPartPrice($subPart->masterPart);
    }

    /**
     * Handle the "deleted" event.
     */
    public function deleted(SubPart $subPart): void
    {
        $this->updateMasterPartPrice($subPart->masterPart);
    }

    /**
     * Handle the "restored" event.
     */
    public function restored(SubPart $subPart): void
    {
        $this->updateMasterPartPrice($subPart->masterPart);
    }

    /**
     * Recalculate and update the total price of the master part.
     *
     * @param MasterPart|null $masterPart The master part associated with the sub-part.
     * @return void
     */
    protected function updateMasterPartPrice(?MasterPart $masterPart): void
    {
        // Pastikan master part ada
        if ($masterPart) {
        // Hitung total harga jual (price)
        $totalPrice = $masterPart->subParts()->sum('price');

        // Hitung total harga modal (cost)
        $totalCost = $masterPart->subParts()->sum('cost');

        // Update kedua kolom di master_part
        $masterPart->updateQuietly([
            'part_price' => $totalPrice,
            'total_cost' => $totalCost,
        ]);
        }
    }
}
