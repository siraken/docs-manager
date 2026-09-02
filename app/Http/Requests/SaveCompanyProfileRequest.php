<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Application\Setting\Input\CompanyProfileInput;
use Illuminate\Foundation\Http\FormRequest;

final class SaveCompanyProfileRequest extends FormRequest
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
            'zipcode' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'rep' => ['nullable', 'string', 'max:255'],
            'tel_no' => ['nullable', 'string', 'max:50'],
            // 印影とロゴは resources/ からの相対パス。外に出られないよう .. を弾く
            'logo_url' => ['nullable', 'string', 'max:255', 'not_regex:/\.\./'],
            'com_stamp_url' => ['nullable', 'string', 'max:255', 'not_regex:/\.\./'],
            'rep_stamp_url' => ['nullable', 'string', 'max:255', 'not_regex:/\.\./'],
            'apply_stamp_url' => ['nullable', 'string', 'max:255', 'not_regex:/\.\./'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => '会社名',
            'zipcode' => '郵便番号',
            'address' => '住所',
            'rep' => '代表者名',
            'tel_no' => '電話番号',
        ];
    }

    public function toInput(): CompanyProfileInput
    {
        return new CompanyProfileInput(
            name: (string) $this->input('name'),
            zipcode: (string) $this->input('zipcode', ''),
            address: (string) $this->input('address', ''),
            representative: (string) $this->input('rep', ''),
            telNo: (string) $this->input('tel_no', ''),
            logoUrl: (string) $this->input('logo_url', ''),
            companyStampUrl: (string) $this->input('com_stamp_url', ''),
            representativeStampUrl: (string) $this->input('rep_stamp_url', ''),
            applyStampUrl: (string) $this->input('apply_stamp_url', ''),
        );
    }
}
