<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'loggable_type', 'loggable_id',
        'causer_id', 'team_id', 'event',
        'old_values', 'new_values', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function loggable(): MorphTo { return $this->morphTo(); }
    public function causer(): BelongsTo { return $this->belongsTo(User::class, 'causer_id'); }
    public function team(): BelongsTo { return $this->belongsTo(Team::class); }
}
