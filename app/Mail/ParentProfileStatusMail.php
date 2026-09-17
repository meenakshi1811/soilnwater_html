<?php

namespace App\Mail;

use App\Models\ParentProfile;
use App\Models\User;
use App\Services\ParentRegistrationService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ParentProfileStatusMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array{display_name: string, email: ?string, status: string, reason: ?string}  $profileDetails
     */
    public function __construct(
        public array $profileDetails,
        public string $action,
        public string $subjectLine,
    ) {
    }

    public static function forProfile(ParentProfile $parentProfile, string $action, ?string $reason = null): self
    {
        $parentProfile->loadMissing('user');
        $user = $parentProfile->user;

        $displayName = $user
            ? ParentRegistrationService::displayName($user)
            : 'Parent profile';

        $status = match ($action) {
            'pending' => 'Under observation',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => ucfirst($action),
        };

        return new self(
            profileDetails: [
                'display_name' => $displayName,
                'email' => $user?->email,
                'status' => $status,
                'reason' => filled($reason) ? trim($reason) : null,
            ],
            action: $action,
            subjectLine: match ($action) {
                'pending' => 'Welcome to SoilNWater - Your Parent Profile Is Under Observation',
                'approved' => 'Your Parent Profile Has Been Approved',
                'rejected' => 'Parent Profile Application Update',
                default => 'Parent Profile Update',
            },
        );
    }

    public function build(): self
    {
        return $this->subject($this->subjectLine)
            ->view('emails.parent-profile.status');
    }
}
