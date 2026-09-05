<?php

namespace App\Mail;

use App\Models\StudyMaterial;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StudyMaterialStatusMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array{title: string, educator_name: string, status: string, reason: ?string, materials_url: ?string}  $details
     */
    public function __construct(
        public array $details,
        public string $action,
        public string $subjectLine,
    ) {
    }

    public static function forMaterial(StudyMaterial $material, string $action, ?string $reason = null): self
    {
        $material->loadMissing('educator');

        $status = match ($action) {
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => ucfirst($action),
        };

        return new self(
            details: [
                'title' => $material->title,
                'educator_name' => $material->educator?->display_name ?: 'Educator',
                'status' => $status,
                'reason' => filled($reason) ? trim($reason) : null,
                'materials_url' => $action === 'approved' ? route('educator.materials.index') : route('educator.materials.index'),
            ],
            action: $action,
            subjectLine: match ($action) {
                'approved' => 'Your Study Material Has Been Approved',
                'rejected' => 'Study Material Update',
                default => 'Study Material Update',
            },
        );
    }

    public function build(): self
    {
        return $this->subject($this->subjectLine)
            ->view('emails.educator.study-material-status');
    }
}
