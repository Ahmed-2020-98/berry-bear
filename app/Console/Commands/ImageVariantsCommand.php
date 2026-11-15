<?php

namespace App\Console\Commands;

use App\Models\MediaItem;
use App\Support\ImageVariants;
use Illuminate\Console\Command;

class ImageVariantsCommand extends Command
{
    protected $signature = 'berry:image-variants {--fresh : Delete existing variants first}';

    protected $description = 'Generate resized WebP copies of every library image (used by srcset).';

    public function handle(): int
    {
        if (! ImageVariants::supported()) {
            $this->error('GD with WebP support is not available — images will be served at their original size.');

            return self::FAILURE;
        }

        $images = 0;
        $variants = 0;
        MediaItem::query()->orderBy('id')->each(function (MediaItem $m) use (&$images, &$variants) {
            if ($this->option('fresh')) {
                ImageVariants::purge($m->path);
            }
            $variants += ImageVariants::warm($m->path);
            $images++;
        });

        $this->info("Images: {$images}, variants available: {$variants}");

        return self::SUCCESS;
    }
}
