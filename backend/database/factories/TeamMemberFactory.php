<?php
namespace Database\Factories;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamMemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'user_id' => User::factory(),
            'role' => $this->faker->randomElement(['admin', 'member', 'member', 'viewer']),
            'joined_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
