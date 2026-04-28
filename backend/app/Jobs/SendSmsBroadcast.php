<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendSmsBroadcast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 120, 300];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $message,
        public string $audience = 'all'
    )
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $webhookUrl = config('services.n8n.webhook_url');

        if (!$webhookUrl) {
            Log::warning('n8n webhook URL is not configured. Skipping email broadcast dispatch.');
            return;
        }

        $query = User::query()->whereNotNull('email');

        if ($this->audience === 'job_fair') {
            $query->where('wants_job_fair_email', true);
        } elseif ($this->audience === 'employer_of_the_day') {
            $query->where('wants_employer_email', true);
        } else {
            $query->where(function ($nested) {
                $nested->where('wants_job_fair_email', true)
                    ->orWhere('wants_employer_email', true);
            });
        }

        $query->orderBy('id')->chunkById(200, function ($users) {
            foreach ($users as $user) {
                $payload = [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'audience' => $this->audience,
                    'message' => $this->message,
                ];

                $response = Http::timeout(15)
                    ->withHeaders([
                        'X-N8N-Secret' => (string) config('services.n8n.secret'),
                    ])
                    ->post((string) config('services.n8n.webhook_url'), $payload);

                if ($response->failed()) {
                    Log::error('Failed to dispatch email broadcast to n8n', [
                        'user_id' => $user->id,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    continue;
                }

                Log::info('Email broadcast recipient dispatched to n8n', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'audience' => $this->audience,
                ]);
            }
        });
    }
}
