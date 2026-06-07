<?php

namespace App\Jobs;

use App\Models\AliExpressProduct;
use App\Services\AliExpress\AliExpressProductSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AliExpressSyncProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 120;

    public function __construct(public int $aliExpressProductRowId)
    {
    }

    public function handle(AliExpressProductSyncService $syncService): void
    {
        $product = AliExpressProduct::query()->find($this->aliExpressProductRowId);
        if (!$product) {
            return;
        }

        $syncService->sync($product);
    }
}
