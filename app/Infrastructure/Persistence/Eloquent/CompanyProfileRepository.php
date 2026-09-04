<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Application\Shared\DateParser;
use App\Domain\Setting\Entity\CompanyProfile;
use App\Domain\Setting\Repository\CompanyProfileRepositoryInterface;
use App\Domain\Shared\ValueObject\Money;
use App\Infrastructure\Persistence\Eloquent\Models\Setting;

/**
 * settings テーブルは 1 レコードだけを使う。複数行あっても先頭を正とする。
 */
final class CompanyProfileRepository implements CompanyProfileRepositoryInterface
{
    public function find(): ?CompanyProfile
    {
        $model = Setting::orderBy('id')->first();

        if ($model === null) {
            return null;
        }

        return CompanyProfile::reconstitute(
            id: (int) $model->id,
            name: (string) $model->name,
            nameEn: (string) ($model->name_en ?? ''),
            zipcode: (string) $model->zipcode,
            address: (string) $model->address,
            representative: (string) $model->rep,
            telNo: (string) $model->tel_no,
            established: DateParser::parseNullable($model->established, '設立年月日'),
            // sqlite は integer カラムを文字列で返す
            capital: $model->capital === null ? null : Money::fromNumeric($model->capital),
            bank: (string) ($model->bank ?? ''),
            logoUrl: (string) $model->logo_url,
            companyStampUrl: (string) $model->com_stamp_url,
            representativeStampUrl: (string) $model->rep_stamp_url,
            applyStampUrl: (string) $model->apply_stamp_url,
        );
    }

    public function save(CompanyProfile $profile): CompanyProfile
    {
        $model = $profile->id() === null
            ? (Setting::orderBy('id')->first() ?? new Setting())
            : Setting::find($profile->id()) ?? new Setting();

        $model->fill([
            'name' => $profile->name(),
            'name_en' => $profile->nameEn(),
            'zipcode' => $profile->zipcode(),
            'address' => $profile->address(),
            'rep' => $profile->representative(),
            'tel_no' => $profile->telNo(),
            'established' => $profile->established()?->format('Y-m-d'),
            'capital' => $profile->capital()?->amount,
            'bank' => $profile->bank(),
            'logo_url' => $profile->logoUrl(),
            'com_stamp_url' => $profile->companyStampUrl(),
            'rep_stamp_url' => $profile->representativeStampUrl(),
            'apply_stamp_url' => $profile->applyStampUrl(),
        ]);
        $model->save();

        $profile->assignId((int) $model->id);

        return $profile;
    }
}
