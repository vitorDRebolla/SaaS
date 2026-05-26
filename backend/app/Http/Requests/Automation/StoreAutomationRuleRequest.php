<?php
namespace App\Http\Requests\Automation;
use Illuminate\Foundation\Http\FormRequest;

class StoreAutomationRuleRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'trigger_type' => ['required', 'string', 'in:issue_created,issue_status_changed,issue_assigned,issue_priority_changed'],
            'trigger_config' => ['required', 'array'],
            'active' => ['sometimes', 'boolean'],
            'actions' => ['required', 'array', 'min:1'],
            'actions.*.action_type' => ['required', 'string', 'in:change_status,assign_to,set_priority,add_label,send_webhook'],
            'actions.*.action_config' => ['required', 'array'],
        ];
    }
}
