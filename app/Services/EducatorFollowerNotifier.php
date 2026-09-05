<?php

namespace App\Services;

use App\Mail\EducatorNewMaterialMail;
use App\Models\StudyMaterial;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EducatorFollowerNotifier
{
    public static function notifyFollowersOfNewMaterial(StudyMaterial $material): void
    {
        $material->loadMissing(['educator.followers', 'educator.user']);

        $educator = $material->educator;
        if (! $educator) {
            return;
        }

        $followers = $educator->followers;
        if ($followers->isEmpty()) {
            return;
        }

        $ownerId = (int) ($educator->user_id ?: 0);
        $url = $material->publicUrl();
        $title = $educator->display_name.' posted new study material';
        $message = '"'.$material->title.'" is now available from '.$educator->display_name.'.';

        foreach ($followers as $follower) {
            if (! $follower || (int) $follower->id === $ownerId) {
                continue;
            }

            PortalNotificationService::notifyUser(
                $follower,
                $title,
                $message,
                $url,
                'engagement'
            );

            if (! filled($follower->email)) {
                continue;
            }

            try {
                Mail::to($follower->email)->send(
                    EducatorNewMaterialMail::forFollower($material, (string) $follower->name)
                );
            } catch (\Throwable $e) {
                Log::error('Failed to send educator new material mail to follower', [
                    'follower_id' => $follower->id,
                    'material_id' => $material->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
