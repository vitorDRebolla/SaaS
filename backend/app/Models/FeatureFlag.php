<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    protected $fillable = ['name', 'description', 'globally_enabled', 'rollout_percentage', 'allowed_team_ids'];

    protected function casts(): array
    {
        return [
            'globally_enabled' => 'boolean',
            'allowed_team_ids' => 'array',
        ];
    }

    public function isEnabledForTeam(int $teamId): bool
    {
        if ($this->globally_enabled) return true;
        if (in_array($teamId, $this->allowed_team_ids ?? [])) return true;
        if ($this->rollout_percentage > 0) {
            return ($teamId % 100) < $this->rollout_percentage;
        }
        return false;
    }
}
