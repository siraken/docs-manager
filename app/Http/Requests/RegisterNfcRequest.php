<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Application\User\Input\NfcRegistrationInput;
use Illuminate\Foundation\Http\FormRequest;

final class RegisterNfcRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'serial_number' => ['required', 'string', 'max:255'],
            'pin' => ['required', 'string', 'max:255'],
        ];
    }

    public function toInput(): NfcRegistrationInput
    {
        return new NfcRegistrationInput(
            serialNumber: (string) $this->input('serial_number'),
            pin: (string) $this->input('pin'),
        );
    }
}
