<?php

declare(strict_types=1);

namespace App\Application\Accounting\Output;

use App\Domain\Accounting\Entity\JournalEntry;
use App\Domain\Shared\ValueObject\Money;

/**
 * 仕訳帳の検索結果と、その合計。
 *
 * 合計は絞り込み結果の全件が対象。参考にした移植元の一覧には合計が無かった。
 */
final readonly class Journal
{
    /** @param list<JournalEntry> $entries */
    private function __construct(
        public array $entries,
        public Money $total,
    ) {
    }

    /** @param list<JournalEntry> $entries */
    public static function of(array $entries): self
    {
        $total = Money::zero();

        foreach ($entries as $entry) {
            $total = $total->add($entry->amount());
        }

        return new self($entries, $total);
    }
}
