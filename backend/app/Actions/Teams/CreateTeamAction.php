<?php
namespace App\Actions\Teams;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Support\Str;

class CreateTeamAction
{
    public function execute(User $owner, array $data): Team
    {
        $slug = $data['slug'] ?? Str::slug($data['name']);
        $base = $slug;
        $i = 1;
        while (Team::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        $team = Team::create([
            'name' => $data['name'],
            'slug' => $slug,
        ]);

        TeamMember::create([
            'team_id' => $team->id,
            'user_id' => $owner->id,
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        return $team;
    }
}
