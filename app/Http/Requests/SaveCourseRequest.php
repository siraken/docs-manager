<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Application\Learning\Input\CourseInput;
use Illuminate\Foundation\Http\FormRequest;

final class SaveCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'exp' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'is_published' => ['nullable'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'title' => '講座名',
            'description' => '説明',
            'exp' => '獲得ポイント',
        ];
    }

    public function toInput(): CourseInput
    {
        return new CourseInput(
            title: (string) $this->input('title'),
            description: $this->stringOrNull('description'),
            exp: $this->input('exp'),
            // チェックボックスは未チェックだと送信されない
            isPublished: $this->boolean('is_published'),
        );
    }

    private function stringOrNull(string $key): ?string
    {
        $value = $this->input($key);

        return $value === null || $value === '' ? null : (string) $value;
    }
}
