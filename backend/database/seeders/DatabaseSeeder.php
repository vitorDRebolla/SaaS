<?php
namespace Database\Seeders;
use App\Actions\Projects\CreateProjectAction;
use App\Actions\Teams\CreateTeamAction;
use App\Models\AutomationAction;
use App\Models\AutomationRule;
use App\Models\Comment;
use App\Models\FeatureFlag;
use App\Models\Issue;
use App\Models\IssueLabel;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@meridian.test',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);

        // Demo user
        $demo = User::factory()->create([
            'name' => 'Alex Johnson',
            'email' => 'demo@meridian.test',
            'password' => Hash::make('password'),
        ]);

        // Extra team members
        $members = User::factory(6)->create();
        $allUsers = collect([$admin, $demo])->concat($members);

        $createTeam = app(CreateTeamAction::class);
        $createProject = app(CreateProjectAction::class);

        $teamConfigs = [
            ['name' => 'Acme Engineering', 'projects' => [
                ['name' => 'Platform Core', 'identifier' => 'CORE', 'color' => '#6366f1'],
                ['name' => 'Mobile App', 'identifier' => 'MOB', 'color' => '#8b5cf6'],
            ]],
            ['name' => 'Design Studio', 'projects' => [
                ['name' => 'Design System', 'identifier' => 'DS', 'color' => '#ec4899'],
                ['name' => 'Marketing Site', 'identifier' => 'MKT', 'color' => '#f59e0b'],
            ]],
            ['name' => 'Startup Labs', 'projects' => [
                ['name' => 'Backend API', 'identifier' => 'API', 'color' => '#10b981'],
            ]],
        ];

        foreach ($teamConfigs as $i => $config) {
            $owner = $allUsers->get($i * 2);
            $team = $createTeam->execute($owner, ['name' => $config['name']]);

            // Add members
            foreach ($allUsers->slice(1, 4) as $j => $user) {
                if ($user->id !== $owner->id && !$team->teamMembers()->where('user_id', $user->id)->exists()) {
                    TeamMember::create([
                        'team_id' => $team->id,
                        'user_id' => $user->id,
                        'role' => $j === 0 ? 'admin' : 'member',
                        'joined_at' => now()->subDays(rand(10, 90)),
                    ]);
                }
            }

            foreach ($config['projects'] as $projectData) {
                $project = $createProject->execute($team, $projectData);
                $statuses = $project->statuses;

                // Labels
                $labelColors = ['#ef4444', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6'];
                $labelNames = ['bug', 'feature', 'enhancement', 'documentation', 'chore'];
                $labels = [];
                foreach ($labelNames as $k => $labelName) {
                    $labels[] = IssueLabel::create([
                        'project_id' => $project->id,
                        'name' => $labelName,
                        'color' => $labelColors[$k],
                    ]);
                }

                // Issues
                $issueTitles = [
                    'Implement user authentication flow', 'Add dark mode support', 'Fix navigation bug on mobile',
                    'Improve performance of dashboard queries', 'Add export to CSV functionality',
                    'Integrate third-party analytics', 'Write unit tests for auth module', 'Refactor API response format',
                    'Add real-time notifications', 'Implement file upload with progress', 'Set up CI/CD pipeline',
                    'Add rate limiting to API', 'Create onboarding wizard', 'Fix memory leak in worker process',
                    'Add pagination to issue list', 'Implement search functionality', 'Add audit trail',
                    'Create admin dashboard', 'Optimize database queries', 'Add webhook support',
                ];

                $teamMembers = $team->teamMembers()->with('user')->get();
                $seqNum = 1;

                foreach ($issueTitles as $idx => $title) {
                    $status = $statuses->get($idx % $statuses->count());
                    $assignee = $teamMembers->random()->user;

                    $issue = Issue::create([
                        'project_id' => $project->id,
                        'team_id' => $team->id,
                        'creator_id' => $owner->id,
                        'assignee_id' => rand(0, 3) > 0 ? $assignee->id : null,
                        'status_id' => $status->id,
                        'title' => $title,
                        'description' => "## Overview\n\n" . fake()->paragraph(3) . "\n\n## Acceptance Criteria\n\n- " . implode("\n- ", fake()->sentences(3)),
                        'priority' => collect(['none', 'low', 'medium', 'high', 'urgent'])->random(),
                        'sequence_number' => $seqNum++,
                        'position' => $idx * 1000.0,
                        'due_date' => rand(0, 2) === 0 ? fake()->dateTimeBetween('now', '+2 months') : null,
                        'completed_at' => $status->isCompleted() ? fake()->dateTimeBetween('-30 days', 'now') : null,
                        'started_at' => $status->isStarted() ? fake()->dateTimeBetween('-14 days', 'now') : null,
                    ]);

                    // Attach random labels
                    if (rand(0, 2) > 0 && count($labels) > 0) {
                        $issue->labels()->attach(collect($labels)->random(rand(1, min(2, count($labels))))->pluck('id'));
                    }

                    // Add comments
                    $commentCount = rand(0, 5);
                    for ($c = 0; $c < $commentCount; $c++) {
                        Comment::create([
                            'issue_id' => $issue->id,
                            'user_id' => $teamMembers->random()->user_id,
                            'content' => fake()->paragraph(rand(1, 3)),
                            'created_at' => fake()->dateTimeBetween($issue->created_at ?? '-30 days', 'now'),
                            'updated_at' => now(),
                        ]);
                    }
                }

                // Automation rule
                $doneStatus = $statuses->firstWhere('type', 'completed');
                if ($doneStatus) {
                    $rule = AutomationRule::create([
                        'project_id' => $project->id,
                        'name' => 'Auto-assign urgent issues to admin',
                        'trigger_type' => 'issue_priority_changed',
                        'trigger_config' => ['to_priority' => 'urgent'],
                        'active' => true,
                    ]);
                    AutomationAction::create([
                        'automation_rule_id' => $rule->id,
                        'action_type' => 'assign_to',
                        'action_config' => ['user_id' => $owner->id],
                        'position' => 0,
                    ]);
                }
            }
        }

        // Feature flags
        FeatureFlag::create(['name' => 'ai-suggestions', 'description' => 'AI-powered issue suggestions', 'globally_enabled' => false, 'rollout_percentage' => 10]);
        FeatureFlag::create(['name' => 'time-tracking', 'description' => 'Built-in time tracking', 'globally_enabled' => true, 'rollout_percentage' => 100]);
        FeatureFlag::create(['name' => 'advanced-analytics', 'description' => 'Advanced team analytics dashboard', 'globally_enabled' => false, 'rollout_percentage' => 50]);

        $this->command->info('✓ Seeded demo data successfully.');
        $this->command->info('  Admin: admin@meridian.test / password');
        $this->command->info('  Demo:  demo@meridian.test / password');
    }
}
