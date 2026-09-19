<?php

namespace App\Mail;

use App\Models\Institute;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InstituteNewFollowerMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Institute $institute,
        public ?User $follower,
        public string $engagementPortalUrl,
    ) {
    }

    public static function forFollow(Institute $institute, ?User $follower): self
    {
        $institute->loadMissing('user');
        $portalPrefix = $institute->user?->portalRoutePrefix() ?? 'school';
        $url = $institute->user?->portalRoute('engagement.index')
            ?? \App\Support\SchoolInstituteHelper::routeForPrefix($portalPrefix, 'engagement.index');

        return new self($institute, $follower, $url);
    }

    public function build(): self
    {
        return $this->subject('New follower on '.$this->institute->displayName())
            ->view('emails.institute.new-follower');
    }
}
