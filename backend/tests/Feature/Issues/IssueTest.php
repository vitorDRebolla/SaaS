<?php
use App\Actions\Projects\CreateProjectAction;
use App\Models\Issue;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;

function projectWithMember(string $role = 'member'): array
{
    $user = User::factory()->create();
    $team = Team::factory()->create();
    TeamMember::create(['team_id' => $team->id, 'user_id' => $user->id, 'role' => $role, 'joined_at' => now()]);
    $project = app(CreateProjectAction::class)->execute($team, ['name' => 'Test', 'identifier' => 'TST']);
    $status = $project->statuses()->first();
    return [$user, $team, $project, $status];
}

it('creates an issue with auto-incremented sequence number', function () {
    [$user, $team, $project, $status] = projectWithMember();
    $this->actingAs($user)
        ->postJson("/api/v1/teams/{$team->slug}/projects/{$project->id}/issues", [
            'title' => 'Test Issue', 'status_id' => $status->id,
        ])->assertStatus(201)->assertJsonPath('data.sequence_number', 1);
});

it('lists issues with status filter', function () {
    [$user, $team, $project, $status] = projectWithMember();
    for ($i = 1; $i <= 3; $i++) {
        Issue::create([
            'project_id' => $project->id, 'team_id' => $team->id,
            'creator_id' => $user->id, 'status_id' => $status->id,
            'title' => "Issue $i", 'priority' => 'none',
            'sequence_number' => $i, 'position' => $i * 1000,
        ]);
    }
    $this->actingAs($user)
        ->getJson("/api/v1/teams/{$team->slug}/projects/{$project->id}/issues?status_id={$status->id}")
        ->assertOk()->assertJsonCount(3, 'data');
});

it('updates an issue and logs activity', function () {
    [$user, $team, $project, $status] = projectWithMember();
    $issue = Issue::create([
        'project_id' => $project->id, 'team_id' => $team->id,
        'creator_id' => $user->id, 'status_id' => $status->id,
        'title' => 'Original', 'priority' => 'none', 'sequence_number' => 1, 'position' => 1000,
    ]);
    $this->actingAs($user)
        ->patchJson("/api/v1/teams/{$team->slug}/projects/{$project->id}/issues/{$issue->id}", ['title' => 'Updated'])
        ->assertOk()->assertJsonPath('data.title', 'Updated');
    $this->assertDatabaseHas('activity_logs', ['loggable_id' => $issue->id, 'event' => 'updated']);
});
