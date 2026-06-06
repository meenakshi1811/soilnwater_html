<?php

namespace App\Mail;

use App\Models\Consultant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConsultantPublicPageApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Consultant $consultant) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your public consultant page has been approved');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.consultant.public-page-approved');
    }
}
