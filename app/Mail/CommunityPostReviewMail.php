<?php

namespace App\Mail;

use App\Models\CommunityPost;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommunityPostReviewMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public CommunityPost $post,
        public string $status,
        public string $subjectLine,
    ) {}

    public static function forPost(CommunityPost $post, string $status): self
    {
        $post->loadMissing('user');

        $normalizedStatus = $status === 'rejected' ? 'declined' : $status;

        return new self(
            post: $post,
            status: $normalizedStatus,
            subjectLine: match ($normalizedStatus) {
                'approved' => 'Your community post has been approved',
                'declined' => 'Community post update',
                default => 'Community post review update',
            },
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.community.post-review');
    }
}
