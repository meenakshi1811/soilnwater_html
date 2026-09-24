<?php

namespace App\Mail;

use App\Models\InstituteJobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InstituteJobApplicationStatusMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public InstituteJobApplication $application,
        public string $institutionName,
    ) {
    }

    public static function forApplication(InstituteJobApplication $application): self
    {
        $application->loadMissing(['job.institute', 'user']);

        return new self(
            application: $application,
            institutionName: $application->job?->institute?->displayName() ?? 'Institute',
        );
    }

    public function build(): self
    {
        return $this->subject('Update on your job application · '.$this->application->job?->title)
            ->view('emails.institute.job-application-status');
    }
}
