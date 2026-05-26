<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Team;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class AnalyticsController extends Controller
{
    public function __construct(private AnalyticsService $analytics) {}

    public function team(Team $team): JsonResponse
    {
        $data = Cache::remember("team:{$team->id}:analytics:snapshot", now()->addMinutes(15), fn() =>
            $this->analytics->getTeamSnapshot($team)
        );

        return response()->json(['data' => $data]);
    }

    public function project(Team $team, Project $project): JsonResponse
    {
        $data = Cache::remember("project:{$project->id}:analytics", now()->addMinutes(10), fn() =>
            $this->analytics->getProjectAnalytics($project)
        );

        return response()->json(['data' => $data]);
    }
}
