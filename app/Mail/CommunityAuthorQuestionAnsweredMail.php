<?php

namespace App\Mail;

use App\Models\CommunityAuthorQuestion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommunityAuthorQuestionAnsweredMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public CommunityAuthorQuestion $question) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your question to the author was answered');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.community.author-question-answered');
    }
}
