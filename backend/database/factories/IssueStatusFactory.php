<?php
namespace Database\Factories;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class IssueStatusFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'name' => $this->faker->randomElement(['Backlog', 'Todo', 'In Progress', 'Done']),
            'color' => $this->faker->hexColor(),
            'type' => $this->faker->randomElement(['backlog', 'started', 'completed']),
            'position' => $this->faker->numberBetween(0, 10),
        ];
    }
}
