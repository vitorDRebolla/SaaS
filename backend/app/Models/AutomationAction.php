<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationAction extends Model
{
    protected $fillable = ['automation_rule_id', 'action_type', 'action_config', 'position'];

    protected function casts(): array
    {
        return ['action_config' => 'array'];
    }

    public function rule(): BelongsTo { return $this->belongsTo(AutomationRule::class, 'automation_rule_id'); }
}
