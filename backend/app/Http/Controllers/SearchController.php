<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string|min:2']);

        $notes = $request->user()->notes()
            ->with(['notebook', 'tags'])
            ->search($request->q)
            ->orderByDesc('updated_at')
            ->get(['id', 'notebook_id', 'title', 'updated_at', 'created_at']);

        return response()->json($notes);
    }
}
