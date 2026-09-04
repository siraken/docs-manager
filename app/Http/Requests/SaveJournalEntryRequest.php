<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Application\Accounting\Input\JournalEntryInput;
use Illuminate\Foundation\Http\FormRequest;

final class SaveJournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 参考実装 (CakePHP 時代の posts) は検証が一切無く、金額も type="text" で
     * 受けていた。
     *
     * 借方と貸方が同じ科目でないことはドメイン (JournalEntry) でも守られるが、
     * 画面に項目単位でエラーを出したいのでここでも見る。
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'debit_account_id' => ['required', 'integer', 'exists:accounts,id'],
            'credit_account_id' => [
                'required',
                'integer',
                'exists:accounts,id',
                'different:debit_account_id',
            ],
            // 0 円の仕訳は意味を成さない
            'amount' => ['required', 'integer', 'min:1'],
            'description' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'date' => '日付',
            'debit_account_id' => '借方科目',
            'credit_account_id' => '貸方科目',
            'amount' => '金額',
            'description' => '摘要',
            'note' => 'その他',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'credit_account_id.different' => '借方と貸方に同じ勘定科目は指定できません。',
        ];
    }

    public function toInput(): JournalEntryInput
    {
        return new JournalEntryInput(
            date: $this->input('date'),
            debitAccountId: (int) $this->input('debit_account_id'),
            creditAccountId: (int) $this->input('credit_account_id'),
            amount: $this->input('amount'),
            description: (string) $this->input('description'),
            note: $this->stringOrNull('note'),
        );
    }

    private function stringOrNull(string $key): ?string
    {
        $value = $this->input($key);

        return $value === null || $value === '' ? null : (string) $value;
    }
}
