<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AutomationRuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'name' => $this->name,
            'trigger_type' => $this->trigger_type,
            'trigger_config' => $this->trigger_config,
            'active' => $this->active,
            'actions' => $this->whenLoaded('actions', fn() => $this->actions->map(fn($a) => [
                'id' => $a->id,
                'action_type' => $a->action_type,
                'action_config' => $a->action_config,
                'position' => $a->position,
            ])),
            'created_at' => $this->created_at,
        ];
    }
}
