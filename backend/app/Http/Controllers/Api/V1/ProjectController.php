<?php
namespace App\Http\Controllers\Api\V1;
use App\Actions\Projects\CreateProjectAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    public function index(Team $team): JsonResponse
    {
        $projects = $team->projects()
            ->withCount('issues')
            ->with(['statuses', 'labels'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => ProjectResource::collection($projects)]);
    }

    public function store(StoreProjectRequest $request, Team $team, CreateProjectAction $action): JsonResponse
    {
        $this->authorize('update', $team);

        if ($team->projects()->where('identifier', strtoupper($request->identifier))->exists()) {
            abort(422, 'A project with this identifier already exists in this team.');
        }

        $project = $action->execute($team, $request->validated());
        $project->load(['statuses', 'labels']);
        return response()->json(['data' => new ProjectResource($project)], 201);
    }

    public function show(Team $team, Project $project): JsonResponse
    {
        $this->authorize('view', $project);
        $project->load(['statuses', 'labels'])->loadCount('issues');
        return response()->json(['data' => new ProjectResource($project)]);
    }

    public function update(UpdateProjectRequest $request, Team $team, Project $project): JsonResponse
    {
        $this->authorize('update', $project);
        $project->update($request->validated());
        return response()->json(['data' => new ProjectResource($project->fresh(['statuses', 'labels']))]);
    }

    public function destroy(Team $team, Project $project): JsonResponse
    {
        $this->authorize('delete', $project);
        $project->delete();
        return response()->json(null, 204);
    }
}
