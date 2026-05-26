<?php
namespace App\Events\Members;
use App\Models\Team;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MemberJoined implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Team $team, public User $user) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('teams.' . $this->team->id)];
    }

    public function broadcastWith(): array
    {
        return ['user_id' => $this->user->id, 'name' => $this->user->name];
    }
}
