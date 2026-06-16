<?php

namespace App\Mail;

use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommunityPostSubscriptionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public CommunityPost $post,
        public User $subscriber,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New post in a category or topic you follow',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.community.subscription-update');
    }
}
