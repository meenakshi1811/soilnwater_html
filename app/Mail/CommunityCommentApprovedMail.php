<?php

namespace App\Mail;

use App\Models\CommunityPost;
use App\Models\CommunityPostComment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommunityCommentApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public CommunityPost $post,
        public CommunityPostComment $comment,
        public User $recipient,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your comment is now live on "'.$this->post->title.'"');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.community.comment-approved');
    }
}
