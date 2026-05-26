<?php
namespace App\Jobs;
use App\Mail\TeamInvitationMail;
use App\Models\TeamInvitation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendTeamInvitationEmail implements ShouldQueue
{
    use Queueable;

    public function __construct(public TeamInvitation $invitation) {}

    public function handle(): void
    {
        Mail::to($this->invitation->email)->send(new TeamInvitationMail($this->invitation));
    }
}
