<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'team_id' => $this->team_id,
            'name' => $this->name,
            'description' => $this->description,
            'identifier' => $this->identifier,
            'color' => $this->color,
            'settings' => $this->settings,
            'status' => $this->status,
            'archived_at' => $this->archived_at,
            'statuses' => IssueStatusResource::collection($this->whenLoaded('statuses')),
            'labels' => IssueLabelResource::collection($this->whenLoaded('labels')),
            'issues_count' => $this->whenCounted('issues'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
