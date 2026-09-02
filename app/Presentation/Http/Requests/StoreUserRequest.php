<?php

declare(strict_types=1);

namespace App\Presentation\Http\Requests;

use App\Application\User\Input\CreateUserInput;
use Illuminate\Foundation\Http\FormRequest;

final class StoreUserRequest extends FormRequest
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
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => '名前',
            'email' => 'メールアドレス',
            'password' => 'パスワード',
        ];
    }

    public function toInput(): CreateUserInput
    {
        return new CreateUserInput(
            name: (string) $this->input('name'),
            email: (string) $this->input('email'),
            password: (string) $this->input('password'),
        );
    }
}
