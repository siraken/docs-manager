<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Application\Academy\Input\AcademyInquiryInput;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Lumo Academy の問い合わせ (認証不要の公開エンドポイント)。
 *
 * 移行前は $request->all() をそのまま fill() していた。
 */
final class RegisterAcademyInquiryRequest extends FormRequest
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
            'email' => ['required', 'email', 'max:255'],
            // 移行前のコントローラは $request['body'] を読んでいたが、
            // 保存先のカラムは inquiry。どちらの名前でも受けられるようにしておく。
            'body' => ['required_without:inquiry', 'nullable', 'string', 'max:5000'],
            'inquiry' => ['required_without:body', 'nullable', 'string', 'max:5000'],
        ];
    }

    public function toInput(): AcademyInquiryInput
    {
        return new AcademyInquiryInput(
            name: (string) $this->input('name'),
            email: (string) $this->input('email'),
            inquiry: (string) ($this->input('inquiry') ?? $this->input('body')),
        );
    }
}
