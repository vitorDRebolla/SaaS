<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Http\Resources\IssueResource;
use App\Http\Resources\ProjectResource;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request, Team $team): JsonResponse
    {
        $request->validate(['q' => ['required', 'string', 'min:2', 'max:100']]);
        $query = $request->q;

        $issues = Issue::where('team_id', $team->id)
            ->where('title', 'ilike', "%{$query}%")
            ->with(['status', 'assignee'])
            ->limit(10)
            ->get();

        $projects = Project::where('team_id', $team->id)
            ->where('name', 'ilike', "%{$query}%")
            ->limit(5)
            ->get();

        return response()->json([
            'data' => [
                'issues' => IssueResource::collection($issues),
                'projects' => ProjectResource::collection($projects),
            ]
        ]);
    }
}
