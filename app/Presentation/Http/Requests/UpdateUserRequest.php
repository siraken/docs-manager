<?php

declare(strict_types=1);

namespace App\Presentation\Http\Requests;

use App\Application\User\Input\UpdateUserInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * ユーザー編集。パスワードと NFC PIN は空なら現在値を維持する。
 */
final class UpdateUserRequest extends FormRequest
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
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->route('id')),
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'nfc_serial_number' => ['nullable', 'string', 'max:255'],
            'nfc_pin' => ['nullable', 'string', 'max:255'],
            // MetaMask が返すアドレスは 0x + 40 桁の 16 進数で固定
            'wallet_address' => ['nullable', 'string', 'regex:/^0x[0-9a-fA-F]{40}$/'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => '名前',
            'email' => 'メールアドレス',
            'password' => 'パスワード',
            'nfc_serial_number' => 'NFC シリアル番号',
            'nfc_pin' => 'NFC PIN',
            'wallet_address' => 'ウォレットアドレス',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'wallet_address.regex' => 'ウォレットアドレスは 0x で始まる 42 文字で入力してください。',
        ];
    }

    public function toInput(): UpdateUserInput
    {
        return new UpdateUserInput(
            name: (string) $this->input('name'),
            email: (string) $this->input('email'),
            password: $this->stringOrNull('password'),
            nfcSerialNumber: $this->stringOrNull('nfc_serial_number'),
            nfcPin: $this->stringOrNull('nfc_pin'),
            walletAddress: $this->stringOrNull('wallet_address'),
        );
    }

    private function stringOrNull(string $key): ?string
    {
        $value = $this->input($key);

        return $value === null || $value === '' ? null : (string) $value;
    }
}
