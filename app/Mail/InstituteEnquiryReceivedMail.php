<?php

namespace App\Mail;

use App\Models\InstituteEnquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InstituteEnquiryReceivedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public InstituteEnquiry $enquiry,
        public string $institutionName,
    ) {
    }

    public static function forEnquiry(InstituteEnquiry $enquiry): self
    {
        $enquiry->loadMissing('institute');

        return new self(
            enquiry: $enquiry,
            institutionName: $enquiry->institute?->displayName() ?? 'Institute',
        );
    }

    public function build(): self
    {
        return $this->subject('New enquiry for '.$this->institutionName)
            ->view('emails.institute.enquiry-received');
    }
}
