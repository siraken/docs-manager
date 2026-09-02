<?php

declare(strict_types=1);

namespace App\Domain\Order\ValueObject;

/**
 * 発行状態 (order_headers.is_issued)。押すたびに未発行 / 発行済みを往復する。
 */
enum IssueStatus: int
{
    case NotIssued = 0;
    case Issued = 1;

    public static function fromNullable(mixed $value): self
    {
        // sqlite は integer カラムを文字列で返すことがあるため int に寄せる
        return self::tryFrom((int) $value) ?? self::NotIssued;
    }

    /** 一覧のピルを押したときの次の状態 */
    public function next(): self
    {
        return $this === self::NotIssued ? self::Issued : self::NotIssued;
    }

    public function label(): string
    {
        return match ($this) {
            self::NotIssued => '未発行',
            self::Issued => '発行済み',
        };
    }
}
