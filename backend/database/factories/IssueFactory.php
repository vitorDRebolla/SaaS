<?php
namespace Database\Factories;
use App\Models\Issue;
use App\Models\IssueStatus;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class IssueFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'team_id' => Team::factory(),
            'creator_id' => User::factory(),
            'assignee_id' => null,
            'status_id' => IssueStatus::factory(),
            'title' => ucfirst($this->faker->sentence(rand(3, 8), false)),
            'description' => $this->faker->optional(0.7)->paragraphs(2, true),
            'priority' => $this->faker->randomElement(['none', 'none', 'low', 'medium', 'high', 'urgent']),
            'sequence_number' => $this->faker->unique()->numberBetween(1, 9999),
            'position' => $this->faker->randomFloat(2, 0, 100000),
            'due_date' => $this->faker->optional(0.4)->dateTimeBetween('now', '+3 months'),
        ];
    }
}
