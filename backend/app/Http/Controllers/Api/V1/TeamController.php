<?php
namespace App\Http\Controllers\Api\V1;
use App\Actions\Teams\CreateTeamAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Team\StoreTeamRequest;
use App\Http\Requests\Team\UpdateTeamRequest;
use App\Http\Resources\TeamResource;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $teams = $request->user()->teams()->withCount(['members', 'projects'])->get();
        return response()->json(['data' => TeamResource::collection($teams)]);
    }

    public function store(StoreTeamRequest $request, CreateTeamAction $action): JsonResponse
    {
        $team = $action->execute($request->user(), $request->validated());
        return response()->json(['data' => new TeamResource($team)], 201);
    }

    public function show(Request $request, Team $team): JsonResponse
    {
        $this->authorize('view', $team);
        $team->loadCount(['members', 'projects']);
        return response()->json(['data' => new TeamResource($team)]);
    }

    public function update(UpdateTeamRequest $request, Team $team): JsonResponse
    {
        $this->authorize('update', $team);
        $team->update($request->validated());
        return response()->json(['data' => new TeamResource($team->fresh())]);
    }

    public function destroy(Request $request, Team $team): JsonResponse
    {
        $this->authorize('delete', $team);
        $team->delete();
        return response()->json(null, 204);
    }
}
