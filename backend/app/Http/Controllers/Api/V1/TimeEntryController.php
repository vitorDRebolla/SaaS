<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Http\Resources\TimeEntryResource;
use App\Models\Issue;
use App\Models\TimeEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimeEntryController extends Controller
{
    public function index(Issue $issue): JsonResponse
    {
        $entries = $issue->timeEntries()->with('user')->get();
        return response()->json(['data' => TimeEntryResource::collection($entries)]);
    }

    public function store(Request $request, Issue $issue): JsonResponse
    {
        $request->validate([
            'started_at' => ['required', 'date'],
            'stopped_at' => ['nullable', 'date', 'after:started_at'],
            'duration_seconds' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $entry = $issue->timeEntries()->create([
            'user_id' => $request->user()->id,
            'started_at' => $request->started_at,
            'stopped_at' => $request->stopped_at,
            'duration_seconds' => $request->duration_seconds,
            'description' => $request->description,
        ]);

        return response()->json(['data' => new TimeEntryResource($entry->load('user'))], 201);
    }

    public function destroy(Issue $issue, TimeEntry $entry): JsonResponse
    {
        $entry->delete();
        return response()->json(null, 204);
    }
}
