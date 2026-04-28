<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    public function index(): JsonResponse
    {
        $events = Event::query()
            ->orderByDesc('event_date')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'data' => $events,
        ]);
    }

    public function show(Request $request, Event $event): JsonResponse
    {
        $this->authorizeWebhook($request);

        return response()->json([
            'data' => $event,
        ]);
    }

    private function authorizeWebhook(Request $request): void
    {
        $secret = (string) config('services.n8n.secret');
        $providedSecret = (string) ($request->header('X-N8N-Secret') ?? $request->query('secret', ''));

        if (!hash_equals($secret, $providedSecret)) {
            Log::warning('Webhook authorization failed for event lookup.', [
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
