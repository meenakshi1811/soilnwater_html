<?php

namespace App\Mail;

use App\Models\EducatorEnquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EducatorEnquiryReceivedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array{educator_name: string, name: string, email: ?string, phone: ?string, subject: ?string, message: string, enquiries_url: string}  $details
     */
    public function __construct(public array $details)
    {
    }

    public static function forEnquiry(EducatorEnquiry $enquiry): self
    {
        $enquiry->loadMissing('educator');

        return new self([
            'educator_name' => $enquiry->educator?->display_name ?: 'Educator',
            'name' => $enquiry->name,
            'email' => $enquiry->email,
            'phone' => $enquiry->phone,
            'subject' => $enquiry->subject,
            'message' => $enquiry->message,
            'enquiries_url' => route('educator.enquiries.index'),
        ]);
    }

    public function build(): self
    {
        return $this->subject('New enquiry on your Teacher / Tutor profile')
            ->view('emails.educator.enquiry-received');
    }
}
