<?php

declare(strict_types=1);

namespace App\Domain\Accounting\ValueObject;

/**
 * 貸借の別。
 *
 * 複式簿記では 1 つの取引を「借方」と「貸方」の 2 面で記録する。
 * どちらの側で残高が積み上がるかは勘定科目の区分で決まる (AccountType)。
 */
enum BalanceSide: string
{
    case Debit = 'debit';
    case Credit = 'credit';

    public function label(): string
    {
        return match ($this) {
            self::Debit => '借方',
            self::Credit => '貸方',
        };
    }

    public function opposite(): self
    {
        return match ($this) {
            self::Debit => self::Credit,
            self::Credit => self::Debit,
        };
    }
}
