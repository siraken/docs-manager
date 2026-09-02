<?php

declare(strict_types=1);

namespace App\Domain\Order\ValueObject;

/**
 * 受注状態 (order_headers.is_ordered)。
 * 一覧のピルを押すと 未受注 → 受注済み → 失注 → 未受注 と巡回する。
 */
enum OrderStatus: int
{
    case NotOrdered = 0;
    case Ordered = 1;
    case Lost = 2;

    public static function fromNullable(mixed $value): self
    {
        return self::tryFrom((int) $value) ?? self::NotOrdered;
    }

    public function next(): self
    {
        return match ($this) {
            self::NotOrdered => self::Ordered,
            self::Ordered => self::Lost,
            self::Lost => self::NotOrdered,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::NotOrdered => '未受注',
            self::Ordered => '受注済み',
            self::Lost => '失注',
        };
    }
}
