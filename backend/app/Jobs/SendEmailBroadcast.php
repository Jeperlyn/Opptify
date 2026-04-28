<?php

namespace App\Jobs;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendEmailBroadcast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 120, 300];

    public function __construct(public Event $event)
    {
    }

    public function handle(): void
    {
        $webhookUrl = config('services.n8n.webhook_url');

        if (!$webhookUrl) {
            Log::warning('n8n webhook URL is not configured. Skipping email broadcast dispatch.');

            return;
        }

        $response = Http::timeout(15)
            ->withHeaders([
                'X-N8N-Secret' => (string) config('services.n8n.secret'),
            ])
            ->post((string) $webhookUrl, [
                'event_id' => $this->event->id,
                'event_type' => $this->event->type,
            ]);

        if ($response->failed()) {
            Log::error('Failed to dispatch email broadcast to n8n', [
                'event_id' => $this->event->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return;
        }

        Log::info('Email broadcast dispatched to n8n', [
            'event_id' => $this->event->id,
            'event_type' => $this->event->type,
        ]);
    }
}