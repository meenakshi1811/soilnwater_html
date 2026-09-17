<?php

namespace App\Mail;

use App\Models\EducatorEnquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EducatorEnquiryAnsweredMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array{asker_name: string, educator_name: string, subject: ?string, question: string, answer: string, profile_url: string}  $details
     */
    public function __construct(public array $details)
    {
    }

    public static function forEnquiry(EducatorEnquiry $enquiry): self
    {
        $enquiry->loadMissing('educator');

        return new self([
            'asker_name' => $enquiry->name ?: 'there',
            'educator_name' => $enquiry->educator?->display_name ?: 'The educator',
            'subject' => $enquiry->subject,
            'question' => $enquiry->message,
            'answer' => (string) $enquiry->answer,
            'profile_url' => $enquiry->profileUrl(),
        ]);
    }

    public function build(): self
    {
        return $this->subject('Your question to the teacher was answered')
            ->view('emails.educator.enquiry-answered');
    }
}
