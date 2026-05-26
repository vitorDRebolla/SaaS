<?php
namespace App\Events\Issues;
use App\Http\Resources\IssueResource;
use App\Models\Issue;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IssueCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Issue $issue) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('projects.' . $this->issue->project_id)];
    }

    public function broadcastWith(): array
    {
        return ['issue' => new IssueResource($this->issue)];
    }
}
