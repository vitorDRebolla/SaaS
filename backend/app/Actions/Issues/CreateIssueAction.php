<?php
namespace App\Actions\Issues;
use App\Events\Issues\IssueCreated;
use App\Models\Issue;
use App\Models\Project;
use App\Models\User;

class CreateIssueAction
{
    public function execute(Project $project, User $creator, array $data): Issue
    {
        $issue = Issue::create([
            'project_id' => $project->id,
            'team_id' => $project->team_id,
            'creator_id' => $creator->id,
            'assignee_id' => $data['assignee_id'] ?? null,
            'status_id' => $data['status_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'priority' => $data['priority'] ?? 'none',
            'sequence_number' => $project->nextSequenceNumber(),
            'position' => $this->nextPosition($project, $data['status_id']),
            'due_date' => $data['due_date'] ?? null,
        ]);

        if (!empty($data['label_ids'])) {
            $issue->labels()->sync($data['label_ids']);
        }

        broadcast(new IssueCreated($issue))->toOthers();

        return $issue->load(['status', 'assignee', 'creator', 'labels']);
    }

    private function nextPosition(Project $project, int $statusId): float
    {
        $max = Issue::where('project_id', $project->id)
            ->where('status_id', $statusId)
            ->max('position');

        return ($max ?? 0) + 1000;
    }
}
