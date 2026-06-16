<?php

namespace App\Mail;

use App\Models\CommunityAuthorQuestion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommunityAuthorQuestionReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public CommunityAuthorQuestion $question) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New question from a community reader');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.community.author-question-received');
    }
}
