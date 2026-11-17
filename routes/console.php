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

// Push notifications to the app (Firebase). A test to one device, or an announcement to every
// device, each opening the page given as --url:
//   php artisan push:send "New Teacher Listed" "View the teacher's profile on SoilnWater." --url=/teachers-tutors/archana-aggarwal --token=<device token>
//   php artisan push:send "New Teacher Listed" "View the teacher's profile on SoilnWater." --url=/teachers-tutors/archana-aggarwal --all
Artisan::command('push:send {title} {body} {--url= : Page to open (full address or a path like /offers-market)} {--token= : One device token} {--user= : A user id (all their devices)} {--all : Every registered device}', function () {
    if (! \App\Services\PushNotificationService::isConfigured()) {
        $this->error('Firebase is not set up: put the service account key file on the server and its path in FIREBASE_CREDENTIALS in .env.');

        return 1;
    }

    $title = (string) $this->argument('title');
    $body = (string) $this->argument('body');
    $url = $this->option('url');

    if ($this->option('token')) {
        $result = \App\Services\PushNotificationService::sendToTokens([(string) $this->option('token')], $title, $body, $url);
    } elseif ($this->option('user')) {
        $tokens = \App\Models\DeviceToken::query()->where('user_id', (int) $this->option('user'))->pluck('token')->all();
        $result = \App\Services\PushNotificationService::sendToTokens($tokens, $title, $body, $url);
    } elseif ($this->option('all')) {
        if (! $this->confirm('Send this to every registered device ('.\App\Models\DeviceToken::query()->count().')?', true)) {
            return 0;
        }
        $result = \App\Services\PushNotificationService::toAllDevices($title, $body, $url);
    } else {
        $this->error('Say who to send it to: --token=, --user= or --all.');

        return 1;
    }

    $this->info("Sent: {$result['sent']}, failed: {$result['failed']}, invalid tokens removed: {$result['removed']}.");
    $this->line('Data sent with it: '.json_encode(\App\Services\PushNotificationService::dataPayload($url), JSON_UNESCAPED_SLASHES));

    return 0;
})->purpose('Send a push notification to the app (with the page to open as data.url)');
