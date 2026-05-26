<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IssueStatus extends Model
{
    use HasFactory;

    protected $fillable = ['project_id', 'name', 'color', 'type', 'position'];

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function issues(): HasMany { return $this->hasMany(Issue::class, 'status_id'); }

    public function isCompleted(): bool { return $this->type === 'completed'; }
    public function isCancelled(): bool { return $this->type === 'cancelled'; }
    public function isStarted(): bool { return $this->type === 'started'; }
}
