<?php
use App\Models\Issue;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('teams.{teamId}', function ($user, $teamId) {
    $team = Team::find($teamId);
    return $team?->teamMembers()->where('user_id', $user->id)->exists() ? true : null;
});

Broadcast::channel('projects.{projectId}', function ($user, $projectId) {
    $project = Project::find($projectId);
    return $project?->team->teamMembers()->where('user_id', $user->id)->exists() ? true : null;
});

Broadcast::channel('issues.{issueId}', function ($user, $issueId) {
    $issue = Issue::find($issueId);
    return $issue?->team->teamMembers()->where('user_id', $user->id)->exists() ? true : null;
});

Broadcast::channel('users.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
