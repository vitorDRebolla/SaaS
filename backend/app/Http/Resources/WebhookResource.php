<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WebhookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'team_id' => $this->team_id,
            'name' => $this->name,
            'url' => $this->url,
            'subscribed_events' => $this->subscribed_events,
            'active' => $this->active,
            'last_response_code' => $this->last_response_code,
            'last_called_at' => $this->last_called_at,
            'created_at' => $this->created_at,
        ];
    }
}
