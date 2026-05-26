<?php
namespace App\Observers;
use App\Models\ActivityLog;
use App\Models\Comment;
use App\Models\Issue;

class CommentObserver
{
    public function created(Comment $comment): void
    {
        $issue = $comment->issue;
        ActivityLog::create([
            'loggable_type' => Issue::class,
            'loggable_id' => $comment->issue_id,
            'causer_id' => auth()->id(),
            'team_id' => $issue->team_id,
            'event' => 'comment_added',
            'old_values' => [],
            'new_values' => ['comment_id' => $comment->id, 'content' => substr($comment->content, 0, 100)],
            'created_at' => now(),
        ]);
    }
}
