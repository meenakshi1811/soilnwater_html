<?php

use App\Support\ImageThumbnail;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run after deploying: makes the small homepage copies of the images up front, so the first
// visitors don't wait for them to be made (see App\Support\ImageThumbnail)
Artisan::command('study-materials:locate', function () {
    \App\Support\StudyMaterialLocation::flush();
    $count = 0;
    \App\Models\StudyMaterial::query()->with(['educator', 'user'])->orderBy('id')->chunkById(200, function ($materials) use (&$count) {
        foreach ($materials as $material) {
            \App\Support\StudyMaterialLocation::fill($material);
            if ($material->isDirty()) {
                $material->saveQuietly();
                $count++;
            }
        }
    });
    $this->info("Updated the school and city of {$count} study materials.");
})->purpose('Work out the school and city of every study material again');

Artisan::command('images:thumbnails {dirs?* : Folders under public/ (default: the homepage images)}', function () {
    $dirs = $this->argument('dirs') ?: ['uploads/homepage', 'assets/images/homepage-seed'];
    $ready = 0;
    $skipped = 0;

    foreach ($dirs as $dir) {
        $dir = trim(str_replace('\\', '/', $dir), '/');
        if (! is_dir(public_path($dir))) {
            $this->warn("No folder public/{$dir}");

            continue;
        }

        foreach (File::allFiles(public_path($dir)) as $file) {
            if (! preg_match('/\.(png|jpe?g|webp|gif)$/i', $file->getFilename())) {
                continue;
            }

            $path = $dir.'/'.str_replace('\\', '/', $file->getRelativePathname());
            ImageThumbnail::prepare($path) ? $ready++ : $skipped++;
        }
    }

    $this->info("{$ready} small copies ready, {$skipped} images left as they are (already small or not resizable).");
})->purpose('Make the small homepage copies of images (run after deploying)');
