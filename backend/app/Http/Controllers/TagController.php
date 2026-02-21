<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tags = $request->user()->tags()->withCount('notes')->get();

        return response()->json($tags);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $tag = $request->user()->tags()->firstOrCreate(['name' => $data['name']]);

        return response()->json($tag, 201);
    }

    public function destroy(Request $request, Tag $tag): JsonResponse
    {
        abort_unless((int) $tag->user_id === $request->user()->id, 403);

        $tag->delete();

        return response()->json(null, 204);
    }
}
