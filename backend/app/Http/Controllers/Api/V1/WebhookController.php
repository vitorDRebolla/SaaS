<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Http\Resources\WebhookResource;
use App\Jobs\ProcessWebhookDelivery;
use App\Models\Team;
use App\Models\Webhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebhookController extends Controller
{
    public function index(Team $team): JsonResponse
    {
        return response()->json(['data' => WebhookResource::collection($team->webhooks)]);
    }

    public function store(Request $request, Team $team): JsonResponse
    {
        $this->authorize('manageMembers', $team);
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url'],
            'subscribed_events' => ['required', 'array'],
            'subscribed_events.*' => ['string'],
        ]);

        $webhook = $team->webhooks()->create([
            'name' => $request->name,
            'url' => $request->url,
            'subscribed_events' => $request->subscribed_events,
            'secret' => Str::random(40),
            'active' => true,
        ]);

        return response()->json(['data' => new WebhookResource($webhook)], 201);
    }

    public function update(Request $request, Team $team, Webhook $webhook): JsonResponse
    {
        $this->authorize('manageMembers', $team);
        $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'url' => ['sometimes', 'url'],
            'subscribed_events' => ['sometimes', 'array'],
            'active' => ['sometimes', 'boolean'],
        ]);

        $webhook->update($request->only('name', 'url', 'subscribed_events', 'active'));
        return response()->json(['data' => new WebhookResource($webhook->fresh())]);
    }

    public function destroy(Team $team, Webhook $webhook): JsonResponse
    {
        $this->authorize('manageMembers', $team);
        $webhook->delete();
        return response()->json(null, 204);
    }

    public function test(Team $team, Webhook $webhook): JsonResponse
    {
        $this->authorize('manageMembers', $team);
        ProcessWebhookDelivery::dispatch($webhook, 'webhook.test', [
            'event' => 'webhook.test',
            'timestamp' => now()->toIso8601String(),
            'team' => $team->slug,
        ]);

        return response()->json(['message' => 'Test webhook queued.']);
    }
}
