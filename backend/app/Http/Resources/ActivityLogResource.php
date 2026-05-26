<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'loggable_type' => $this->loggable_type,
            'loggable_id' => $this->loggable_id,
            'event' => $this->event,
            'old_values' => $this->old_values,
            'new_values' => $this->new_values,
            'causer' => new UserResource($this->whenLoaded('causer')),
            'created_at' => $this->created_at,
        ];
    }
}
