<?php

namespace App\Mail;

use App\Models\StudyMaterial;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EducatorNewMaterialMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array{follower_name: string, educator_name: string, title: string, material_type: string, material_url: string, profile_url: string}  $details
     */
    public function __construct(public array $details)
    {
    }

    public static function forFollower(StudyMaterial $material, string $followerName): self
    {
        $material->loadMissing('educator');

        return new self([
            'follower_name' => $followerName ?: 'there',
            'educator_name' => $material->educator?->display_name ?: 'Teacher / Tutor',
            'title' => $material->title,
            'material_type' => $material->materialTypeLabel(),
            'material_url' => $material->publicUrl(),
            'profile_url' => $material->educator?->publicUrl() ?: url('/teachers-tutors'),
        ]);
    }

    public function build(): self
    {
        return $this->subject($this->details['educator_name'].' posted new study material')
            ->view('emails.educator.new-material');
    }
}
