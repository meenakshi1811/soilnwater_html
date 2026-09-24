<?php

namespace App\Mail;

use App\Models\InstituteJobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InstituteJobApplicationReceivedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public InstituteJobApplication $application,
        public string $institutionName,
        public string $jobsPortalUrl,
    ) {
    }

    public static function forApplication(InstituteJobApplication $application, string $jobsPortalUrl): self
    {
        $application->loadMissing(['job.institute', 'user']);

        return new self(
            application: $application,
            institutionName: $application->job?->institute?->displayName() ?? 'Institute',
            jobsPortalUrl: $jobsPortalUrl,
        );
    }

    public function build(): self
    {
        return $this->subject('New job application · '.$this->application->job?->title)
            ->view('emails.institute.job-application-received');
    }
}
