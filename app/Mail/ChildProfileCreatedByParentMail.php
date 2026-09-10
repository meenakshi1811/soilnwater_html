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
     * @param  array{child_name: string, parent_name: string, email: string, status: string, login_url: string}  $details
     */
    public function __construct(
        public array $details,
        public string $subjectLine,
    ) {
    }

    public static function forChild(ChildProfile $childProfile, User $parent): self
    {
        $parentName = $parent->full_name ?: $parent->name ?: 'Your parent/guardian';

        return new self(
            details: [
                'child_name' => $childProfile->full_name,
                'parent_name' => $parentName,
                'email' => $childProfile->email,
                'status' => 'Pending admin approval',
                'login_url' => route('login'),
            ],
            subjectLine: 'Your child profile has been created on SoilNWater',
        );
    }

    public function build(): self
    {
        return $this->subject($this->subjectLine)
            ->view('emails.child-profile.created');
    }
}
