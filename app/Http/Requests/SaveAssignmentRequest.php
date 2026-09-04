<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Application\Learning\Input\AssignmentInput;
use Illuminate\Foundation\Http\FormRequest;

final class SaveAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'due_on' => ['nullable', 'date'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'course_id' => '講座',
            'title' => '課題名',
            'description' => '内容',
            'due_on' => '提出期限',
        ];
    }

    public function toInput(): AssignmentInput
    {
        return new AssignmentInput(
            courseId: (int) $this->input('course_id'),
            title: (string) $this->input('title'),
            description: $this->stringOrNull('description'),
            dueOn: $this->input('due_on'),
        );
    }

    private function stringOrNull(string $key): ?string
    {
        $value = $this->input($key);

        return $value === null || $value === '' ? null : (string) $value;
    }
}
