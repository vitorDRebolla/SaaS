<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeEntry extends Model
{
    protected $fillable = ['issue_id', 'user_id', 'started_at', 'stopped_at', 'duration_seconds', 'description'];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'stopped_at' => 'datetime',
        ];
    }

    public function issue(): BelongsTo { return $this->belongsTo(Issue::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
