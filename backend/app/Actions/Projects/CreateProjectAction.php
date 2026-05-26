<?php
namespace App\Actions\Projects;
use App\Models\IssueStatus;
use App\Models\Project;
use App\Models\Team;

class CreateProjectAction
{
    public function execute(Team $team, array $data): Project
    {
        $project = Project::create([
            'team_id' => $team->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'identifier' => strtoupper($data['identifier']),
            'color' => $data['color'] ?? '#6366f1',
        ]);

        $this->createDefaultStatuses($project);

        return $project;
    }

    private function createDefaultStatuses(Project $project): void
    {
        $statuses = [
            ['name' => 'Backlog', 'color' => '#94a3b8', 'type' => 'backlog', 'position' => 0],
            ['name' => 'Todo', 'color' => '#64748b', 'type' => 'backlog', 'position' => 1],
            ['name' => 'In Progress', 'color' => '#f59e0b', 'type' => 'started', 'position' => 2],
            ['name' => 'In Review', 'color' => '#8b5cf6', 'type' => 'started', 'position' => 3],
            ['name' => 'Done', 'color' => '#10b981', 'type' => 'completed', 'position' => 4],
            ['name' => 'Cancelled', 'color' => '#ef4444', 'type' => 'cancelled', 'position' => 5],
        ];

        foreach ($statuses as $status) {
            IssueStatus::create(array_merge(['project_id' => $project->id], $status));
        }
    }
}
