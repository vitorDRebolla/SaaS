<?php
namespace App\Http\Requests\Team;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMemberRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'role' => ['required', 'string', 'in:admin,member,viewer'],
        ];
    }
}
