<?php

declare(strict_types=1);

namespace App\Application\Accounting\Output;

use App\Domain\Accounting\ValueObject\AccountBalance;
use App\Domain\Shared\ValueObject\Money;

/**
 * 残高試算表。
 *
 * 縦計 (借方合計・貸方合計) は単一仕訳しか作れない以上、必ず一致する。
 * 一致しない結果が出たらそれ自体が不具合なので、画面で検算できるよう
 * 両方の合計を持たせている。
 */
final readonly class TrialBalance
{
    /** @param list<AccountBalance> $rows */
    private function __construct(
        public array $rows,
        public Money $debitTotal,
        public Money $creditTotal,
        public Money $debitBalanceTotal,
        public Money $creditBalanceTotal,
        public ?int $year,
        public ?int $month,
    ) {
    }

    /** @param list<AccountBalance> $rows */
    public static function of(array $rows, ?int $year, ?int $month): self
    {
        $debitTotal = Money::zero();
        $creditTotal = Money::zero();
        $debitBalanceTotal = Money::zero();
        $creditBalanceTotal = Money::zero();

        foreach ($rows as $row) {
            $debitTotal = $debitTotal->add($row->debitTotal);
            $creditTotal = $creditTotal->add($row->creditTotal);
            $debitBalanceTotal = $debitBalanceTotal->add($row->debitBalance());
            $creditBalanceTotal = $creditBalanceTotal->add($row->creditBalance());
        }

        return new self(
            $rows,
            $debitTotal,
            $creditTotal,
            $debitBalanceTotal,
            $creditBalanceTotal,
            $year,
            $month,
        );
    }

    /** 貸借が一致しているか。画面に検算の結果を出すために使う */
    public function isBalanced(): bool
    {
        return $this->debitTotal->equals($this->creditTotal)
            && $this->debitBalanceTotal->equals($this->creditBalanceTotal);
    }
}
