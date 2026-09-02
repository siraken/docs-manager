<?php

declare(strict_types=1);

namespace App\Presentation\Http\Requests;

use App\Application\Travel\Input\TravelInput;
use Illuminate\Foundation\Http\FormRequest;

final class SaveTravelRequest extends FormRequest
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
            'price' => ['nullable', 'numeric'],
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'apply_date' => ['required', 'date'],
            'apply_person' => ['required', 'string', 'max:255'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'rel_id' => '管理 ID',
            'dir' => '出張先',
            'purpose' => '目的',
            'price' => '金額',
            'date_from' => '出発日',
            'date_to' => '帰着日',
            'apply_date' => '申請日',
            'apply_person' => '申請者氏名',
        ];
    }

    public function toInput(): TravelInput
    {
        return new TravelInput(
            relId: (string) $this->input('rel_id'),
            destination: (string) $this->input('dir'),
            purpose: (string) $this->input('purpose'),
            price: $this->input('price'),
            dateFrom: $this->input('date_from'),
            dateTo: $this->input('date_to'),
            applyDate: $this->input('apply_date'),
            applyPerson: (string) $this->input('apply_person'),
        );
    }
}
