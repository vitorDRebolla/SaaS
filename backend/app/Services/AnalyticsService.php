<?php
namespace App\Services;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function getTeamSnapshot(Team $team): array
    {
        return [
            'total_issues' => Issue::where('team_id', $team->id)->count(),
            'completed_issues' => Issue::where('team_id', $team->id)->whereNotNull('completed_at')->count(),
            'in_progress' => Issue::where('team_id', $team->id)
                ->whereHas('status', fn($q) => $q->where('type', 'started'))
                ->count(),
            'overdue' => Issue::where('team_id', $team->id)
                ->whereNull('completed_at')
                ->where('due_date', '<', now())
                ->count(),
            'velocity' => $this->weeklyVelocity($team),
            'throughput' => $this->monthlyThroughput($team),
            'generated_at' => now()->toIso8601String(),
        ];
    }

    public function getProjectAnalytics(Project $project): array
    {
        $statusBreakdown = $project->issues()
            ->join('issue_statuses', 'issues.status_id', '=', 'issue_statuses.id')
            ->select('issue_statuses.name', 'issue_statuses.type', 'issue_statuses.color', DB::raw('count(*) as count'))
            ->groupBy('issue_statuses.id', 'issue_statuses.name', 'issue_statuses.type', 'issue_statuses.color')
            ->get();

        $priorityBreakdown = $project->issues()
            ->select('priority', DB::raw('count(*) as count'))
            ->groupBy('priority')
            ->get();

        $completedByWeek = Issue::where('project_id', $project->id)
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', now()->subWeeks(12))
            ->select(DB::raw("DATE_TRUNC('week', completed_at) as week"), DB::raw('count(*) as count'))
            ->groupBy('week')
            ->orderBy('week')
            ->get();

        $memberWorkload = $project->issues()
            ->whereNull('completed_at')
            ->whereNotNull('assignee_id')
            ->with('assignee:id,name,avatar_url')
            ->select('assignee_id', DB::raw('count(*) as count'))
            ->groupBy('assignee_id')
            ->get();

        return [
            'status_breakdown' => $statusBreakdown,
            'priority_breakdown' => $priorityBreakdown,
            'velocity_by_week' => $completedByWeek,
            'member_workload' => $memberWorkload,
            'total_issues' => $project->issues()->count(),
            'completed_issues' => $project->issues()->whereNotNull('completed_at')->count(),
            'open_issues' => $project->issues()->whereNull('completed_at')->count(),
        ];
    }

    private function weeklyVelocity(Team $team): array
    {
        return Issue::where('team_id', $team->id)
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', now()->subWeeks(8))
            ->select(DB::raw("DATE_TRUNC('week', completed_at) as week"), DB::raw('count(*) as count'))
            ->groupBy('week')
            ->orderBy('week')
            ->get()
            ->toArray();
    }

    private function monthlyThroughput(Team $team): array
    {
        return Issue::where('team_id', $team->id)
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', now()->subMonths(6))
            ->select(DB::raw("DATE_TRUNC('month', completed_at) as month"), DB::raw('count(*) as count'))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->toArray();
    }
}
