<?php
namespace App\Jobs;
use App\Models\Issue;
use App\Models\IssueStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class EvaluateAutomationRules implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Issue $issue,
        public array $oldData = [],
    ) {}

    public function handle(): void
    {
        $rules = $this->issue->project->automationRules()
            ->where('active', true)
            ->with('actions')
            ->get();

        foreach ($rules as $rule) {
            if ($this->triggerMatches($rule)) {
                $this->executeActions($rule);
            }
        }
    }

    private function triggerMatches($rule): bool
    {
        return match ($rule->trigger_type) {
            'issue_status_changed' => isset($this->oldData['status_id']) && $this->oldData['status_id'] !== $this->issue->status_id,
            'issue_assigned' => isset($this->oldData['assignee_id']) && $this->oldData['assignee_id'] !== $this->issue->assignee_id,
            'issue_priority_changed' => isset($this->oldData['priority']) && $this->oldData['priority'] !== $this->issue->priority,
            default => false,
        };
    }

    private function executeActions($rule): void
    {
        foreach ($rule->actions as $action) {
            match ($action->action_type) {
                'change_status' => $this->issue->update(['status_id' => $action->action_config['status_id'] ?? null]),
                'assign_to' => $this->issue->update(['assignee_id' => $action->action_config['user_id'] ?? null]),
                'set_priority' => $this->issue->update(['priority' => $action->action_config['priority'] ?? 'none']),
                'add_label' => $this->issue->labels()->syncWithoutDetaching($action->action_config['label_ids'] ?? []),
                default => null,
            };
        }
    }
}
