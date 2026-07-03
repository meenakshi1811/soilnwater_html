<?php

namespace App\Mail;

use App\Models\ListingPaymentSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ListingPaymentSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ListingPaymentSubmission $submission) {}

    public function envelope(): Envelope
    {
        $userName = $this->submission->user?->full_name ?: ($this->submission->user?->name ?? 'A user');

        return new Envelope(
            subject: $this->submission->listingTypeLabel().' payment proof submitted by '.$userName,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.listing.payment-submitted');
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $path = public_path($this->submission->screenshot_path);
        if (! is_file($path)) {
            return [];
        }

        return [
            Attachment::fromPath($path)
                ->as('payment-proof.'.pathinfo($path, PATHINFO_EXTENSION)),
        ];
    }
}
