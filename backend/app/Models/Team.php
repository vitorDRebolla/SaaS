<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'avatar_url', 'settings', 'plan', 'subscription_status', 'trial_ends_at'];

    protected function casts(): array
    {
        return ['settings' => 'array', 'trial_ends_at' => 'datetime'];
    }

    public function getRouteKeyName(): string { return 'slug'; }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_members')
            ->withPivot('role', 'joined_at')->withTimestamps();
    }

    public function teamMembers(): HasMany { return $this->hasMany(TeamMember::class); }
    public function invitations(): HasMany { return $this->hasMany(TeamInvitation::class); }
    public function projects(): HasMany { return $this->hasMany(Project::class); }
    public function issues(): HasMany { return $this->hasMany(Issue::class); }
    public function webhooks(): HasMany { return $this->hasMany(Webhook::class); }
    public function activityLogs(): HasMany { return $this->hasMany(ActivityLog::class); }
}
