<?php
namespace App\Policies;
use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function update(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->id;
    }

    public function delete(User $user, Comment $comment): bool
    {
        if ($comment->user_id === $user->id) return true;
        return $comment->issue->team->teamMembers()->where('user_id', $user->id)->whereIn('role', ['owner', 'admin'])->exists();
    }
}
