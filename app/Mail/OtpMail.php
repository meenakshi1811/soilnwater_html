<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $otpCode,
        public string $subjectLine,
        public string $headline,
        public string $contextLine,
        public int $expiresInMinutes = 5,
    ) {
    }

    public function build(): self
    {
        return $this->subject($this->subjectLine)
            ->view('emails.otp.code');
    }
}
