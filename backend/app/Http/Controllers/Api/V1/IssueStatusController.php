<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Http\Resources\IssueStatusResource;
use App\Models\IssueStatus;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IssueStatusController extends Controller
{
    public function store(Request $request, Team $team, Project $project): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'type' => ['required', 'string', 'in:backlog,started,completed,cancelled'],
        ]);
        $status = $project->statuses()->create([
            'name' => $request->name, 'color' => $request->color, 'type' => $request->type,
            'position' => ($project->statuses()->max('position') ?? 0) + 1,
        ]);
        return response()->json(['data' => new IssueStatusResource($status)], 201);
    }

    public function update(Request $request, Team $team, Project $project, IssueStatus $status): JsonResponse
    {
        $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'color' => ['sometimes', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'position' => ['sometimes', 'integer', 'min:0'],
        ]);
        $status->update($request->only('name', 'color', 'position'));
        return response()->json(['data' => new IssueStatusResource($status->fresh())]);
    }

    public function destroy(Team $team, Project $project, IssueStatus $status): JsonResponse
    {
        if ($project->statuses()->count() <= 1) abort(422, 'Cannot delete the last status.');
        if ($status->issues()->exists()) abort(422, 'Move issues from this status first.');
        $status->delete();
        return response()->json(null, 204);
    }
}
