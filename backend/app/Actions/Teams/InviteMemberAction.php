<?php
namespace App\Actions\Teams;
use App\Jobs\SendTeamInvitationEmail;
use App\Models\Team;
use App\Models\TeamInvitation;
use Illuminate\Support\Str;

class InviteMemberAction
{
    public function execute(Team $team, string $email, string $role): TeamInvitation
    {
        $team->invitations()->where('email', $email)->delete();

        $invitation = TeamInvitation::create([
            'team_id' => $team->id,
            'email' => $email,
            'role' => $role,
            'token' => Str::random(40),
            'expires_at' => now()->addDays(7),
        ]);

        SendTeamInvitationEmail::dispatch($invitation);

        return $invitation;
    }
}
