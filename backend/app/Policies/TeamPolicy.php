<?php
namespace App\Policies;
use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    public function view(User $user, Team $team): bool
    {
        return $team->teamMembers()->where('user_id', $user->id)->exists();
    }

    public function update(User $user, Team $team): bool
    {
        return $this->isMemberWithRole($user, $team, ['owner', 'admin']);
    }

    public function delete(User $user, Team $team): bool
    {
        return $this->isMemberWithRole($user, $team, ['owner']);
    }

    public function manageMembers(User $user, Team $team): bool
    {
        return $this->isMemberWithRole($user, $team, ['owner', 'admin']);
    }

    private function isMemberWithRole(User $user, Team $team, array $roles): bool
    {
        return $team->teamMembers()->where('user_id', $user->id)->whereIn('role', $roles)->exists();
    }
}
