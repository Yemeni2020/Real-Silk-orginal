<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class AliExpressBackfillThumbnailsCommand extends Command
{
    protected $signature = 'aliexpress:backfill-thumbnails {--dry-run : Show count only without updating}';

    protected $description = 'Fill missing thumbnail for AliExpress imported products using first image from images JSON';

    public function handle(): int
    {
        $query = Product::query()
            ->where('code', 'like', 'AE-%')
            ->where(function ($q) {
                $q->whereNull('thumbnail')->orWhere('thumbnail', '');
            })
            ->whereNotNull('images');

        $products = $query->get(['id', 'code', 'thumbnail', 'images']);
        $updated = 0;

        foreach ($products as $product) {
            $images = json_decode((string) $product->images, true);
            if (!is_array($images) || empty($images[0])) {
                continue;
            }

            $first = $images[0];
            $imageName = null;
            if (is_array($first) && !empty($first['image_name'])) {
                $imageName = (string) $first['image_name'];
            } elseif (is_string($first) && $first !== '') {
                $imageName = $first;
            }

            if (!$imageName) {
                continue;
            }

            if (!$this->option('dry-run')) {
                $product->update([
                    'thumbnail' => $imageName,
                    'meta_image' => $product->meta_image ?: $imageName,
                    'thumbnail_storage_type' => $product->thumbnail_storage_type ?: 'public',
                ]);
            }
            $updated++;
        }

        $message = $this->option('dry-run')
            ? 'Products that can be fixed: '
            : 'Products updated: ';

        $this->info($message . $updated);

        return self::SUCCESS;
    }
}
