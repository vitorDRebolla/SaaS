<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutomationRule extends Model
{
    protected $fillable = ['project_id', 'name', 'trigger_type', 'trigger_config', 'active'];

    protected function casts(): array
    {
        return [
            'trigger_config' => 'array',
            'active' => 'boolean',
        ];
    }

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function actions(): HasMany { return $this->hasMany(AutomationAction::class)->orderBy('position'); }
}
