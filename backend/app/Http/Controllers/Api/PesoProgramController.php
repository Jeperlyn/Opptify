<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PesoProgram;
use Illuminate\Http\Request;

class PesoProgramController extends Controller
{
    public function index(Request $request)
    {
        $programs = PesoProgram::query()
            ->with('contact')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->query('search'));

                $query->where(function ($nested) use ($search) {
                    $nested->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->where('category', $request->query('category'));
            })
            ->when($request->has('active'), function ($query) use ($request) {
                $query->where('is_active', filter_var($request->query('active'), FILTER_VALIDATE_BOOLEAN));
            }, function ($query) {
                $query->where('is_active', true);
            })
            ->orderBy('display_order')
            ->orderBy('title')
            ->paginate(min((int) $request->query('per_page', 20), 50));

        return response()->json([
            'data' => $programs->items(),
            'meta' => [
                'current_page' => $programs->currentPage(),
                'per_page' => $programs->perPage(),
                'total' => $programs->total(),
                'last_page' => $programs->lastPage(),
            ],
        ]);
    }

    public function show(PesoProgram $pesoProgram)
    {
        $pesoProgram->load('contact');

        return response()->json([
            'data' => $pesoProgram,
        ]);
    }
}
