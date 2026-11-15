<?php

namespace App\Console\Commands;

use App\Support\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/** Register images already on the public disk (imported / uploaded before the library existed). */
class SyncMedia extends Command
{
    protected $signature = 'berry:media-sync';

    protected $description = 'Add existing images under storage/uploads to the media library';

    public function handle(): int
    {
        $added = 0;
        foreach (Storage::disk('public')->allFiles('uploads') as $path) {
            if (Media::register($path)) {
                $added++;
            }
        }
        $this->info("Added {$added} image(s) to the media library.");

        return self::SUCCESS;
    }
}
