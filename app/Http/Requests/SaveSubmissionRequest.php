<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Application\Learning\Input\SubmissionInput;
use App\Domain\Learning\ValueObject\SubmissionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SaveSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 状態と提出日の整合はドメイン (Submission) でも守られるが、
     * 画面に項目単位でエラーを出したいのでここでも見る。
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'assignment_id' => ['required', 'integer', 'exists:assignments,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'status' => ['required', Rule::in(array_column(SubmissionStatus::cases(), 'value'))],
            'submitted_at' => [
                'nullable',
                'date',
                // 未提出以外は提出日が要る
                Rule::requiredIf(fn (): bool => SubmissionStatus::fromNullable($this->input('status'))
                    ->requiresSubmittedAt()),
            ],
            'body' => ['nullable', 'string', 'max:5000'],
            'feedback' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'assignment_id' => '課題',
            'user_id' => '提出者',
            'status' => '状態',
            'submitted_at' => '提出日',
            'body' => '提出内容',
            'feedback' => '講評',
        ];
    }

    public function toInput(): SubmissionInput
    {
        return new SubmissionInput(
            assignmentId: (int) $this->input('assignment_id'),
            userId: (int) $this->input('user_id'),
            status: $this->input('status'),
            submittedAt: $this->input('submitted_at'),
            body: $this->stringOrNull('body'),
            feedback: $this->stringOrNull('feedback'),
        );
    }

    private function stringOrNull(string $key): ?string
    {
        $value = $this->input($key);

        return $value === null || $value === '' ? null : (string) $value;
    }
}
