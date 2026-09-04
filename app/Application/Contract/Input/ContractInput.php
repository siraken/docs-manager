<?php

declare(strict_types=1);

namespace App\Application\Contract\Input;

use App\Application\Shared\DateParser;
use App\Domain\Contract\ValueObject\ContractTerm;

final readonly class ContractInput
{
    public function __construct(
        public string $name,
        public ?string $contractNo,
        public ?int $customerId,
        public mixed $startDate,
        public mixed $endDate,
        public ?string $description,
    ) {
    }

    /** 期間の前後関係の検証は ContractTerm が持つ */
    public function termValue(): ContractTerm
    {
        return ContractTerm::of(
            DateParser::parseNullable($this->startDate, '契約開始日'),
            DateParser::parseNullable($this->endDate, '契約終了日'),
        );
    }
}
