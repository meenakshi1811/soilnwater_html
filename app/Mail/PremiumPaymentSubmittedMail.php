<?php

namespace App\Mail;

use App\Models\PremiumPaymentSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PremiumPaymentSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PremiumPaymentSubmission $submission) {}

    public function envelope(): Envelope
    {
        $userName = $this->submission->user?->full_name ?: ($this->submission->user?->name ?? 'A user');

        return new Envelope(
            subject: 'Premium payment proof submitted by '.$userName,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.premium.payment-submitted');
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
