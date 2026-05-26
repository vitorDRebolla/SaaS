<?php
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;

it('allows a user to create a team', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->postJson('/api/v1/teams', ['name' => 'My Team'])
        ->assertStatus(201)->assertJsonPath('data.name', 'My Team');
    $this->assertDatabaseHas('team_members', ['user_id' => $user->id, 'role' => 'owner']);
});

it('allows team owner to invite a member', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create();
    TeamMember::create(['team_id' => $team->id, 'user_id' => $owner->id, 'role' => 'owner', 'joined_at' => now()]);
    $this->actingAs($owner)
        ->postJson("/api/v1/teams/{$team->slug}/invitations", ['email' => 'new@example.com', 'role' => 'member'])
        ->assertStatus(201);
    $this->assertDatabaseHas('team_invitations', ['email' => 'new@example.com']);
});

it('blocks non-members from accessing team', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $this->actingAs($user)
        ->getJson("/api/v1/teams/{$team->slug}/projects")
        ->assertStatus(403);
});

it('returns member list', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create();
    TeamMember::create(['team_id' => $team->id, 'user_id' => $owner->id, 'role' => 'owner', 'joined_at' => now()]);
    $this->actingAs($owner)
        ->getJson("/api/v1/teams/{$team->slug}/members")
        ->assertOk()->assertJsonCount(1, 'data');
});
