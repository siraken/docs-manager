<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Application\Order\Input\OrderInput;
use App\Application\Order\Input\OrderLineInput;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 発注書の作成・更新。
 *
 * 明細は item_name[] / qty[] / unit[] / cost[] / tax[] という並列の配列で届く
 * (行ごとの id は持たない)。この HTTP 固有の形をユースケースに漏らさないよう、
 * ここで OrderInput に組み替える。
 *
 * price[] は受け取らない。フロントが表示用に計算した値で、保存する金額は
 * サーバー側で計算し直すため。
 */
final class SaveOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        // 認証は login ミドルウェアが担当する
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            // TODO: exists:customers,id を付けたいが、顧客を消したあとに既存の
            //       発注書を編集できなくなるため保留にしている。顧客に論理削除を
            //       入れてから有効化すること。
            'customer_id' => ['required', 'integer', 'min:1'],
            'responsible' => ['nullable', 'string', 'max:255'],
            'honor_title' => ['nullable', 'string', 'max:50'],
            'issued_date' => ['required', 'date'],
            'exp_date' => ['nullable', 'date'],
            'order_no' => ['required', 'string', 'max:255', 'not_regex:#[/\\\\]#'],
            'title' => ['nullable', 'string', 'max:70'],
            'remarks' => ['nullable', 'string', 'max:1000'],

            'item_name' => ['array'],
            'item_name.*' => ['nullable', 'string', 'max:255'],
            'qty' => ['array'],
            'qty.*' => ['nullable', 'numeric'],
            'unit' => ['array'],
            'unit.*' => ['nullable', 'string', 'max:50'],
            'cost' => ['array'],
            'cost.*' => ['nullable', 'numeric'],
            'tax' => ['array'],
            'tax.*' => ['nullable', 'integer', 'between:1,5'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'customer_id' => '取引先',
            'issued_date' => '発行日',
            'exp_date' => '有効期限',
            'order_no' => '発注書番号',
            'title' => '件名',
            'remarks' => '備考',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'order_no.not_regex' => '発注書番号にパス区切り文字は使えません。',
        ];
    }

    public function toInput(): OrderInput
    {
        return new OrderInput(
            customerId: (int) $this->input('customer_id'),
            responsible: $this->stringOrNull('responsible'),
            honorTitle: $this->stringOrNull('honor_title'),
            issuedDate: $this->input('issued_date'),
            expDate: $this->input('exp_date'),
            orderNo: (string) $this->input('order_no'),
            title: $this->stringOrNull('title'),
            remarks: $this->stringOrNull('remarks'),
            lines: $this->lines(),
        );
    }

    /** @return list<OrderLineInput> */
    private function lines(): array
    {
        $itemNames = (array) $this->input('item_name', []);
        $quantities = (array) $this->input('qty', []);
        $units = (array) $this->input('unit', []);
        $costs = (array) $this->input('cost', []);
        $taxes = (array) $this->input('tax', []);

        $lines = [];

        foreach (array_keys($itemNames) as $index) {
            $lines[] = new OrderLineInput(
                itemName: $itemNames[$index] === null ? null : (string) $itemNames[$index],
                quantity: $quantities[$index] ?? null,
                unit: isset($units[$index]) ? (string) $units[$index] : null,
                unitCost: $costs[$index] ?? null,
                taxId: $taxes[$index] ?? null,
            );
        }

        return $lines;
    }

    private function stringOrNull(string $key): ?string
    {
        $value = $this->input($key);

        return $value === null || $value === '' ? null : (string) $value;
    }
}
