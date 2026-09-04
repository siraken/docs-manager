<?php

declare(strict_types=1);

namespace App\Application\Accounting\Input;

use App\Domain\Accounting\ValueObject\AccountType;

final readonly class AccountInput
{
    public function __construct(
        public string $name,
        public ?string $code,
        public mixed $type,
        public bool $isActive,
        public ?string $note,
    ) {
    }

    public function typeValue(): AccountType
    {
        return AccountType::tryFrom((string) $this->type) ?? AccountType::Asset;
    }
}
