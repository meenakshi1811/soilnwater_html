<?php

namespace App\Services;

use App\Mail\CommunityPostParticipationReceivedMail;
use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CommunityPostParticipationNotificationService
{
    public static function notifyAuthorOfComment(CommunityPost $post, User $participant, string $body, bool $isReply = false): void
    {
        $type = $isReply ? 'discussion reply' : 'comment';

        self::notifyAuthor(
            $post,
            $participant,
            $isReply ? 'New discussion reply' : 'New public comment',
            ucfirst($type),
            Str::limit($body, 160),
            route('community.show', $post).'#public-participation'
        );
    }

    public static function notifyAuthorOfSuggestion(CommunityPost $post, User $participant, string $body): void
    {
        self::notifyAuthor(
            $post,
            $participant,
            'New public suggestion',
            'Suggestion',
            Str::limit($body, 160),
            route('community.show', $post).'#public-participation'
        );
    }

    public static function notifyAuthorOfFeedback(CommunityPost $post, User $participant, string $body): void
    {
        self::notifyAuthor(
            $post,
            $participant,
            'New public feedback',
            'Feedback',
            Str::limit($body, 160),
            route('community.show', $post).'#public-participation'
        );
    }

    public static function notifyAuthorOfEvidence(CommunityPost $post, User $participant, int $fileCount, ?string $note = null): void
    {
        $filesLabel = $fileCount === 1 ? '1 file' : $fileCount.' files';
        $summary = 'Uploaded '.$filesLabel.' of additional evidence';
        if (filled($note)) {
            $summary .= ': '.Str::limit($note, 120);
        }

        self::notifyAuthor(
            $post,
            $participant,
            'New additional evidence',
            'Additional evidence',
            $summary,
            route('community.show', $post).'#public-participation'
        );
    }

    private static function notifyAuthor(
        CommunityPost $post,
        User $participant,
        string $title,
        string $participationType,
        string $summary,
        ?string $url = null,
    ): void {
        $post->loadMissing('user');

        if (! $post->user || $post->user_id === $participant->id) {
            return;
        }

        $participantName = $participant->full_name ?: $participant->name ?: 'A community member';
        $message = $participantName.' submitted '.$participationType.' on "'.$post->title.'": '.$summary;

        PortalNotificationService::notifyUser(
            $post->user,
            $title,
            $message,
            $url ?? route('community.show', $post),
            'community'
        );

        $recipient = $post->user->email;
        if (! filled($recipient)) {
            return;
        }

        Mail::to($recipient)->send(new CommunityPostParticipationReceivedMail(
            $post,
            $participant,
            $participationType,
            $summary,
            $url
        ));
    }
}
