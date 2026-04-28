<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserPreferencesRequest;
use Illuminate\Http\Request;

class UserPreferenceController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'wants_job_fair_email' => $user->wants_job_fair_email,
                'wants_employer_email' => $user->wants_employer_email,
            ],
        ]);
    }

    public function update(UpdateUserPreferencesRequest $request)
    {
        $user = $request->user();
        $validated = $request->validated();

        // Backward compatibility for older clients still sending sms keys.
        if (array_key_exists('wants_job_fair_sms', $validated) && !array_key_exists('wants_job_fair_email', $validated)) {
            $validated['wants_job_fair_email'] = $validated['wants_job_fair_sms'];
        }

        if (array_key_exists('wants_employer_sms', $validated) && !array_key_exists('wants_employer_email', $validated)) {
            $validated['wants_employer_email'] = $validated['wants_employer_sms'];
        }

        $user->fill($validated);
        $user->save();

        return response()->json([
            'message' => 'Preferences updated successfully.',
            'data' => [
                'wants_job_fair_email' => $user->wants_job_fair_email,
                'wants_employer_email' => $user->wants_employer_email,
            ],
        ]);
    }
}
