<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Application\Learning\Input\EnrollmentInput;
use App\Domain\Learning\ValueObject\EnrollmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SaveEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 状態と日付の整合はドメイン (Enrollment) でも守られるが、
     * 画面に項目単位でエラーを出したいのでここでも見る。
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'status' => ['required', Rule::in(array_column(EnrollmentStatus::cases(), 'value'))],
            'started_at' => ['nullable', 'date'],
            'completed_at' => [
                'nullable',
                'date',
                'after_or_equal:started_at',
                // 完了にするなら完了日が要る
                Rule::requiredIf(fn (): bool => $this->input('status') === EnrollmentStatus::Completed->value),
            ],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'user_id' => '受講者',
            'course_id' => '講座',
            'status' => '状態',
            'started_at' => '受講開始日',
            'completed_at' => '完了日',
            'note' => 'メモ',
        ];
    }

    public function toInput(): EnrollmentInput
    {
        return new EnrollmentInput(
            userId: (int) $this->input('user_id'),
            courseId: (int) $this->input('course_id'),
            status: $this->input('status'),
            startedAt: $this->input('started_at'),
            completedAt: $this->input('completed_at'),
            note: $this->stringOrNull('note'),
        );
    }

    private function stringOrNull(string $key): ?string
    {
        $value = $this->input($key);

        return $value === null || $value === '' ? null : (string) $value;
    }
}
