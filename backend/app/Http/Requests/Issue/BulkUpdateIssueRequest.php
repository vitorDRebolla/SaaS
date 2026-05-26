<?php
namespace App\Http\Requests\Issue;
use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateIssueRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'issue_ids' => ['required', 'array', 'min:1'],
            'issue_ids.*' => ['integer', 'exists:issues,id'],
            'status_id' => ['sometimes', 'integer', 'exists:issue_statuses,id'],
            'assignee_id' => ['nullable', 'integer', 'exists:users,id'],
            'priority' => ['sometimes', 'string', 'in:none,low,medium,high,urgent'],
        ];
    }
}
