<?php

namespace App\Mail;

use App\Models\Educator;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EducatorStatusMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array{display_name: string, email: ?string, status: string, profile_url: ?string, reason: ?string, role_label: string}  $educatorDetails
     */
    public function __construct(
        public array $educatorDetails,
        public string $action,
        public string $subjectLine,
    ) {
    }

    public static function forEducator(Educator $educator, string $action, ?string $reason = null): self
    {
        $educator->loadMissing('user');
        $roleLabel = $educator->roleLabel();
        $displayName = $educator->display_name ?: $educator->user?->name ?: $roleLabel;

        $status = match ($action) {
            'pending' => 'Under observation',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'deleted' => 'Deleted',
            default => ucfirst($action),
        };

        return new self(
            educatorDetails: [
                'display_name' => $displayName,
                'email' => $educator->email ?: $educator->user?->email,
                'status' => $status,
                'profile_url' => $action === 'approved' ? $educator->publicUrl() : null,
                'reason' => filled($reason) ? trim($reason) : null,
                'role_label' => $roleLabel,
            ],
            action: $action,
            subjectLine: match ($action) {
                'pending' => "Welcome to SoilNWater - Your {$roleLabel} Profile Is Under Observation",
                'approved' => "Your {$roleLabel} Account Has Been Approved",
                'rejected' => "{$roleLabel} Application Update",
                'deleted' => "{$roleLabel} Account Removed",
                default => "{$roleLabel} Account Update",
            },
        );
    }

    public function build(): self
    {
        return $this->subject($this->subjectLine)
            ->view('emails.educator.status');
    }
}
