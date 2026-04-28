<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminSessionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string'],
        ]);

        $adminEmail = (string) config('services.admin.email');

        if ($adminEmail === '') {
            return response()->json([
                'message' => 'Admin portal is not configured.',
            ], 503);
        }

        $admin = User::query()
            ->where('email', $adminEmail)
            ->where('is_admin', true)
            ->first();

        if (!$admin || !Hash::check($validated['password'], $admin->password)) {
            return response()->json([
                'message' => 'Invalid admin password.',
            ], 422);
        }

        return response()->json([
            'message' => 'Admin verified successfully.',
            'token' => $admin->createToken('admin-panel')->plainTextToken,
            'data' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
            ],
        ]);
    }
}