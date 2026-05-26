<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Http\Resources\FeatureFlagResource;
use App\Models\FeatureFlag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeatureFlagController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => FeatureFlagResource::collection(FeatureFlag::all())]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'alpha_dash', 'unique:feature_flags,name'],
            'description' => ['nullable', 'string'],
            'globally_enabled' => ['boolean'],
            'rollout_percentage' => ['integer', 'min:0', 'max:100'],
            'allowed_team_ids' => ['array'],
        ]);

        $flag = FeatureFlag::create($request->validated());
        return response()->json(['data' => new FeatureFlagResource($flag)], 201);
    }

    public function update(Request $request, FeatureFlag $flag): JsonResponse
    {
        $request->validate([
            'globally_enabled' => ['boolean'],
            'rollout_percentage' => ['integer', 'min:0', 'max:100'],
            'allowed_team_ids' => ['array'],
            'description' => ['nullable', 'string'],
        ]);

        $flag->update($request->validated());
        return response()->json(['data' => new FeatureFlagResource($flag->fresh())]);
    }

    public function destroy(FeatureFlag $flag): JsonResponse
    {
        $flag->delete();
        return response()->json(null, 204);
    }
}
