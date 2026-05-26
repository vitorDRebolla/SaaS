<?php
namespace App\Jobs;
use App\Models\Team;
use App\Services\AnalyticsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class GenerateTeamAnalyticsSnapshot implements ShouldQueue
{
    use Queueable;

    public function __construct(public Team $team) {}

    public function handle(AnalyticsService $analytics): void
    {
        $data = $analytics->getTeamSnapshot($this->team);
        Cache::put("team:{$this->team->id}:analytics:snapshot", $data, now()->addHours(24));
    }
}
