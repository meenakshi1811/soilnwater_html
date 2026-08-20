<?php

namespace App\Mail;

use App\Models\DiscussionGroupInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DiscussionGroupInvitationResponseMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public DiscussionGroupInvitation $invitation)
    {
        $this->invitation->loadMissing(['topic', 'inviter', 'invitee']);
    }

    public function envelope(): Envelope
    {
        $groupTitle = $this->invitation->topic?->title ?: 'your group';
        $action = $this->invitation->status === DiscussionGroupInvitation::STATUS_ACCEPTED
            ? 'accepted'
            : 'declined';

        return new Envelope(subject: 'Your group invitation was '.$action.' for '.$groupTitle);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.discussions.group-invitation-response');
    }
}
