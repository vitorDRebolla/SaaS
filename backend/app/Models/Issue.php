<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Issue extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'team_id', 'creator_id', 'assignee_id', 'status_id',
        'title', 'description', 'priority', 'sequence_number', 'position',
        'due_date', 'started_at', 'completed_at', 'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'archived_at' => 'datetime',
            'position' => 'float',
        ];
    }

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function team(): BelongsTo { return $this->belongsTo(Team::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'creator_id'); }
    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assignee_id'); }
    public function status(): BelongsTo { return $this->belongsTo(IssueStatus::class, 'status_id'); }
    public function labels(): BelongsToMany { return $this->belongsToMany(IssueLabel::class, 'issue_label'); }
    public function comments(): HasMany { return $this->hasMany(Comment::class)->orderBy('created_at'); }
    public function attachments(): MorphMany { return $this->morphMany(Attachment::class, 'attachable'); }
    public function timeEntries(): HasMany { return $this->hasMany(TimeEntry::class); }

    public function getIdentifierAttribute(): string
    {
        return $this->project->identifier . '-' . $this->sequence_number;
    }
}
