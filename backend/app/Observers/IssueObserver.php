<?php
namespace App\Observers;
use App\Models\ActivityLog;
use App\Models\Issue;

class IssueObserver
{
    public function created(Issue $issue): void
    {
        ActivityLog::create([
            'loggable_type' => Issue::class,
            'loggable_id' => $issue->id,
            'causer_id' => auth()->id(),
            'team_id' => $issue->team_id,
            'event' => 'created',
            'old_values' => [],
            'new_values' => $issue->only(['title', 'priority', 'status_id', 'assignee_id']),
            'created_at' => now(),
        ]);
    }

    public function updated(Issue $issue): void
    {
        $dirty = $issue->getDirty();
        if (empty($dirty)) return;

        ActivityLog::create([
            'loggable_type' => Issue::class,
            'loggable_id' => $issue->id,
            'causer_id' => auth()->id(),
            'team_id' => $issue->team_id,
            'event' => 'updated',
            'old_values' => array_intersect_key($issue->getOriginal(), $dirty),
            'new_values' => $dirty,
            'created_at' => now(),
        ]);
    }

    public function deleted(Issue $issue): void
    {
        ActivityLog::create([
            'loggable_type' => Issue::class,
            'loggable_id' => $issue->id,
            'causer_id' => auth()->id(),
            'team_id' => $issue->team_id,
            'event' => 'deleted',
            'old_values' => $issue->only(['title']),
            'new_values' => [],
            'created_at' => now(),
        ]);
    }
}
