<?php

namespace App\Listeners;

use App\Events\SellCreatedOrModified;
use App\Services\TableOrderService;

class SyncTableOccupancyFromSale
{
    public function handle(SellCreatedOrModified $event)
    {
        try {
            app(TableOrderService::class)->syncFromSale($event->transaction);
        } catch (\Throwable $e) {
            \Log::debug('Table occupancy sync skipped: '.$e->getMessage());
        }
    }
}
