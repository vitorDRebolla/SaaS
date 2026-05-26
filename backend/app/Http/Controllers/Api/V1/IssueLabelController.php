<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Http\Resources\IssueLabelResource;
use App\Models\IssueLabel;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IssueLabelController extends Controller
{
    public function store(Request $request, Team $team, Project $project): JsonResponse
    {
        $request->validate(['name' => ['required', 'string', 'max:100'], 'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/']]);
        $label = $project->labels()->create($request->only('name', 'color'));
        return response()->json(['data' => new IssueLabelResource($label)], 201);
    }

    public function update(Request $request, Team $team, Project $project, IssueLabel $label): JsonResponse
    {
        $request->validate(['name' => ['sometimes', 'string', 'max:100'], 'color' => ['sometimes', 'string', 'regex:/^#[0-9a-fA-F]{6}$/']]);
        $label->update($request->only('name', 'color'));
        return response()->json(['data' => new IssueLabelResource($label->fresh())]);
    }

    public function destroy(Team $team, Project $project, IssueLabel $label): JsonResponse
    {
        $label->delete();
        return response()->json(null, 204);
    }
}
