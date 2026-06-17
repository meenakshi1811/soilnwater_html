<?php

namespace App\Mail;

use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommunityPostParticipationReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public CommunityPost $post,
        public User $participant,
        public string $participationType,
        public string $summary,
        public ?string $actionUrl = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New '.$this->participationType.' on your community post');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.community.participation-received');
    }
}
