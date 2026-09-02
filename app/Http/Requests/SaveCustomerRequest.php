<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Application\Customer\Input\CustomerInput;
use Illuminate\Foundation\Http\FormRequest;

final class SaveCustomerRequest extends FormRequest
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
            'is_company' => ['nullable'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'post_code' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => '顧客名',
            'email' => 'メールアドレス',
            'phone' => '電話番号',
            'post_code' => '郵便番号',
            'address' => '住所',
        ];
    }

    public function toInput(): CustomerInput
    {
        return new CustomerInput(
            name: (string) $this->input('name'),
            // チェックボックスは未チェックだと送信されないので boolean() で受ける
            isCompany: $this->boolean('is_company'),
            email: $this->stringOrNull('email'),
            phone: $this->stringOrNull('phone'),
            postCode: $this->stringOrNull('post_code'),
            address: $this->stringOrNull('address'),
            city: $this->stringOrNull('city'),
            state: $this->stringOrNull('state'),
            country: $this->stringOrNull('country'),
            note: $this->stringOrNull('note'),
        );
    }

    private function stringOrNull(string $key): ?string
    {
        $value = $this->input($key);

        return $value === null || $value === '' ? null : (string) $value;
    }
}
