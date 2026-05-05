<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmployeeRegistrationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public User $employee,
        public string $temporaryPassword,
        public string $roleName,
        public string $subjectLine = 'Your Employee Account Is Ready',
    ) {
    }

    public function build(): self
    {
        return $this->subject($this->subjectLine)
            ->view('emails.employee.registration');
    }
}
