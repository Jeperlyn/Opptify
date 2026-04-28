<?php

namespace App\Http\Controllers;

use App\Jobs\SendEmailBroadcast;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminEventController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:job_fair,employer_of_the_day'],
            'description' => ['required', 'string'],
            'event_date' => ['required', 'date'],
            'send_email_alert' => ['sometimes', 'boolean'],
            'send_sms_alert' => ['sometimes', 'boolean'],
        ]);

        $sendEmailAlert = $request->boolean('send_email_alert', $request->boolean('send_sms_alert'));

        $event = Event::create([
            ...$validated,
            'send_email_alert' => $sendEmailAlert,
            'send_sms_alert' => $sendEmailAlert,
        ]);

        if ($event->send_email_alert) {
            SendEmailBroadcast::dispatch($event);
        }

        return response()->json([
            'message' => 'Event created successfully.',
            'event' => $event
        ], 201);
    }
}