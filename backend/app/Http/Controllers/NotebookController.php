<?php

namespace App\Http\Controllers;

use App\Models\Notebook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotebookController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notebooks = $request->user()->notebooks()->withCount('notes')->get();

        return response()->json($notebooks);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $notebook = $request->user()->notebooks()->create($data);

        return response()->json($notebook, 201);
    }

    public function update(Request $request, Notebook $notebook): JsonResponse
    {
        abort_unless((int) $notebook->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $notebook->update($data);

        return response()->json($notebook);
    }

    public function destroy(Request $request, Notebook $notebook): JsonResponse
    {
        abort_unless((int) $notebook->user_id === $request->user()->id, 403);

        $notebook->delete();

        return response()->json(null, 204);
    }
}
