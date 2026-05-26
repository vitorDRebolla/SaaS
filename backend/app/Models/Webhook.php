<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Webhook extends Model
{
    protected $fillable = ['team_id', 'name', 'url', 'subscribed_events', 'secret', 'active', 'last_response_code', 'last_called_at'];
    protected $hidden = ['secret'];

    protected function casts(): array
    {
        return [
            'subscribed_events' => 'array',
            'active' => 'boolean',
            'last_called_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo { return $this->belongsTo(Team::class); }
}
