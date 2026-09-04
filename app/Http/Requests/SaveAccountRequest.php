<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Application\Accounting\Input\AccountInput;
use App\Domain\Accounting\ValueObject\AccountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SaveAccountRequest extends FormRequest
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
            // コードは任意だが、付けるなら重複させない (自分自身は除く)
            'code' => [
                'nullable',
                'string',
                'max:16',
                Rule::unique('accounts', 'code')->ignore($this->route('id')),
            ],
            'type' => ['required', Rule::in(array_column(AccountType::cases(), 'value'))],
            'is_active' => ['nullable'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => '科目名',
            'code' => '科目コード',
            'type' => '区分',
            'note' => '備考',
        ];
    }

    public function toInput(): AccountInput
    {
        return new AccountInput(
            name: (string) $this->input('name'),
            code: $this->stringOrNull('code'),
            type: $this->input('type'),
            // チェックボックスは未チェックだと送信されない
            isActive: $this->boolean('is_active'),
            note: $this->stringOrNull('note'),
        );
    }

    private function stringOrNull(string $key): ?string
    {
        $value = $this->input($key);

        return $value === null || $value === '' ? null : (string) $value;
    }
}
