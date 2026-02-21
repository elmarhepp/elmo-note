<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notes = $request->user()->notes()
            ->with(['notebook', 'tags'])
            ->when($request->notebook_id, fn ($q) => $q->where('notebook_id', $request->notebook_id))
            ->when($request->tag_id, fn ($q) => $q->whereHas('tags', fn ($q) => $q->where('tags.id', $request->tag_id)))
            ->orderByDesc('updated_at')
            ->get(['id', 'notebook_id', 'title', 'updated_at', 'created_at']);

        return response()->json($notes);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'nullable|string',
            'notebook_id' => 'nullable|exists:notebooks,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
        ]);

        $note = $request->user()->notes()->create([
            'title' => $data['title'] ?? 'Untitled',
            'content' => $data['content'] ?? null,
            'notebook_id' => $data['notebook_id'] ?? null,
        ]);

        if (! empty($data['tag_ids'])) {
            $note->tags()->sync($data['tag_ids']);
        }

        return response()->json($note->load(['notebook', 'tags']), 201);
    }

    public function show(Request $request, Note $note): JsonResponse
    {
        abort_unless((int) $note->user_id === $request->user()->id, 403);

        return response()->json($note->load(['notebook', 'tags']));
    }

    public function update(Request $request, Note $note): JsonResponse
    {
        abort_unless((int) $note->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'nullable|string',
            'notebook_id' => 'nullable|exists:notebooks,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
        ]);

        $note->update(array_filter([
            'title' => $data['title'] ?? null,
            'content' => array_key_exists('content', $data) ? $data['content'] : null,
            'notebook_id' => array_key_exists('notebook_id', $data) ? $data['notebook_id'] : null,
        ], fn ($v) => $v !== null));

        if (array_key_exists('tag_ids', $data)) {
            $note->tags()->sync($data['tag_ids'] ?? []);
        }

        return response()->json($note->load(['notebook', 'tags']));
    }

    public function destroy(Request $request, Note $note): JsonResponse
    {
        abort_unless((int) $note->user_id === $request->user()->id, 403);

        $note->delete();

        return response()->json(null, 204);
    }
}
