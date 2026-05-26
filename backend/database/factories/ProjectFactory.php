<?php
namespace Database\Factories;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->words(2, true);
        $words = explode(' ', $name);
        $identifier = strtoupper(substr($words[0], 0, 3) . (isset($words[1]) ? substr($words[1], 0, 2) : ''));
        return [
            'team_id' => Team::factory(),
            'name' => ucwords($name),
            'description' => $this->faker->sentence(),
            'identifier' => $identifier . $this->faker->numberBetween(1, 99),
            'color' => $this->faker->randomElement(['#6366f1', '#8b5cf6', '#ec4899', '#10b981', '#f59e0b', '#3b82f6']),
            'status' => 'active',
        ];
    }
}
