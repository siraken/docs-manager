<?php

declare(strict_types=1);

namespace App\Application\Setting\Input;

use App\Application\Shared\DateParser;
use App\Domain\Shared\ValueObject\Money;

final readonly class CompanyProfileInput
{
    public function __construct(
        public string $name,
        public string $nameEn,
        public string $zipcode,
        public string $address,
        public string $representative,
        public string $telNo,
        public mixed $established,
        public mixed $capital,
        public string $bank,
        public string $logoUrl,
        public string $companyStampUrl,
        public string $representativeStampUrl,
        public string $applyStampUrl,
    ) {
    }

    public function establishedValue(): ?\DateTimeImmutable
    {
        return DateParser::parseNullable($this->established, '設立年月日');
    }

    /** 未入力は null。0 円の資本金と区別する */
    public function capitalValue(): ?Money
    {
        if ($this->capital === null || $this->capital === '') {
            return null;
        }

        return Money::fromNumeric($this->capital);
    }
}
