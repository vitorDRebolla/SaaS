<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['team_id', 'name', 'description', 'identifier', 'color', 'settings', 'status', 'archived_at'];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'archived_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function statuses(): HasMany
    {
        return $this->hasMany(IssueStatus::class)->orderBy('position');
    }

    public function labels(): HasMany
    {
        return $this->hasMany(IssueLabel::class);
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class);
    }

    public function automationRules(): HasMany
    {
        return $this->hasMany(AutomationRule::class);
    }

    public function nextSequenceNumber(): int
    {
        return ($this->issues()->max('sequence_number') ?? 0) + 1;
    }
}
