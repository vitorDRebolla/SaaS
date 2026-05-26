<?php
use App\Models\User;

it('allows a user to register', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'SecurePass1!',
        'password_confirmation' => 'SecurePass1!',
    ]);
    $response->assertStatus(201)->assertJsonPath('user.email', 'jane@example.com');
    $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
});

it('rejects duplicate email on register', function () {
    User::factory()->create(['email' => 'existing@example.com']);
    $this->postJson('/api/v1/auth/register', [
        'name' => 'New User', 'email' => 'existing@example.com',
        'password' => 'SecurePass1!', 'password_confirmation' => 'SecurePass1!',
    ])->assertStatus(422);
});

it('allows a user to login', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);
    $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'password'])
        ->assertOk()->assertJsonPath('user.id', $user->id);
});

it('rejects invalid credentials', function () {
    User::factory()->create(['email' => 'user@example.com']);
    $this->postJson('/api/v1/auth/login', ['email' => 'user@example.com', 'password' => 'wrong'])
        ->assertStatus(422);
});

it('returns the authenticated user', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->getJson('/api/v1/auth/me')
        ->assertOk()->assertJsonPath('user.id', $user->id);
});

it('requires authentication to access me endpoint', function () {
    $this->getJson('/api/v1/auth/me')->assertStatus(401);
});
