<?php

declare(strict_types=1);

namespace App\Application\Travel\Input;

use App\Application\Shared\DateParser;
use App\Domain\Shared\ValueObject\Money;
use App\Domain\Travel\Entity\Travel;

final readonly class TravelInput
{
    public function __construct(
        public string $relId,
        public string $destination,
        public string $purpose,
        public mixed $price,
        public mixed $dateFrom,
        public mixed $dateTo,
        public mixed $applyDate,
        public string $applyPerson,
    ) {
    }

    /** CSV の 1 行から作る。先頭列は連番として使われず読み飛ばす (移行前からの仕様) */
    public static function fromCsvRow(array $row): self
    {
        return new self(
            relId: (string) ($row[1] ?? ''),
            destination: (string) ($row[2] ?? ''),
            purpose: (string) ($row[3] ?? ''),
            price: $row[4] ?? 0,
            dateFrom: $row[5] ?? null,
            dateTo: $row[6] ?? null,
            applyDate: $row[7] ?? null,
            applyPerson: (string) ($row[8] ?? ''),
        );
    }

    public function toTravel(): Travel
    {
        return Travel::create(
            relId: $this->relId,
            destination: $this->destination,
            purpose: $this->purpose,
            price: Money::fromNumeric($this->price),
            dateFrom: DateParser::parse($this->dateFrom, '出発日'),
            dateTo: DateParser::parse($this->dateTo, '帰着日'),
            applyDate: DateParser::parse($this->applyDate, '申請日'),
            applyPerson: $this->applyPerson,
        );
    }
}
