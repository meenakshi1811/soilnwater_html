<?php

namespace App\Mail;

use App\Models\ServiceProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServiceProviderPublicPageApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ServiceProvider $serviceProvider)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your public service page has been approved');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.service_provider.public-page-approved');
    }
}
