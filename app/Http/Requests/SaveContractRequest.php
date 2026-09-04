<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Application\Contract\Input\ContractInput;
use Illuminate\Foundation\Http\FormRequest;

final class SaveContractRequest extends FormRequest
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
            'contract_no' => ['nullable', 'string', 'max:32'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'start_date' => ['nullable', 'date'],
            // 期間の前後関係はドメイン (ContractTerm) でも守られるが、
            // 画面に項目単位でエラーを出したいのでここでも見る
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => '契約名',
            'contract_no' => '契約番号',
            'customer_id' => '取引先',
            'start_date' => '契約開始日',
            'end_date' => '契約終了日',
            'description' => '備考',
        ];
    }

    public function toInput(): ContractInput
    {
        return new ContractInput(
            name: (string) $this->input('name'),
            contractNo: $this->stringOrNull('contract_no'),
            customerId: $this->intOrNull('customer_id'),
            startDate: $this->input('start_date'),
            endDate: $this->input('end_date'),
            description: $this->stringOrNull('description'),
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
