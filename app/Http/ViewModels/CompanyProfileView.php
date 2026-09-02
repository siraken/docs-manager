<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\Setting\Entity\CompanyProfile;

final readonly class CompanyProfileView
{
    private function __construct(
        public string $name,
        public string $zipcode,
        public string $address,
        public string $representative,
        public string $telNo,
        public string $logoUrl,
        public string $companyStampUrl,
        public string $representativeStampUrl,
        public string $applyStampUrl,
        public bool $isDefault,
    ) {
    }

    public static function fromEntity(CompanyProfile $profile): self
    {
        return new self(
            name: $profile->name(),
            zipcode: $profile->zipcode(),
            address: $profile->address(),
            representative: $profile->representative(),
            telNo: $profile->telNo(),
            logoUrl: $profile->logoUrl(),
            companyStampUrl: $profile->companyStampUrl(),
            representativeStampUrl: $profile->representativeStampUrl(),
            applyStampUrl: $profile->applyStampUrl(),
            // 未保存 (既定値をそのまま出している) かどうか。画面に注意書きを出す
            isDefault: $profile->id() === null,
        );
    }
}
