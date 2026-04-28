<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BroadcastSmsAlert implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 120, 300];

    public function __construct(public Event $event)
    {
    }

    public function handle(): void
    {
        $webhookUrl = config('services.n8n.webhook_url');

        if (!$webhookUrl) {
            Log::warning('n8n webhook URL is not configured. Skipping SMS broadcast dispatch.');

            return;
        }

        $query = User::query()->whereNotNull('phone_number');

        if ($this->event->type === 'job_fair') {
            $query->where('wants_job_fair_sms', true);
        } elseif ($this->event->type === 'employer_of_the_day') {
            $query->where('wants_employer_sms', true);
        } else {
            $query->where(function ($nested) {
                $nested->where('wants_job_fair_sms', true)
                    ->orWhere('wants_employer_sms', true);
            });
        }

        $query->orderBy('id')->chunkById(200, function ($users) {
            foreach ($users as $user) {
                $response = Http::timeout(15)
                    ->withHeaders([
                        'X-N8N-Secret' => (string) config('services.n8n.secret'),
                    ])
                    ->post((string) config('services.n8n.webhook_url'), [
                        'user_id' => $user->id,
                        'phone_number' => $user->phone_number,
                        'event_id' => $this->event->id,
                        'event_title' => $this->event->title,
                        'event_type' => $this->event->type,
                        'event_date' => $this->event->event_date?->toIso8601String(),
                        'description' => $this->event->description,
                    ]);

                if ($response->failed()) {
                    Log::error('Failed to dispatch SMS broadcast to n8n', [
                        'user_id' => $user->id,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);

                    continue;
                }

                Log::info('SMS broadcast recipient dispatched to n8n', [
                    'user_id' => $user->id,
                    'phone_number' => $user->phone_number,
                    'event_id' => $this->event->id,
                ]);
            }
        });
    }
}
