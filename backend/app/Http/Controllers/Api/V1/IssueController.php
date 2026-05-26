<?php
namespace App\Http\Controllers\Api\V1;
use App\Actions\Issues\CreateIssueAction;
use App\Actions\Issues\UpdateIssueAction;
use App\Events\Issues\IssueDeleted;
use App\Http\Controllers\Controller;
use App\Http\Requests\Issue\BulkUpdateIssueRequest;
use App\Http\Requests\Issue\StoreIssueRequest;
use App\Http\Requests\Issue\UpdateIssueRequest;
use App\Http\Resources\IssueResource;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IssueController extends Controller
{
    public function index(Request $request, Team $team, Project $project): JsonResponse
    {
        $query = $project->issues()
            ->with(['status', 'assignee', 'creator', 'labels'])
            ->withCount(['comments', 'attachments']);

        if ($request->filled('status_id')) $query->where('status_id', $request->status_id);
        if ($request->filled('assignee_id')) $query->where('assignee_id', $request->assignee_id);
        if ($request->filled('priority')) $query->whereIn('priority', (array) $request->priority);
        if ($request->filled('search')) $query->where('title', 'ilike', '%' . $request->search . '%');

        $sortField = in_array($request->get('sort', 'position'), ['position', 'created_at', 'priority', 'due_date'])
            ? $request->get('sort') : 'position';
        $query->orderBy($sortField ?? 'position');

        return response()->json(['data' => IssueResource::collection($query->get())]);
    }

    public function store(StoreIssueRequest $request, Team $team, Project $project, CreateIssueAction $action): JsonResponse
    {
        $issue = $action->execute($project, $request->user(), $request->validated());
        return response()->json(['data' => new IssueResource($issue)], 201);
    }

    public function show(Request $request, Team $team, Project $project, Issue $issue): JsonResponse
    {
        $this->authorize('view', $issue);
        $issue->load(['status', 'assignee', 'creator', 'labels', 'comments.user', 'attachments.user', 'timeEntries.user']);
        return response()->json(['data' => new IssueResource($issue)]);
    }

    public function update(UpdateIssueRequest $request, Team $team, Project $project, Issue $issue, UpdateIssueAction $action): JsonResponse
    {
        $this->authorize('update', $issue);
        $issue = $action->execute($issue, $request->user(), $request->validated());
        return response()->json(['data' => new IssueResource($issue)]);
    }

    public function destroy(Request $request, Team $team, Project $project, Issue $issue): JsonResponse
    {
        $this->authorize('delete', $issue);
        broadcast(new IssueDeleted($issue->id, $issue->project_id))->toOthers();
        $issue->delete();
        return response()->json(null, 204);
    }

    public function bulkUpdate(BulkUpdateIssueRequest $request, Team $team, Project $project): JsonResponse
    {
        $issues = Issue::whereIn('id', $request->issue_ids)->where('project_id', $project->id)->get();
        $updateData = array_filter([
            'status_id' => $request->status_id ?? null,
            'assignee_id' => $request->assignee_id ?? null,
            'priority' => $request->priority ?? null,
        ]);
        foreach ($issues as $issue) { $issue->update($updateData); }
        return response()->json(['updated' => $issues->count()]);
    }
}
