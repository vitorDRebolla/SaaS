<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Http\Resources\AttachmentResource;
use App\Models\Attachment;
use App\Models\Issue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentController extends Controller
{
    public function index(Issue $issue): JsonResponse
    {
        $attachments = $issue->attachments()->with('user')->get();
        return response()->json(['data' => AttachmentResource::collection($attachments)]);
    }

    public function store(Request $request, Issue $issue): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:51200'],
        ]);

        $file = $request->file('file');
        $path = 'attachments/' . Str::uuid() . '/' . $file->getClientOriginalName();
        Storage::disk('s3')->put($path, $file->getContent());

        $attachment = $issue->attachments()->create([
            'user_id' => $request->user()->id,
            'name' => $file->getClientOriginalName(),
            'disk_path' => $path,
            'size_bytes' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);

        $attachment->load('user');
        return response()->json(['data' => new AttachmentResource($attachment)], 201);
    }

    public function destroy(Issue $issue, Attachment $attachment): JsonResponse
    {
        Storage::disk('s3')->delete($attachment->disk_path);
        $attachment->delete();
        return response()->json(null, 204);
    }
}
