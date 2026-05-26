<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IssueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'team_id' => $this->team_id,
            'identifier' => $this->identifier,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'sequence_number' => $this->sequence_number,
            'position' => $this->position,
            'due_date' => $this->due_date,
            'started_at' => $this->started_at,
            'completed_at' => $this->completed_at,
            'archived_at' => $this->archived_at,
            'status' => new IssueStatusResource($this->whenLoaded('status')),
            'status_id' => $this->status_id,
            'assignee' => new UserResource($this->whenLoaded('assignee')),
            'assignee_id' => $this->assignee_id,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'creator_id' => $this->creator_id,
            'labels' => IssueLabelResource::collection($this->whenLoaded('labels')),
            'comments_count' => $this->whenCounted('comments'),
            'attachments_count' => $this->whenCounted('attachments'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
