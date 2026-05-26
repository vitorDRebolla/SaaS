<?php
namespace App\Actions\Comments;
use App\Events\Comments\CommentCreated;
use App\Models\Comment;
use App\Models\Issue;
use App\Models\User;

class CreateCommentAction
{
    public function execute(Issue $issue, User $author, array $data): Comment
    {
        $comment = Comment::create([
            'issue_id' => $issue->id,
            'user_id' => $author->id,
            'content' => $data['content'],
        ]);

        $comment->load('user');

        broadcast(new CommentCreated($comment))->toOthers();

        return $comment;
    }
}
