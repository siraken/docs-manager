<?php

declare(strict_types=1);

namespace App\Application\Customer\Input;

final readonly class CustomerInput
{
    public function __construct(
        public string $name,
        public bool $isCompany,
        public ?string $email,
        public ?string $phone,
        public ?string $postCode,
        public ?string $address,
        public ?string $city,
        public ?string $state,
        public ?string $country,
        public ?string $note,
    ) {
    }
}
