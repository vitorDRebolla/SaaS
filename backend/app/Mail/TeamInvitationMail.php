<?php
namespace App\Mail;
use App\Models\TeamInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeamInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public TeamInvitation $invitation) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "You've been invited to join {$this->invitation->team->name} on Meridian");
    }

    public function content(): Content
    {
        $acceptUrl = config('app.frontend_url') . '/invitations/' . $this->invitation->token;

        return new Content(
            view: 'emails.team-invitation',
            with: [
                'teamName' => $this->invitation->team->name,
                'role' => $this->invitation->role,
                'acceptUrl' => $acceptUrl,
                'expiresAt' => $this->invitation->expires_at->format('M j, Y'),
            ]
        );
    }
}
