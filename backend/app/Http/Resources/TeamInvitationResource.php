<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamInvitationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'team_id' => $this->team_id,
            'email' => $this->email,
            'role' => $this->role,
            'expires_at' => $this->expires_at,
            'accepted_at' => $this->accepted_at,
            'is_pending' => $this->isPending(),
            'created_at' => $this->created_at,
        ];
    }
}
