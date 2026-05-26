<?php
namespace App\Http\Controllers\Api\V1;
use App\Actions\Comments\CreateCommentAction;
use App\Events\Comments\CommentUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    public function index(Team $team, Project $project, Issue $issue): JsonResponse
    {
        return response()->json(['data' => CommentResource::collection($issue->comments()->with('user')->get())]);
    }

    public function store(StoreCommentRequest $request, Team $team, Project $project, Issue $issue, CreateCommentAction $action): JsonResponse
    {
        $comment = $action->execute($issue, $request->user(), $request->validated());
        return response()->json(['data' => new CommentResource($comment)], 201);
    }

    public function update(UpdateCommentRequest $request, Team $team, Project $project, Issue $issue, Comment $comment): JsonResponse
    {
        $this->authorize('update', $comment);
        $comment->update(['content' => $request->content, 'edited_at' => now()]);
        $comment->load('user');
        broadcast(new CommentUpdated($comment))->toOthers();
        return response()->json(['data' => new CommentResource($comment)]);
    }

    public function destroy(Team $team, Project $project, Issue $issue, Comment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);
        $comment->delete();
        return response()->json(null, 204);
    }
}
