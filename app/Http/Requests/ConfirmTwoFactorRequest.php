<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ConfirmTwoFactorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'digits:6'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['code' => '認証コード'];
    }

    public function code(): string
    {
        return (string) $this->input('code');
    }
}
