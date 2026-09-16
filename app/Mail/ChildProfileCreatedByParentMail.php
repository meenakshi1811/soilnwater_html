<?php

namespace App\Mail;

use App\Models\ChildProfile;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ChildProfileCreatedByParentMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array{child_name: string, parent_name: string, status: string, dashboard_url: string, audience: string}  $details
     */
    public function __construct(
        public array $details,
        public string $subjectLine,
    ) {
    }

    public static function forParent(ChildProfile $childProfile, User $parent): self
    {
        $parentName = $parent->full_name ?: $parent->name ?: 'Parent';

        return new self(
            details: [
                'child_name' => $childProfile->full_name,
                'parent_name' => $parentName,
                'status' => 'Pending admin approval',
                'dashboard_url' => route('parent.dashboard'),
                'audience' => 'parent',
            ],
            subjectLine: 'Child profile submitted for approval — '.$childProfile->full_name,
        );
    }

    public function build(): self
    {
        return $this->subject($this->subjectLine)
            ->view('emails.child-profile.created');
    }
}
