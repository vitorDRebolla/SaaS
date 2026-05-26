<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Http\Requests\Automation\StoreAutomationRuleRequest;
use App\Http\Resources\AutomationRuleResource;
use App\Models\AutomationRule;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AutomationController extends Controller
{
    public function index(Project $project): JsonResponse
    {
        $rules = $project->automationRules()->with('actions')->get();
        return response()->json(['data' => AutomationRuleResource::collection($rules)]);
    }

    public function store(StoreAutomationRuleRequest $request, Project $project): JsonResponse
    {
        $rule = $project->automationRules()->create([
            'name' => $request->name,
            'trigger_type' => $request->trigger_type,
            'trigger_config' => $request->trigger_config,
            'active' => $request->active ?? true,
        ]);

        foreach ($request->actions as $i => $action) {
            $rule->actions()->create([
                'action_type' => $action['action_type'],
                'action_config' => $action['action_config'],
                'position' => $i,
            ]);
        }

        return response()->json(['data' => new AutomationRuleResource($rule->load('actions'))], 201);
    }

    public function update(Request $request, Project $project, AutomationRule $rule): JsonResponse
    {
        $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'active' => ['sometimes', 'boolean'],
        ]);

        $rule->update($request->only('name', 'active'));
        return response()->json(['data' => new AutomationRuleResource($rule->load('actions'))]);
    }

    public function destroy(Project $project, AutomationRule $rule): JsonResponse
    {
        $rule->delete();
        return response()->json(null, 204);
    }
}
