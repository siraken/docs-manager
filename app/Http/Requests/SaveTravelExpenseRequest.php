<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Application\Travel\Input\TravelExpenseInput;
use Illuminate\Foundation\Http\FormRequest;

final class SaveTravelExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'rel_id' => ['required', 'string', 'max:255'],
            'dir' => ['required', 'string', 'max:255'],
            'purpose' => ['required', 'string', 'max:2000'],
            'apply_date' => ['required', 'date'],
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'pay_date' => ['required', 'date'],
            'apply_person' => ['required', 'string', 'max:255'],
            'trans_fee' => ['nullable', 'numeric', 'min:0'],
            'acm_fee' => ['nullable', 'numeric', 'min:0'],
            'gas_fee' => ['nullable', 'numeric', 'min:0'],
            'dinner_fee' => ['nullable', 'numeric', 'min:0'],
            'lunch_fee' => ['nullable', 'numeric', 'min:0'],
            'daily_pay' => ['nullable', 'numeric', 'min:0'],
            // total_fee は受け取らない (内訳から計算する)
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'rel_id' => '管理 ID',
            'dir' => '出張先',
            'purpose' => '目的',
            'apply_date' => '申請日',
            'date_from' => '出発日',
            'date_to' => '帰着日',
            'pay_date' => '精算日',
            'apply_person' => '申請者氏名',
            'trans_fee' => '交通費',
            'acm_fee' => '宿泊費',
            'gas_fee' => 'ガソリン代',
            'dinner_fee' => '夕食代',
            'lunch_fee' => '昼食代',
            'daily_pay' => '日当',
        ];
    }

    public function toInput(): TravelExpenseInput
    {
        return new TravelExpenseInput(
            relId: (string) $this->input('rel_id'),
            destination: (string) $this->input('dir'),
            purpose: (string) $this->input('purpose'),
            applyDate: $this->input('apply_date'),
            dateFrom: $this->input('date_from'),
            dateTo: $this->input('date_to'),
            payDate: $this->input('pay_date'),
            applyPerson: (string) $this->input('apply_person'),
            transportationFee: $this->input('trans_fee'),
            accommodationFee: $this->input('acm_fee'),
            gasFee: $this->input('gas_fee'),
            dinnerFee: $this->input('dinner_fee'),
            lunchFee: $this->input('lunch_fee'),
            dailyAllowance: $this->input('daily_pay'),
        );
    }
}
