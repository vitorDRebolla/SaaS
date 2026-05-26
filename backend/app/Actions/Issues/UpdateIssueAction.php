<?php
namespace App\Actions\Issues;
use App\Events\Issues\IssueUpdated;
use App\Jobs\EvaluateAutomationRules;
use App\Models\Issue;
use App\Models\User;

class UpdateIssueAction
{
    public function execute(Issue $issue, User $updater, array $data): Issue
    {
        $oldData = $issue->only(['status_id', 'assignee_id', 'priority', 'title']);

        $updateData = array_filter([
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'priority' => $data['priority'] ?? null,
            'status_id' => $data['status_id'] ?? null,
            'assignee_id' => array_key_exists('assignee_id', $data) ? $data['assignee_id'] : null,
            'due_date' => array_key_exists('due_date', $data) ? $data['due_date'] : null,
            'position' => $data['position'] ?? null,
        ], fn($v) => !is_null($v));

        if (isset($data['status_id'])) {
            $status = $issue->status;
            if ($status?->isCompleted() === false && $issue->status?->isCompleted() === false) {
                $newStatus = \App\Models\IssueStatus::find($data['status_id']);
                if ($newStatus?->isCompleted()) {
                    $updateData['completed_at'] = now();
                } elseif ($newStatus?->isStarted() && !$issue->started_at) {
                    $updateData['started_at'] = now();
                }
            }
        }

        $issue->update($updateData);

        if (array_key_exists('label_ids', $data)) {
            $issue->labels()->sync($data['label_ids'] ?? []);
        }

        broadcast(new IssueUpdated($issue->fresh(['status', 'assignee', 'creator', 'labels'])))->toOthers();

        EvaluateAutomationRules::dispatch($issue, $oldData)->afterCommit();

        return $issue->load(['status', 'assignee', 'creator', 'labels']);
    }
}
