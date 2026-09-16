<?php

namespace App\Mail;

use App\Models\Institute;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InstituteStatusMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array{institution_name: string, contact_person: ?string, email: ?string, status: string, profile_url: ?string, reason: ?string}  $instituteDetails
     */
    public function __construct(
        public array $instituteDetails,
        public string $action,
        public string $subjectLine,
    ) {
    }

    public static function forInstitute(Institute $institute, string $action, ?string $reason = null): self
    {
        $institute->loadMissing('user');

        $institutionName = $institute->institution_name ?: $institute->user?->name ?: 'Institute';
        $status = match ($action) {
            'pending' => 'Under observation',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'deleted' => 'Deleted',
            default => ucfirst($action),
        };

        return new self(
            instituteDetails: [
                'institution_name' => $institutionName,
                'contact_person' => $institute->contact_person ?: $institute->user?->name,
                'email' => $institute->email ?: $institute->user?->email,
                'status' => $status,
                'profile_url' => $action === 'approved' ? $institute->publicUrl() : null,
                'reason' => filled($reason) ? trim($reason) : null,
            ],
            action: $action,
            subjectLine: match ($action) {
                'pending' => 'Welcome to SoilNWater - Your School / Institute Profile Is Under Observation',
                'approved' => 'Your School / Institute Account Has Been Approved',
                'rejected' => 'School / Institute Application Update',
                'deleted' => 'School / Institute Account Removed',
                default => 'School / Institute Account Update',
            },
        );
    }

    public function build(): self
    {
        return $this->subject($this->subjectLine)
            ->view('emails.institute.status');
    }
}
