<?php

declare(strict_types=1);

namespace App\Application\Setting\Input;

final readonly class CompanyProfileInput
{
    public function __construct(
        public string $name,
        public string $zipcode,
        public string $address,
        public string $representative,
        public string $telNo,
        public string $logoUrl,
        public string $companyStampUrl,
        public string $representativeStampUrl,
        public string $applyStampUrl,
    ) {
    }
}
