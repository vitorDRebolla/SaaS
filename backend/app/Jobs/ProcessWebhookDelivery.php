<?php
namespace App\Jobs;
use App\Models\Webhook;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class ProcessWebhookDelivery implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public Webhook $webhook,
        public string $event,
        public array $payload,
    ) {}

    public function handle(): void
    {
        $body = json_encode($this->payload);
        $signature = hash_hmac('sha256', $body, $this->webhook->secret);

        $response = Http::timeout(10)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'X-Meridian-Event' => $this->event,
                'X-Meridian-Signature' => 'sha256=' . $signature,
            ])
            ->post($this->webhook->url, $this->payload);

        $this->webhook->update([
            'last_response_code' => $response->status(),
            'last_called_at' => now(),
        ]);
    }
}
