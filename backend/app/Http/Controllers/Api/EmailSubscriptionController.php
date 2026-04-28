<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmailSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailSubscriptionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorizeWebhook($request);

        $subscriptions = EmailSubscription::query()
            ->orderBy('email')
            ->get();

        return response()->json([
            'data' => $subscriptions,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc,dns', 'max:255'],
        ]);

        $subscription = EmailSubscription::updateOrCreate(
            ['email' => $validated['email']],
            [
                'wants_job_fair' => true,
                'wants_employer_of_the_day' => true,
            ]
        );

        return response()->json([
            'message' => 'Email updated successfully.',
            'data' => $subscription,
        ]);
    }

    private function authorizeWebhook(Request $request): void
    {
        $secret = (string) config('services.n8n.secret');
        $providedSecret = (string) ($request->header('X-N8N-Secret') ?? $request->query('secret', ''));

        if (!hash_equals($secret, $providedSecret)) {
            Log::warning('Webhook authorization failed for email subscription lookup.', [
                'path' => $request->path(),
                'has_header_secret' => $request->hasHeader('X-N8N-Secret'),
                'has_query_secret' => $request->query->has('secret'),
                'provided_secret_length' => strlen($providedSecret),
                'expected_secret_length' => strlen($secret),
                'user_agent' => (string) $request->userAgent(),
            ]);

            abort(response()->json([
                'message' => 'Unauthorized.',
            ], 403));
        }
    }
}