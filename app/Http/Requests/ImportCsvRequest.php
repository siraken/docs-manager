<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * CSV 取り込み。
 *
 * mimes: での判定はしていない。CSV は環境によって text/plain や
 * application/vnd.ms-excel として送られ、正しいファイルまで弾かれるため。
 */
final class ImportCsvRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'csv' => ['required', 'file', 'max:10240'],
            'header' => ['nullable'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['csv' => 'CSV ファイル'];
    }

    public function csvPath(): string
    {
        return (string) $this->file('csv')?->getRealPath();
    }

    /** 1 行目を見出しとして読み飛ばすか */
    public function hasHeaderRow(): bool
    {
        return $this->boolean('header');
    }
}
