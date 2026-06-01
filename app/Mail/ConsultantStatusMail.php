<?php

namespace App\Mail;

use App\Models\Consultant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConsultantStatusMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array{company_name: string, contact_person: ?string, email: ?string, status: string, consultant_url: ?string}  $consultantDetails
     */
    public function __construct(
        public array $consultantDetails,
        public string $action,
        public string $subjectLine,
    ) {
    }

    public static function forConsultant(Consultant $consultant, string $action): self
    {
        $consultant->loadMissing('user');

        $companyName = $consultant->company_name ?: $consultant->user?->name ?: 'Consultant';
        $status = match ($action) {
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'deleted' => 'Deleted',
            default => ucfirst($action),
        };

        return new self(
            consultantDetails: [
                'company_name' => $companyName,
                'contact_person' => $consultant->contact_person ?: $consultant->user?->name,
                'email' => $consultant->email ?: $consultant->user?->email,
                'status' => $status,
                'consultant_url' => $action === 'approved' ? $consultant->consultantUrl() : null,
            ],
            action: $action,
            subjectLine: match ($action) {
                'approved' => 'Your Consultant Account Has Been Approved',
                'rejected' => 'Consultant Application Update',
                'deleted' => 'Consultant Account Removed',
                default => 'Consultant Account Update',
            },
        );
    }

    public function build(): self
    {
        return $this->subject($this->subjectLine)
            ->view('emails.consultant.status');
    }
}
