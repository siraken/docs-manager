<?php

declare(strict_types=1);

namespace App\Http\ViewModels;

use App\Domain\Setting\Entity\CompanyProfile;

final readonly class CompanyProfileView implements \JsonSerializable
{
    private function __construct(
        public string $name,
        public string $nameEn,
        public string $zipcode,
        public string $address,
        public string $representative,
        public string $telNo,
        public ?string $established,
        public string $establishedLabel,
        public ?int $capital,
        public string $capitalLabel,
        public string $bank,
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
            nameEn: $profile->nameEn(),
            zipcode: $profile->zipcode(),
            address: $profile->address(),
            representative: $profile->representative(),
            telNo: $profile->telNo(),
            // 前者はフォームの value、後者は表示に使う
            established: $profile->established()?->format('Y-m-d'),
            establishedLabel: $profile->established()?->format('Y年n月j日') ?? '-',
            capital: $profile->capital()?->amount,
            capitalLabel: $profile->capital()?->format() ?? '-',
            bank: $profile->bank(),
            logoUrl: $profile->logoUrl(),
            companyStampUrl: $profile->companyStampUrl(),
            representativeStampUrl: $profile->representativeStampUrl(),
            applyStampUrl: $profile->applyStampUrl(),
            // 未保存 (既定値をそのまま出している) かどうか。画面に注意書きを出す
            isDefault: $profile->id() === null,
        );
    }

    /**
     * Inertia の props 用。
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'nameEn' => $this->nameEn,
            'zipcode' => $this->zipcode,
            'address' => $this->address,
            'representative' => $this->representative,
            'telNo' => $this->telNo,
            'established' => $this->established,
            'establishedLabel' => $this->establishedLabel,
            'capital' => $this->capital,
            'capitalLabel' => $this->capitalLabel,
            'bank' => $this->bank,
            'logoUrl' => $this->logoUrl,
            'companyStampUrl' => $this->companyStampUrl,
            'representativeStampUrl' => $this->representativeStampUrl,
            'applyStampUrl' => $this->applyStampUrl,
            'isDefault' => $this->isDefault,
        ];
    }
}
