<?php
namespace App\Http\Requests\Issue;
use Illuminate\Foundation\Http\FormRequest;

class UpdateIssueRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'priority' => ['sometimes', 'string', 'in:none,low,medium,high,urgent'],
            'status_id' => ['sometimes', 'integer', 'exists:issue_statuses,id'],
            'assignee_id' => ['nullable', 'integer', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
            'position' => ['sometimes', 'numeric'],
            'label_ids' => ['nullable', 'array'],
            'label_ids.*' => ['integer', 'exists:issue_labels,id'],
        ];
    }
}
