<?php
use App\Models\Project;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;

function teamWithMember(string $role = 'member'): array
{
    $user = User::factory()->create();
    $team = Team::factory()->create();
    TeamMember::create(['team_id' => $team->id, 'user_id' => $user->id, 'role' => $role, 'joined_at' => now()]);
    return [$user, $team];
}

it('lists projects for team members', function () {
    [$user, $team] = teamWithMember();
    Project::factory(3)->create(['team_id' => $team->id]);
    $this->actingAs($user)
        ->getJson("/api/v1/teams/{$team->slug}/projects")
        ->assertOk()->assertJsonCount(3, 'data');
});

it('creates a project with default statuses', function () {
    [$user, $team] = teamWithMember('admin');
    $this->actingAs($user)
        ->postJson("/api/v1/teams/{$team->slug}/projects", ['name' => 'New Project', 'identifier' => 'NP'])
        ->assertStatus(201);
    $project = Project::where('team_id', $team->id)->first();
    expect($project->statuses()->count())->toBeGreaterThanOrEqual(4);
});

it('blocks viewer from creating projects', function () {
    [$user, $team] = teamWithMember('viewer');
    $this->actingAs($user)
        ->postJson("/api/v1/teams/{$team->slug}/projects", ['name' => 'New Project', 'identifier' => 'NP2'])
        ->assertStatus(403);
});
