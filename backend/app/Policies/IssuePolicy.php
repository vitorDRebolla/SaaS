<?php
namespace App\Policies;
use App\Models\Issue;
use App\Models\User;

class IssuePolicy
{
    public function view(User $user, Issue $issue): bool
    {
        return $issue->team->teamMembers()->where('user_id', $user->id)->exists();
    }

    public function update(User $user, Issue $issue): bool
    {
        return $issue->team->teamMembers()->where('user_id', $user->id)->whereIn('role', ['owner', 'admin', 'member'])->exists();
    }

    public function delete(User $user, Issue $issue): bool
    {
        return $issue->team->teamMembers()->where('user_id', $user->id)->whereIn('role', ['owner', 'admin'])->exists()
            || $issue->creator_id === $user->id;
    }
}
