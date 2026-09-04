<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Application\Project\Input\ProjectInput;
use App\Domain\Project\ValueObject\ProjectStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SaveProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'client_id' => ['nullable', 'integer', 'exists:customers,id'],
            // 書式そのものの検証は JiraKey が行う。ここでは長さだけ見る
            'jira_key' => ['nullable', 'string', 'max:32'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'payment_date' => ['nullable', 'date'],
            'price' => ['nullable', 'numeric'],
            'status' => ['nullable', Rule::in(array_column(ProjectStatus::cases(), 'value'))],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => '案件名',
            'client_id' => '取引先',
            'jira_key' => 'Jira のキー',
            'description' => '備考',
            'price' => '請求金額',
            'status' => '状態',
        ];
    }

    public function toInput(): ProjectInput
    {
        return new ProjectInput(
            name: (string) $this->input('name'),
            description: $this->stringOrNull('description'),
            clientId: $this->intOrNull('client_id'),
            jiraKey: $this->stringOrNull('jira_key'),
            startDate: $this->input('start_date'),
            endDate: $this->input('end_date'),
            paymentDate: $this->input('payment_date'),
            price: $this->input('price'),
            status: $this->input('status'),
        );
    }

    private function stringOrNull(string $key): ?string
    {
        $value = $this->input($key);

        return $value === null || $value === '' ? null : (string) $value;
    }

    private function intOrNull(string $key): ?int
    {
        $value = $this->input($key);

        return $value === null || $value === '' ? null : (int) $value;
    }
}
