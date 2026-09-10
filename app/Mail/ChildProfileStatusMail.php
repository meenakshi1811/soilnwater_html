<?php

namespace App\Mail;

use App\Models\ChildProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ChildProfileStatusMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array{recipient_name: string, child_name: string, parent_name: ?string, status: string, reason: ?string, login_url: ?string, dashboard_url: ?string, audience: string}  $details
     */
    public function __construct(
        public array $details,
        public string $subjectLine,
    ) {
    }

    public static function forParent(ChildProfile $childProfile, string $action, ?string $reason = null): self
    {
        $parent = $childProfile->parentUser;
        $status = $action === 'approved' ? 'Approved' : 'Declined';

        return new self(
            details: [
                'recipient_name' => $parent?->full_name ?: $parent?->name ?: 'Parent',
                'child_name' => $childProfile->full_name,
                'parent_name' => null,
                'status' => $status,
                'reason' => $reason,
                'login_url' => null,
                'dashboard_url' => route('parent.dashboard'),
                'audience' => 'parent',
            ],
            subjectLine: 'Child profile '.$status.' — '.$childProfile->full_name,
        );
    }

    public static function forChild(ChildProfile $childProfile, string $action, ?string $reason = null): self
    {
        $parentName = $childProfile->parentUser?->full_name ?: $childProfile->parentUser?->name;
        $status = $action === 'approved' ? 'Approved' : 'Declined';

        return new self(
            details: [
                'recipient_name' => $childProfile->full_name,
                'child_name' => $childProfile->full_name,
                'parent_name' => $parentName,
                'status' => $status,
                'reason' => $reason,
                'login_url' => $action === 'approved' ? route('login') : null,
                'dashboard_url' => null,
                'audience' => 'child',
            ],
            subjectLine: $action === 'approved'
                ? 'Your SoilNWater child account has been approved'
                : 'Update on your SoilNWater child profile',
        );
    }

    public function build(): self
    {
        return $this->subject($this->subjectLine)
            ->view('emails.child-profile.status');
    }
}
