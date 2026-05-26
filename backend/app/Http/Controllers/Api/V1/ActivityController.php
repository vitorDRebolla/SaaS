<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityLogResource;
use App\Models\Issue;
use App\Models\Team;
use Illuminate\Http\JsonResponse;

class ActivityController extends Controller
{
    public function team(Team $team): JsonResponse
    {
        $logs = $team->activityLogs()
            ->with('causer')
            ->latest('created_at')
            ->limit(50)
            ->get();

        return response()->json(['data' => ActivityLogResource::collection($logs)]);
    }

    public function issue(Issue $issue): JsonResponse
    {
        $logs = $issue->morphMany(\App\Models\ActivityLog::class, 'loggable')
            ->with('causer')
            ->latest('created_at')
            ->get();

        return response()->json(['data' => ActivityLogResource::collection($logs)]);
    }
}
