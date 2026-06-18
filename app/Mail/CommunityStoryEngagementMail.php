<?php

namespace App\Mail;

use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommunityStoryEngagementMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  list<string>|null  $badgeLabels
     */
    public function __construct(
        public CommunityPost $post,
        public ?User $actor,
        public string $engagementType,
        public string $summary,
        public ?string $actionUrl = null,
        public ?array $badgeLabels = null,
        public ?int $rating = null,
    ) {}

    public function envelope(): Envelope
    {
        $contentLabel = match ($this->post->content_type) {
            'poetry' => 'poem',
            'stories' => 'story',
            'autobiography' => 'autobiography',
            default => 'community post',
        };

        return new Envelope(subject: $this->engagementType.' on your community '.$contentLabel);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.community.story-engagement');
    }
}
