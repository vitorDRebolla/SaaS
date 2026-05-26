<?php
namespace App\Events\Issues;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IssueDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $issueId,
        public int $projectId,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('projects.' . $this->projectId)];
    }

    public function broadcastWith(): array
    {
        return ['issue_id' => $this->issueId];
    }
}
