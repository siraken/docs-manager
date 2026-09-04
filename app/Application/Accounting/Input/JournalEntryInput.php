<?php

declare(strict_types=1);

namespace App\Application\Accounting\Input;

use App\Application\Shared\DateParser;
use App\Domain\Shared\ValueObject\Money;

final readonly class JournalEntryInput
{
    public function __construct(
        public mixed $date,
        public int $debitAccountId,
        public int $creditAccountId,
        public mixed $amount,
        public string $description,
        public ?string $note,
    ) {
    }

    public function dateValue(): \DateTimeImmutable
    {
        return DateParser::parse($this->date, '日付');
    }

    public function amountValue(): Money
    {
        return Money::fromNumeric($this->amount);
    }
}
